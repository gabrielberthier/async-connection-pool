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

$result = $poolItem->reveal();

echo $result->number . PHP_EOL;
echo "Validate {$poolItem->validate()}" . PHP_EOL;
$poolItem->close();