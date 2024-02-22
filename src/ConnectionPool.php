<?php

declare(strict_types=1);

namespace Ravine\ConnectionPool;

use Ravine\ConnectionPool\Exceptions\ConnectionPoolException;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Ravine\ConnectionPool\Exceptions\SqlException;
use Psr\Log\LoggerInterface;

use function React\Async\{await, async};
use function React\Promise\Timer\sleep as r_sleep;
use function React\Promise\{resolve, reject};



/**
 * @template T
 * @implements ConnectionPoolInterface<T>
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
        private ?LoggerInterface $loggerInterface = null,
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

        $timer = $loop->addPeriodicTimer($discardIdleConnectionsIn, async(function () {
            $this->loggerInterface?->debug("Called discard connections");
            // $size = $this->size();
            // echo "Timer called. Number of active connections $size" . PHP_EOL;
            // echo "Borrowed connections " . $this->locked->count() . PHP_EOL;
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
        }));

        $this->loopInterface = $loop;
        $this->isClosed = false;
        $this->onClose
            ->promise()
            ->finally(fn() => $loop->cancelTimer($timer));
    }

    public function size(): int
    {
        return $this->idle->count() + $this->locked->count();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getCountPopAttempts()
    {
        return $this->countPopAttempts;
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
            $this->loggerInterface?->debug("Finished connections");
        });

        $this->onClose->promise()->then(function () {
            $this->isClosed = true;
        });
        $this->onClose->resolve(null);
    }

    /**
     * This method will return synchronously the element in the connection pool
     * 
     * @return PoolItem<T>
     *
     * @throws SqlException
     */
    public function get(): PoolItem
    {
        return await($this->pop());
    }

    /** @return PromiseInterface<PoolItem<T>> */
    public function getAsync(): PromiseInterface
    {
        return $this->pop();
    }

    /** @param PoolItem<T> $connection */
    public function returnConnection(PoolItem $connection)
    {
        $this->loggerInterface?->debug("Returned connection");
        $this->push($connection);
    }

    /**
     * @return PromiseInterface<PoolItem<T>>
     *
     * @throws SqlException If creating a new connection fails.
     * @throws ConnectionPoolException If no connections are available to be created
     * @throws \Error If the pool has been closed.
     */
    protected function pop(): PromiseInterface
    {
        $this->loggerInterface?->debug("Attempting to get available connection");
        if (++$this->countPopAttempts >= $this->maxRetries) {
            $this->loggerInterface?->debug("Max attempts achieved");
            $this->close();

            return reject(
                new ConnectionPoolException(
                    "No available connection to use; $this->maxRetries retries were made and reached the limit"
                )
            );
        }
        if ($this->isClosed()) {
            return reject(new \Error("The pool has been closed"));
        }

        // Attempt to get an idle connection.
        while (!$this->idle->isEmpty()) {
            $connection = $this->idle->first();
            $this->idle->remove($connection);

            if (!$connection->isClosed()) {
                $this->locked->add($connection);
                $connection->setLastUsedAt(\time());
                $this->resetAttemptsCounter();

                return resolve($connection);
            }
        }

        // If no idle connections are available, create a new one if allowed.
        if ($this->size() < $this->maxConnections) {
            $connection = await($this->createConnection());

            if (!is_null($connection)) {
                $this->loggerInterface?->debug("Connection created.");

                $this->locked->add($connection);
                $this->resetAttemptsCounter();

                return resolve($connection);
            }
        }

        // Retry until an active connection is obtained or the pool is closed.

        return r_sleep(0.01, $this->loopInterface)->then(fn() => $this->pop());
    }

    /**
     * @param PoolItem<T> $connection
     *
     * @throws \Error If the connection is not part of this pool.
     */
    protected function push(PoolItem $connection): void
    {
        \assert(
            $this->locked->contains($connection),
            "Connection is not part of this pool"
        );

        $this->locked->remove($connection);

        if (!$connection->isClosed()) {
            $this->idle->add($connection);
        }

        $this->awaitingConnection?->resolve($connection);
        $this->awaitingConnection = null;
    }

    /** @return PromiseInterface<PoolItem<T>> */
    private function createConnection(): ?PromiseInterface
    {
        return new Promise(function (\Closure $resolve, \Closure $reject) {
            $connection = $this->factory->create();

            if (is_null($connection)) {
                $resolve(null);
            }

            if ($this->isClosed()) {
                $connection->close();

                $resolve(null);
            }

            if ($connection->validate()) {
                $resolve($connection);
            }

            $reject(new \Error("Invalid object created for " . get_class($connection) . PHP_EOL));
        });
    }

    private function resetAttemptsCounter()
    {
        $this->countPopAttempts = 0;
    }
}
