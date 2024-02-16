<?php

namespace Ravine\Tests\ConnectionPool;

use Ravine\ConnectionPool\ConnectionAdapters\ConnectionAdapterInterface;
use Ravine\ConnectionPool\ConnectionSelectors\ConnectionSelectorInterface;

class Selector implements ConnectionSelectorInterface
{
    public function __construct(private \SplObjectStorage $connections)
    {
    }

    public function select(): ConnectionAdapterInterface|null
    {
        $this->connections->rewind();

        return $this->connections->count() ? $this->connections->current() : null;
    }
}