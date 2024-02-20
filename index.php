<?php

require 'vendor/autoload.php';

use React\EventLoop\Loop;
use React\Promise\Promise;
use Revolt\EventLoop;
use Revolt\EventLoop\React\Internal\EventLoopAdapter;
use function React\Async\async;
use function React\Async\await;
use React\Promise\Timer;
use function React\Promise\resolve;

$app = function ($request, $response) {
    $response->writeHead(200, array('Content-Type' => 'text/plain'));
    $response->end("Hello World\n");
};

Loop::set(EventLoopAdapter::get());

$http = new React\Http\HttpServer(
    async(function (Psr\Http\Message\ServerRequestInterface $request) {
        try {
            // await(
            //     new Promise(function ($resolve, $reject) {
            //         sleep(1);
            //         $resolve(null);
            //     })
            // );
            return new React\Http\Message\Response(
                React\Http\Message\Response::STATUS_OK,
                array(
                    'Content-Type' => 'text/plain'
                ),
                "Hello World!\n"
            );
        } catch (\Throwable $th) {
            echo $th;
        }
    })
);

$serverAddress = '0.0.0.0:8080';

echo "Server running at $serverAddress";

$socket = new React\Socket\SocketServer($serverAddress, loop: Loop::get());

$http->listen($socket);

EventLoop::run();

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;