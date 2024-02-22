<?php

namespace Ravine\ConnectionPool\Factories;

use Ravine\ConnectionPool\Adapters\PdoAdapter;
use Ravine\ConnectionPool\ObjectFactoryInterface;
use Ravine\ConnectionPool\PoolItem;

/**
 * 
 * @implements ObjectFactoryInterface<\PDO>
 * @template-implements ObjectFactoryInterface<\PDO> 
 */
class PdoFactoryImplementation implements ObjectFactoryInterface
{
    public function create(): ?PoolItem
    {
        return new PdoAdapter();
    }
}