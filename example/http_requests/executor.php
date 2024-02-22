<?php

use Ravine\ConnectionPool\ConnectionPool;
use Ravine\ConnectionPool\Factories\PdoFactoryImplementation;
use Ravine\ConnectionPool\PoolFactory;
use Ravine\ConnectionPool\PoolItem;
use React\Promise\Deferred;
use function React\Async\async;
use function React\Async\await;
use function React\Promise\resolve;

// use function React\Promise\Timer\sleep as r_sleep;

function executeSync(ConnectionPool $connectionPool)
{
    $poolItem = $connectionPool->get();
    $conn = $poolItem->reveal();

    try {
        $result = await(
            resolve($conn)
                // The following code snipped emulates a blocking part of the flow
                // ->then(fn($conn) => await(r_sleep(0.2)->then(fn() => $conn)))
                ->then(fn(\PDO $conn) => $conn->prepare("SELECT * FROM authors"))
                ->then(
                    fn(\PDOStatement $sth) => $sth->execute() ? $sth->fetchAll(PDO::FETCH_ASSOC) : null
                )
        );

        return new React\Http\Message\Response(
            React\Http\Message\Response::STATUS_OK,
            ['Content-Type' => 'application/json'],
            json_encode($result)
        );
    } catch (\Throwable $th) {
        echo $th; // This should be logged, not echoed

        throw $th; // Rethrow the exception for proper error handling
    } finally {
        $connectionPool->returnConnection($poolItem);
    }
}

function executeAsync(ConnectionPool $connectionPool)
{
    $deferred = new Deferred();
    $deferred->promise()->then(fn(PoolItem $item) => $connectionPool->returnConnection($item));

    return $connectionPool
        ->getAsync()
        ->then(function (PoolItem $poolItem) use ($deferred) {
            /** @var \PDO */
            $conn = $poolItem->reveal();
            $sth = $conn->prepare("SELECT * FROM authors");
            $result = $sth->execute() ? $sth->fetchAll(PDO::FETCH_ASSOC) : null;

            $deferred->resolve($poolItem);

            return $result;
        })
        ->then(
            fn(array $result) => new React\Http\Message\Response(
                React\Http\Message\Response::STATUS_OK,
                ['Content-Type' => 'application/json'],
                json_encode($result)
            )
        )
        ->catch(function ($th) {
            echo $th; // This should be logged, not echoed
    
            throw $th; // Rethrow the exception for proper error handling
        });
}

return function () {
    return async(function (Psr\Http\Message\ServerRequestInterface $request) {
        $factory = new PdoFactoryImplementation();
        $connectionPool = PoolFactory::get($factory);
        return executeSync($connectionPool);
        // OR
        return executeAsync($connectionPool);
    });
};