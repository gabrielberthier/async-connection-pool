<?php

namespace Ravine\Tests\ConnectionPool;

use PHPUnit\Framework\MockObject\MockObject;
use Ravine\ConnectionPool\ObjectFactoryInterface;
use Ravine\ConnectionPool\PoolItem;
use Ravine\ConnectionPool\ConnectionPool;
use PHPUnit\Framework\TestCase;
use Ravine\ConnectionPool\Exceptions\ConnectionPoolException;
use React\EventLoop\Loop;

function generateInput(int $n)
{
    return new class ($n) extends PoolItem {
        public function __construct($currentValue)
        {
            $obj = new \stdClass();
            $obj->number = $currentValue;
            parent::__construct($obj);
        }

        protected function onClose(): void
        {

        }

        public function validate(): bool
        {
            return true;
        }
    };
}

class ConnectionPoolTest extends TestCase
{
    private ConnectionPool $sut;
    private MockObject|ObjectFactoryInterface $defaultFactory;

    private int $maxConnections = 10;

    public function setUp(): void
    {
        $this->defaultFactory = $this->getMockBuilder(ObjectFactoryInterface::class)->getMock();
        $items = array_map(fn($el) => generateInput($el), range(1, 25));
        $this->defaultFactory->method('create')->willReturn(array_shift($items), ...$items);
        $this->sut = new ConnectionPool($this->defaultFactory, maxConnections: $this->maxConnections);
    }

    public function tearDown(): void
    {
        $this->sut->close();
    }

    public function testShouldValidateFactoryEventLoop()
    {
        $loop = Loop::get();

        $this->assertSame($this->sut->loopInterface, $loop);
    }

    public function testShouldReceiveValidConnection()
    {
        $poolItem = $this->sut->get();

        $this->assertInstanceOf(PoolItem::class, $poolItem);
    }


    public function testShouldExhaustGetConnectionAndThrow()
    {
        $this->expectException(ConnectionPoolException::class);
        $factoryMock = $this->getMockBuilder(ObjectFactoryInterface::class)->getMock();
        $factoryMock->method('create')->willReturn(null);
        $this->sut = new ConnectionPool($factoryMock);
        $this->sut->get();
    }

    public function testShouldCreateOnlyMaxNumberOfConnections()
    {
        $maxConnections = $this->maxConnections;

        $poolItens = [];
        for ($i = 0; $i < 115; $i++) {
            $item = $this->sut->get();
            $poolItens[] = $item;
            if ($i % $maxConnections === 0) {
                foreach ($poolItens as $item) {
                    $this->sut->returnConnection($item);
                }
            }
        }

        $this->assertEquals($maxConnections, $this->sut->size());
    }

    public function testShouldAlwaysReturnIdleConnection()
    {
        $poolItens = [];
        for ($i = 0; $i < 20; $i++) {
            $item = $this->sut->get();
            $poolItens[] = $item;
            # Will mark connection as idle
            $this->sut->returnConnection($poolItens[$i]);
        }

        $this->assertEquals(1, $this->sut->size());
        $this->assertIsArray($poolItens);
    }

    public function testShouldResetGetCounter()
    {
        $defaultFactory = $this->getMockBuilder(ObjectFactoryInterface::class)->getMock();
        $defaultFactory->method('create')->willReturn(null, null, generateInput(1));
        $this->sut = new ConnectionPool($defaultFactory);
        $this->sut->get();

        $this->assertEquals(0, $this->sut->getCountPopAttempts());
    }
}
