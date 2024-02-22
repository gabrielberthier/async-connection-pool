# ReactPHP Connection Pool

Async and flexible pool for any type of connections built on top of [ReactPHP](https://reactphp.org/).

Connection pooling allows you to easily manage range of connections with some remote service (e.g. a database server). You can define how many connections your app can estabilish or how to react when all connections are busy at the same time.

This library will handle closing connections after a certain timeout, create connections as they are required and manage connections' states, however it is still **Flexible** enough to manage any type of connections by implementing your own connection adapter and specify how connections are created AND **Lightweight**, as it uses very few external components. In addition, it is a simple component that can be freely extended according to your preferences and needs.

## Requirements

- PHP >= 8.1 (fibers, enums)

## Examples

```php
final class PdoAdapter extends PoolItem
{
    public function __construct()
    {
        parent::__construct(
            new \PDO(
                dsn: 'mysql:host=127.0.0.1;dbname=test',
                username: 'root',
                password: '123456',
                options: array(
                    \PDO::ATTR_PERSISTENT => true
                )
            )
        );
    }

    protected function onClose(): void
    {

    }

    function validate(): bool
    {
        return $this->item instanceof \PDO;
    }
}

class PdoFactoryImplementation implements ObjectFactoryInterface
{
    public function create(): ?PoolItem
    {
        return new PdoAdapter();
    }
}

$sut = new ConnectionPool(new PdoFactoryImplementation());
/** @var \PDO */
$result = $sut->get()->reveal();

```

## Configuration

It is **mandatory** to pass an instance of _ObjectFactoryInterface_ to the constructor of either Ravine\ConnectionPool\PoolFactory or Ravine\ConnectionPool\ConnectionPool. The other parameters are inferred from predefined values that came to me in a dream as a good default values.

**BUT**:

You can still pass additional parameters to pool's constructor:

- `maxConnections`: number of connections to be open in a single process (remember, PHP is single threaded).
- `idleTimeout`: timeout (in seconds) to dispose idle connections.
- `maxRetries`: number of attempts JUST IN CASE your factory returns a nullable value from an error.
- `discardIdleConnectionsIn`: number of seconds to look up for removable connections.
- `loggerInterface`: a PRS\LoggerInstance to log interactions in this component.
- `loop`: instance of `React\EventLoop\LoopInterface` to use.

## At the end

- Run tests: `./vendor/bin/phpunit`
- Feel free to submit your PR
- Licence: MIT
