<?php

namespace Ravine\ConnectionPool;


/**
 * @template TObject
 */
interface ObjectFactoryInterface
{
    /**
     * 
     * @return PoolItem<TObject>
     */
    function create(): PoolItem;
}