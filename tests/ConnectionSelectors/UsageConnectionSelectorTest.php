<?php

namespace Ravine\Tests\ConnectionPool\ConnectionSelectors;

use Ravine\ConnectionPool\ConnectionSelectors\UsageConnectionSelector;
use PHPUnit\Framework\TestCase;
use Ravine\ConnectionPool\ConnectionState;
use Ravine\Tests\ConnectionPool\Adapter;

class UsageConnectionSelectorTest extends TestCase
{
    public function testSelect()
    {
        $st = new \SplObjectStorage();
        $sel = new UsageConnectionSelector($st);
        $this->assertNull($sel->select());

        $a1 = new Adapter();
        $a2 = new Adapter();
        $a3 = new Adapter();

        $st->attach($a1);
        $this->assertEquals($a1, $sel->select());

        $st->attach($a2);
        $this->assertEquals($a2, $sel->select());

        $st->attach($a3);
        $a3->state = ConnectionState::Busy;
        $this->assertEquals($a1, $sel->select());

        $a3->state = ConnectionState::Ready;
        $this->assertEquals($a3, $sel->select());

        $this->assertEquals($a2, $sel->select());
    }
}
