<?php

declare(strict_types=1);

namespace Ravine\ConnectionPool;

/**
 * @template T
 */
interface ConnectionPoolInterface
{
    public function size(): int;
    public function getLastUsedAt(): int;
    public function isClosed(): bool;
    public function close(): void;
    /** @return PoolItem<T> */
    public function get(): PoolItem;
}