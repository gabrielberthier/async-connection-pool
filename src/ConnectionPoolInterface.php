<?php

declare(strict_types=1);

namespace Ravine\ConnectionPool;
use React\Promise\PromiseInterface;

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
    /** @return PromiseInterface<PoolItem<T>> */
    public function getAsync(): PromiseInterface;
}