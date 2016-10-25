<?php

require __DIR__ . '/vendor/autoload.php';


function dump(array $map)
{
    foreach ($map as $item) {
        echo $item['id'] . ' => ' . $item['output'] . PHP_EOL;
    }
}

$map = [];

$loop = React\EventLoop\Factory::create();


for ($i = 0; $i < 5; $i++) {
    $id = rand(10, 90);
    $wait = rand(1, 20);
    $process = new React\ChildProcess\Process('php child.php ' . $id . ' ' . $wait);

    $map[] = [
        'process' => $process,
        'id' => $id,
        'wait' => $wait,
        'output' => '',
    ];
}

$loop->addTimer(0.001, function($timer) use (&$map) {
    foreach ($map as $position => $item) {
        $process = $item['process'];
        $process->start($timer->getLoop());

        $process->stdout->on('data', function($output) use (&$map, $position) {
            $map[$position]['output'] = $output;
            //dump($map);
            echo "Child script says: {$output}\n";
        });
    }
});

// $loop->addPeriodicTimer(5, function($timer) {
//     echo "Parent cannot be blocked by child\n";
// });

$loop->addPeriodicTimer(1, function() use (&$map, $loop) {
    //dump($map);
    $items = array_filter($map, function($item) {
        return empty($item['output']);
    });
    //dump($items);
    //var_dump(count($items));
    if (empty($items)) {
        echo "Stop loop\n";
        $loop->stop();
    }
});

$loop->run();


dump($map);