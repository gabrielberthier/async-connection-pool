<?php

namespace Ravine\Tests\ConnectionPool;


use Ravine\ConnectionPool\PoolItem;
use PHPUnit\Framework\TestCase;

class PoolItemTest extends TestCase
{
    public function testShouldAssertRawObjectIsReceived()
    {
        $obj = new \stdClass();
        $obj->number = 42;
        /** @var PoolItem<object> $poolItem */
        $poolItem = new class ($obj) extends PoolItem {
            protected function onClose(): void
            {
                echo "Closing connection in pool item";
            }

            public function validate(): bool
            {
                return true;
            }
        };

        $this->assertSame($poolItem->reveal(), $obj);
        $this->assertEquals($poolItem->reveal()->number, 42);
    }
}
