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
            echo "Closing connection";
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

    public function setUp(): void
    {
        $this->defaultFactory = $this->getMockBuilder(ObjectFactoryInterface::class)->getMock();
        $items = array_map(fn($el) => generateInput($el), range(1, 25));
        $this->defaultFactory->method('create')->willReturn(array_shift($items), ...$items);

    }

    public function testShouldValidateFactoryEventLoop()
    {
        $loop = Loop::get();
        $sut = new ConnectionPool($this->defaultFactory);

        $this->assertSame($sut->loopInterface, $loop);
    }

    public function testShouldReceiveValidConnection()
    {
        $sut = new ConnectionPool($this->defaultFactory);
        $poolItem = $sut->get();

        $this->assertInstanceOf(PoolItem::class, $poolItem);
    }


    public function testShouldExhaustGetConnectionAndThrow()
    {
        $this->expectException(ConnectionPoolException::class);
        $factoryMock = $this->getMockBuilder(ObjectFactoryInterface::class)->getMock();
        $factoryMock->method('create')->willReturn(null);
        $sut = new ConnectionPool($factoryMock);
        $response = $sut->get();

        print_r($response);
    }

    public function testShouldCreateOnlyMaxNumberOfConnections()
    {
        $maxConnections = 10;

        $sut = new ConnectionPool($this->defaultFactory, maxConnections: $maxConnections);
        $poolItens = [];
        for ($i = 0; $i < 115; $i++) {
            $item = $sut->get();
            print_r($item);
            $poolItens[] = $item;
            if ($i % $maxConnections === 0) {
                foreach ($poolItens as $item) {
                    $sut->returnConnection($item);
                }
            }
        }

        $this->assertEquals($maxConnections, $sut->size());
    }

    public function testShouldAlwaysReturnIdleConnection()
    {
        $sut = new ConnectionPool($this->defaultFactory);
        $poolItens = [];
        for ($i = 0; $i < 20; $i++) {
            $item = $sut->get();
            $poolItens[] = $item;
            # Will mark connection as idle
            $sut->returnConnection($poolItens[$i]);
        }

        $this->assertEquals(1, $sut->size());
        $this->assertIsArray($poolItens);
    }
    
    public function testShouldThrowForMaxConnectionsReached()
    {
        $sut = new ConnectionPool($this->defaultFactory);
        $poolItens = [];
        for ($i = 0; $i < 20; $i++) {
            $item = $sut->get();
            $poolItens[] = $item;
            # Will mark connection as idle
            $sut->returnConnection($poolItens[$i]);
        }

        $this->assertEquals(1, $sut->size());
        $this->assertIsArray($poolItens);
    }

}
