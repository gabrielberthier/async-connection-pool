<?php

require 'vendor/autoload.php';

use Ravine\ConnectionPool\ConnectionPool;
use Ravine\ConnectionPool\Factories\PdoFactoryImplementation;
use React\EventLoop\Loop;
use Revolt\EventLoop\React\Internal\EventLoopAdapter;

Loop::set(EventLoopAdapter::get());

$loop = Loop::get();

$sut = new ConnectionPool(new PdoFactoryImplementation(), loop: $loop);

$handler = require __DIR__ . '/executor.php';

ini_set('memory_limit', '512M');

$http = new React\Http\HttpServer(
    new React\Http\Middleware\StreamingRequestMiddleware(),
    $handler()
);

$serverAddress = '0.0.0.0:8080';

echo "Server running at $serverAddress" . PHP_EOL;

$socket = new React\Socket\SocketServer($serverAddress, loop: $loop);

$http->listen($socket);

$loop->run();

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;