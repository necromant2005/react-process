<?php
require __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Pool\Pool\Fixed as FixedPool;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollection\ArrayList as ProcessCollectionArrayList;
use React\ChildProcess\Process;


$tasks = [];
for ($i=0; $i<10; $i++) {
    $id = ($i+1);
    $wait = rand(3, 7);
    //$wait = rand(5, 25);
    $tasks[] = 'php child.php ' . $id . ' ' . $wait;
}

$loop = EventLoopFactory::create();

var_dump($tasks);

$processCollection = new ProcessCollectionArrayList(array_map(function($task) use ($loop) {
    return function() use ($loop, $task) {
        $process = new React\ChildProcess\Process($task);
        $promise = \WyriHaximus\React\childProcessPromise($loop, $process);
        // $promise->then(function ($result) {
        //     var_export($result);
        // });
        return $promise;
    };
}, $tasks));
$pool = new FixedPool($processCollection, $loop, ['size' => 2]);
$pool = \React\Promise\resolve();
$loop->run();

var_dump('ZZZ');
//var_dump($processCollection);
//var_dump($pool);