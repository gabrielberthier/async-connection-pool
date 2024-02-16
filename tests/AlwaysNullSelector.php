<?php

namespace Ravine\Tests\ConnectionPool;

use Ravine\ConnectionPool\ConnectionAdapters\ConnectionAdapterInterface;
use Ravine\ConnectionPool\ConnectionSelectors\ConnectionSelectorInterface;

class AlwaysNullSelector implements ConnectionSelectorInterface
{
    public function __construct(\SplObjectStorage $connections)
    {
    }

    public function select(): ConnectionAdapterInterface|null
    {
        return null;
    }
}