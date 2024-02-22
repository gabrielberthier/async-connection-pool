<?php

namespace Ravine\ConnectionPool;

/**
 * @template T 
 */
abstract class PoolItem
{
    private int $lastUsedAt;
    private bool $isClosed;
    /** @var T $item */
    public readonly object $item;

    /**
     * @param T $item
     */
    public function __construct(object $item)
    {
        $this->item = $item;
        $this->lastUsedAt = \time();
        $this->isClosed = false;
    }

    protected abstract function onClose(): void;
    abstract function validate(): bool;

    public function close(): void
    {
        $this->isClosed = true;
        $this->onClose();
    }

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    /** @return T */
    public function reveal(): object
    {
        return $this->item;
    }

    public function getLastUsedAt(): int
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(int $time): void
    {
        $this->lastUsedAt = $time;
    }
}
