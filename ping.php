<?php
require 'vendor/autoload.php';
use React\ChildProcess\Process;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Pool\Factory\CpuCoreCountFixed;
use WyriHaximus\React\ChildProcess\Pool\Factory\CpuCoreCountFlexible;
use WyriHaximus\React\ChildProcess\Pool\Factory\Fixed;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;


$tasks = [];
for ($i=0; $i < 10; $i++) {
    $id = ($i+1);
    $wait = rand(3, 7);
    $tasks[] = [
        'input' => [
            'id' => $id,
            'wait' => $wait,
        ],
        'output' => '',
        'error' => '',
    ];
}

$loop = Factory::create();
Fixed::create(new Process('php pong.php'), $loop, ['size' => 10])->then(function (PoolInterface $pool) use ($loop, &$tasks) {
    // $pool->on('message', function ($message) {
    //     var_export($message);
    // });
    // $pool->on('error', function ($e) {
    //     echo 'Error: ', var_export($e, true), PHP_EOL;
    // });

    foreach ($tasks as $position => $task) {
        $pool->rpc(MessagesFactory::rpc('call', $task['input']))->then(function ($data) use ($position, &$tasks) {
            $tasks[$position]['output'] = $data['result'];
            echo 'Answer for ' . $position . ': ', $data['result'], PHP_EOL;
        }, function ($error) {
            var_export($error);
            $tasks[$position]['output'] = '';
            $tasks[$position]['error'] = $error;
        });
    }

    $timer = $loop->addPeriodicTimer(1, function () use ($pool, $loop) {
        $info = $pool->info();
        if (empty($info['busy']) && empty($info['calls'])) {
            $loop->stop();
        }
    });
});
$loop->run();
// var_dump($tasks);