<?php

require 'vendor/autoload.php';

use Ravine\ConnectionPool\ConnectionPool;
use Ravine\ConnectionPool\Factories\PdoFactoryImplementation;
use Ravine\ConnectionPool\PoolItem;
use React\EventLoop\Loop;
use Revolt\EventLoop\React\Internal\EventLoopAdapter;

Loop::set(EventLoopAdapter::get());

$loop = Loop::get();

$sut = new ConnectionPool(new PdoFactoryImplementation());
$sut->getAsync()
    ->then(fn(PoolItem $item) => $item->reveal())
    ->then(
        fn(\PDO $pdo) =>
        $pdo->exec('INSERT into authors(first_name, last_name, email, birthdate) values (?, ?, ?, ?)')
    )
    ->then(function (PDOStatement $sth) {
        $strValue = 'test';
        $sth->bindParam(1, $strValue, PDO::PARAM_STR);
        $sth->bindParam(2, $strValue, PDO::PARAM_STR);
        $sth->bindParam(3, $strValue, PDO::PARAM_STR);
        $sth->bindParam(4, $strValue, PDO::PARAM_STR);

        return $sth->execute();
    })
    ->then(fn(bool $result) => print($result))
    ->catch($th);


$loop->run();