<?php

declare(strict_types=1);

namespace Ravine\ConnectionPool;

use Amp\DeferredFuture;
use Amp\Future;
use Ravine\ConnectionPool\Exceptions\ConnectionPoolException;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Ravine\ConnectionPool\Exceptions\SqlException;

use function React\Async\await;
use function React\Async\async;
use function React\Promise\resolve;

function println(string $value)
{
    echo $value . PHP_EOL;
}

/**
 * @template T
 * 
 * @template-implements ConnectionPoolInterface<T>
 */
class ConnectionPool implements ConnectionPoolInterface
{
    public const DEFAULT_MAX_CONNECTIONS = 100;
    public const DEFAULT_IDLE_TIMEOUT = 60;

    /** @var \DS\Set<PoolItem<T>> */
    private readonly \DS\Set $idle;

    /** @var \DS\Set<PoolItem<T>> */
    private readonly \DS\Set $locked;

    /** @var ?PromiseInterface<?PoolItem<T>> */
    private ?PromiseInterface $future = null;

    /** @var ?Deferred<?PoolItem<T>> */
    private ?Deferred $awaitingConnection = null;

    /** @var ?Deferred<null> */
    private readonly Deferred $onClose;
    private int $countPopAttempts;

    private bool $isClosed;

    public LoopInterface $loopInterface;

    public function __construct(
        private readonly ObjectFactoryInterface $factory,
        private readonly int $maxConnections = self::DEFAULT_MAX_CONNECTIONS,
        private int $idleTimeout = self::DEFAULT_IDLE_TIMEOUT,
        private int $maxRetries = 7,
        private int $discardIdleConnectionsIn = 1,
        ?LoopInterface $loop = null
    ) {
        if ($this->idleTimeout < 1) {
            throw new \Error("The idle timeout must be 1 or greater");
        }

        if ($this->maxConnections < 1) {
            throw new \Error("Pool must contain at least one connection");
        }

        $this->locked = new \DS\Set();
        $this->idle = new \DS\Set();
        $this->onClose = new Deferred();
        $this->countPopAttempts = 0;
        $loop ??= Loop::get();

        $timer = $loop->addPeriodicTimer($discardIdleConnectionsIn, $this->discardIdleConnections(...));

        $this->loopInterface = $loop;
        $this->isClosed = false;
        $this->onClose
            ->promise()
            ->finally(static fn() => $loop->cancelTimer($timer));
    }

    public function size(): int
    {
        return $this->idle->count() + $this->locked->count();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getLastUsedAt(): int
    {
        $time = 0;

        foreach ($this->locked as $connection) {
            $lastUsedAt = $connection->getLastUsedAt();
            $time = max($time, $lastUsedAt);
        }

        return $time;
    }

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    /**
     * Close all connections in the pool. No further queries may be made after a pool is closed.
     *
     * Fatalistic scenario: kills every locked connections as well.
     */
    public function close(): void
    {
        if ($this->isClosed) {
            return;
        }

        foreach ($this->locked as $conn) {
            $this->idle->add($conn);
        }

        $promises = [];

        /** @var PoolItem<T> $connection */
        foreach ($this->idle as $connection) {
            $promises[] = new Promise(fn($resolve) => $resolve($connection->close()));
        }

        \React\Promise\all($promises)->then(function (array $promises) {
            println("Finished connections");
        });

        $this->onClose->promise()->then(function () {
            $this->isClosed = true;
        });
        $this->onClose->resolve(null);

        $this->awaitingConnection?->reject(
            new SqlException("Connection pool closed")
        );
        $this->awaitingConnection = null;
    }

    /**
     * @return PoolItem<T>
     *
     * @throws SqlException
     */
    public function get(): PoolItem
    {
        return $this->pop();
    }

    /** @param PoolItem<T> $connection */
    public function returnConnection(PoolItem $connection)
    {
        println("Returned connection");
        print_r($connection);
        $this->push($connection);
    }

    /**
     * @return PoolItem<T>
     *
     * @throws SqlException If creating a new connection fails.
     * @throws ConnectionPoolException If no connections are available to be created
     * @throws \Error If the pool has been closed.
     */
    protected function pop(): PoolItem
    {
        println("Attempting to get available connection");
        if (++$this->countPopAttempts >= $this->maxRetries) {
            println("Max attempts achieved");
            $this->close();

            throw new ConnectionPoolException(
                "No available connection to use; $this->maxRetries retries were made and reached the limit"
            );
        }
        if ($this->isClosed()) {
            throw new \Error("The pool has been closed");
        }

        while ($this->future !== null) {
            println("Waiting");
            resolve($this->future); // Wait until all pending futures are resolved.
        }

        // Attempt to get an idle connection.
        while (!$this->idle->isEmpty()) {
            $connection = $this->idle->first();
            $this->idle->remove($connection);

            if (!$connection->isClosed()) {
                $this->locked->add($connection);
                $connection->setLastUsedAt(\time());
                $this->resetAttemptsCounter();

                return $connection;
            }
        }

        // If no idle connections are available, create a new one if allowed.
        if ($this->size() < $this->maxConnections) {
            $connection = $this->createConnection();
            if (!is_null($connection)) {
                println("Connection created.");

                $this->locked->add($connection);
                $this->resetAttemptsCounter();

                return $connection;
            }
        }

        // If all connections are busy, wait until one becomes available.
        try {
            $this->awaitingConnection = new Deferred();
            $this->future = $this->awaitingConnection->promise();
            resolve($this->future);
        } finally {
            $this->awaitingConnection = null;
            $this->future = null;
        }

        // Retry until an active connection is obtained or the pool is closed.
        return $this->pop();
    }

    /**
     * @param PoolItem<T> $connection
     *
     * @throws \Error If the connection is not part of this pool.
     */
    protected function push(PoolItem $connection): void
    {
        \assert(
            isset($this->locked[$connection]),
            "Connection is not part of this pool"
        );

        $this->locked->remove($connection);

        if (!$connection->isClosed()) {
            $this->idle->add($connection);
        }

        $this->awaitingConnection?->resolve($connection);
        $this->awaitingConnection = null;
    }

    /** @return PoolItem<T> */
    private function createConnection(): ?PoolItem
    {
        try {
            $connection = $this->factory->create();
            print_r($connection);
        } finally {
            $this->future = null;
        }

        if ($this->isClosed()) {
            $connection->close();

            return null;
        }

        return $connection;
    }

    public function discardIdleConnections(TimerInterface $timerInterface)
    {
        println("Called discard connections");
        $now = \time();
        while (!$this->idle->isEmpty()) {
            $connection = $this->idle->first();

            if ($connection->getLastUsedAt() + $this->idleTimeout > $now) {
                return;
            }

            // Close connection and remove it from the pool.
            $this->idle->remove($connection);
            $connection->close();
        }
    }

    private function resetAttemptsCounter()
    {
        $this->countPopAttempts = 0;
    }
}
