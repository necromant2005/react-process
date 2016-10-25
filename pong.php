<?php
require 'vendor/autoload.php';
use React\EventLoop\Factory;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Invoke;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Messenger\Recipient;

$loop = Factory::create();
$recipient = \WyriHaximus\React\ChildProcess\Messenger\Factory::child($loop);
// $recipient->on('message', function (Payload $payload, Messenger $messenger) {
//     $messenger->write(json_encode([
//         'type' => 'message',
//         'payload' => $payload,
//     ]));
//     $messenger->getLoop()->addTimer(1, function () use ($messenger) {
//         $messenger->getLoop()->stop();
//     });
// });
$recipient->registerRpc('call', function (Payload $payload, Messenger $messenger) use ($loop) {

    sleep($payload['wait']);

    return \React\Promise\resolve([
        'result' => $payload['id'] . '=>' . $payload['wait'],
    ]);
});
$loop->run();