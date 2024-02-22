<?php

require 'vendor/autoload.php';

use Ravine\ConnectionPool\ConnectionPool;
use Ravine\ConnectionPool\Factories\PdoFactoryImplementation;
use React\EventLoop\Loop;
use Revolt\EventLoop\React\Internal\EventLoopAdapter;

Loop::set(EventLoopAdapter::get());

$loop = Loop::get();

try{
    $sut = new ConnectionPool(new PdoFactoryImplementation());
    /** @var \PDO */
    $result = $sut->get()->reveal();
    $result->exec('INSERT into authors(first_name, last_name, email, birthdate) values (?, ?, ?, ?)');
}catch (Throwable $th){
    echo $th;
}

$loop->run();