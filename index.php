<?php

require 'vendor/autoload.php';

use Ravine\ConnectionPool\ConnectionPool;
use Ravine\ConnectionPool\Factories\PdoFactoryImplementation;
use React\EventLoop\Loop;
use Revolt\EventLoop\React\Internal\EventLoopAdapter;

$app = function ($request, $response) {
    $response->writeHead(200, array('Content-Type' => 'text/plain'));
    $response->end("Hello World\n");
};

Loop::set(EventLoopAdapter::get());

$loop = Loop::get();

$sut = new ConnectionPool(new PdoFactoryImplementation(), loop: $loop);
$items = [];
// Warmup
for ($i = 0; $i < 10; $i++) {
    $items[] = $sut->get();
}

foreach ($items as $item) {
    $sut->returnConnection($item);
}

$counter = 0;

$handler = require __DIR__ . '/executor.php';


ini_set('memory_limit', '512M');

// memory_limit 128M
// post_max_size 8M // capped at 64K

// enable_post_data_reading 1
// max_input_nesting_level 64
// max_input_vars 1000

// file_uploads 1
// upload_max_filesize 2M
// max_file_uploads 20

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