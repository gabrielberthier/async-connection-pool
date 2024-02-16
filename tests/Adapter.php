<?php

namespace Ravine\Tests\ConnectionPool;

use Ravine\ConnectionPool\ConnectionAdapters\ConnectionAdapterInterface;
use Ravine\ConnectionPool\ConnectionState;

class Adapter implements ConnectionAdapterInterface
{
    public ConnectionState $state;

    public function __construct(ConnectionState $connectionState = ConnectionState::Ready)
    {
        $this->state = $connectionState;
    }

    public function getState(): ConnectionState
    {
        return $this->state;
    }

    public function getConnection(): object
    {
        return new \stdClass();
    }
}