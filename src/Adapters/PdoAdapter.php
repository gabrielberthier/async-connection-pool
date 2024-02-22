<?php
namespace Ravine\ConnectionPool\Adapters;

use Ravine\ConnectionPool\PoolItem;

/**
 * @template-extends PoolItem<\PDO>
 * @extends parent<\PDO>
 */
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
