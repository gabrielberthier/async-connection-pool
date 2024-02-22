<?php

namespace Ravine\ConnectionPool;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;


class PoolFactory
{
    private static ConnectionPool $instance;

    // Private constructor to prevent instantiation from outside
    private function __construct()
    {
    }

    public static function get(
        ObjectFactoryInterface $factory,
        int $maxConnections = Ravine\ConnectionPool\ConnectionPool::DEFAULT_MAX_CONNECTIONS,
        int $idleTimeout = Ravine\ConnectionPool\ConnectionPool::DEFAULT_IDLE_TIMEOUT,
        int $maxRetries = 7,
        int $discardIdleConnectionsIn = 1,
        ?LoggerInterface $loggerInterface = null,
        ?LoopInterface $loop = null
    ) {
        if (self::$instance === null) {
            self::$instance = new ConnectionPool(
                $factory,
                $maxConnections,
                $idleTimeout,
                $maxRetries,
                $discardIdleConnectionsIn,
                $loggerInterface,
                $loop
            );
        }
        return self::$instance;
    }
}