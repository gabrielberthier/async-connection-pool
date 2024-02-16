<?php

declare(strict_types=1);

namespace Ravine\ConnectionPool;

use Amp\DeferredFuture;
use Amp\Future;
use Ravine\ConnectionPool\Exceptions\ConnectionPoolException;
use Revolt\EventLoop;
use Ravine\ConnectionPool\Exceptions\SqlException;

use function Amp\async;

/**
 * @template T
 * 
 * @template-implements ConnectionPoolInterface<T>
 */
class ConnectionPool implements ConnectionPoolInterface
{
    public const DEFAULT_MAX_CONNECTIONS = 100;
    public const DEFAULT_IDLE_TIMEOUT = 60;

    /** @var \SplQueue<PoolItem<T>> */
    private readonly \SplQueue $idle;

    /** @var \SplObjectStorage<PoolItem<T>, null> */
    private readonly \SplObjectStorage $locked;

    /** @var Future<PoolItem<T>>|null */
    private ?Future $future = null;

    /** @var DeferredFuture<PoolItem<T>>|null */
    private ?DeferredFuture $awaitingConnection = null;

    private readonly DeferredFuture $onClose;
    private int $countPopAttempts;

    public function __construct(
        private readonly ObjectFactoryInterface $factory,
        private readonly int $maxConnections = self::DEFAULT_MAX_CONNECTIONS,
        private int $idleTimeout = self::DEFAULT_IDLE_TIMEOUT,
        private int $maxRetries = 7
    ) {
        if ($this->idleTimeout < 1) {
            throw new \Error("The idle timeout must be 1 or greater");
        }

        if ($this->maxConnections < 1) {
            throw new \Error("Pool must contain at least one connection");
        }

        $this->locked = new \SplObjectStorage();
        $this->idle = new \SplQueue();
        $this->onClose = new DeferredFuture();
        $this->countPopAttempts = 0;

        $timeoutWatcher = EventLoop::repeat(
            1,
            $this->discardIdleConnections(...)
        );

        EventLoop::unreference($timeoutWatcher);
        $this->onClose
            ->getFuture()
            ->finally(static fn() => EventLoop::cancel($timeoutWatcher));
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
        return $this->onClose->isComplete();
    }

    /**
     * Close all connections in the pool. No further queries may be made after a pool is closed.
     *
     * Fatalistic scenario: kills every locked connections as well.
     */
    public function close(): void
    {
        if ($this->onClose->isComplete()) {
            return;
        }

        foreach ($this->locked as $conn) {
            $this->idle->enqueue($conn);
        }

        /** @var PoolItem<T> $connection */
        foreach ($this->idle as $connection) {
            async(fn() => $connection->close())->ignore();
        }

        $this->onClose->complete();

        $this->awaitingConnection?->error(
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
        if (++$this->countPopAttempts <= $this->maxRetries) {
            $this->close();

            new ConnectionPoolException(
                "No available connection to use; $this->maxRetries retries were made and reached the limit"
            );
        }
        if ($this->isClosed()) {
            throw new \Error("The pool has been closed");
        }

        while ($this->future !== null) {
            $this->future->await(); // Wait until all pending futures are resolved.
        }

        // Attempt to get an idle connection.
        while (!$this->idle->isEmpty()) {
            /** @var PoolItem */
            $connection = $this->idle->dequeue();

            if (!$connection->isClosed()) {
                $this->locked->attach($connection);
                $this->resetAttemptsCounter();

                return $connection;
            }
        }

        // If no idle connections are available, create a new one if allowed.
        if ($this->size() < $this->maxConnections) {
            $connection = $this->createConnection();
            if (!is_null($connection)) {
                $this->locked->attach($connection);
                $this->resetAttemptsCounter();

                return $connection;
            }
        }

        // If all connections are busy, wait until one becomes available.
        try {
            $this->awaitingConnection = new DeferredFuture();
            ($this->future = $this->awaitingConnection->getFuture())->await();
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

        $this->locked->detach($connection);

        if (!$connection->isClosed()) {
            $this->idle->enqueue($connection);
        }

        $this->awaitingConnection?->complete($connection);
        $this->awaitingConnection = null;
    }

    /** @return PoolItem<T> */
    private function createConnection(): ?PoolItem
    {
        try {
            /** @var PoolItem<T> */
            $connection = ($this->future = async(
                fn() => $this->factory->create()
            )
            )->await();
        } finally {
            $this->future = null;
        }

        if ($this->isClosed()) {
            $connection->close();

            return null;
        }

        return $connection;
    }

    private function discardIdleConnections()
    {
        $now = \time();
        while (!$this->idle->isEmpty()) {
            /** @var PoolItem<T> $connection */
            $connection = $this->idle->bottom();

            if ($connection->getLastUsedAt() + $this->idleTimeout > $now) {
                return;
            }

            // Close connection and remove it from the pool.
            $this->idle->shift();
            $connection->close();
        }
    }

    private function resetAttemptsCounter()
    {
        $this->countPopAttempts = 0;
    }
}

/** @var PoolItem<object> $poolItem */
$poolItem = new class extends PoolItem {
    public function __construct()
    {
        $obj = new \stdClass();
        $obj->number = 42;
        parent::__construct($obj);
    }

    protected function onClose(): void
    {
        echo "Closing connection";
    }

    public function validate(): bool
    {
        return true;
    }
};

$poolItem->reveal();

/** @var ConnectionPool<Future> */
$pool = new ConnectionPool(new class($poolItem) implements ObjectFactoryInterface{
    public function __construct(public readonly PoolItem $poolItem) {
    }

    function create(): PoolItem{
        return $this->poolItem;
    }
});

$pool->get();