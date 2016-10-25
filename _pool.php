<?php
require __DIR__ . '/vendor/autoload.php';

function dump(array $pool)
{
    foreach ($pool as $item) {
        if (!array_key_exists('pointer', $item)) {
            var_dump($item);
        }
        echo '     ' . $item['pointer'] . ' => ' . $item['output']  . ' => ' . $item['exit'] . PHP_EOL;
    }
}


function pool(array $tasks, $poolSize = 10)
{
    $processed = [];
    $pointer = 0;

    do {
        $pool = [];
        $position = 0;
        for ($i = $pointer; $i < count($tasks); $i++) {
            $process = new React\ChildProcess\Process($tasks[$pointer]);
            $process->on('exit', function($exitCode, $termSignal) use ($position, &$pool) {
                $pool[$position]['exit'] = true;
            });
            $pool[] = [
                'pointer' => $pointer,
                'task' => $process,
                'output' => '',
                'exit' => false,
            ];
            $pointer++;
            $position++;
            if (count($pool) >= $poolSize) break;
        }

        $loop = React\EventLoop\Factory::create();

        $loop->addTimer(0.001, function($timer) use (&$pool) {
            foreach ($pool as $position => $item) {
                $process = $item['task'];
                $process->start($timer->getLoop());

                $process->stdout->on('data', function($output) use (&$pool, $position) {
                    $pool[$position]['output'] .= $output;
                    //dump($pool);
                    echo "Child script says: {$output}\n";
                });
            }
        });

        $loop->addPeriodicTimer(1, function() use (&$pool, $loop) {
            dump($pool);
            $items = array_filter($pool, function($item) {
                return !$item['exit'];
            });
            //dump($items);
            //var_dump(count($items));
            if (empty($items)) {
                echo "Stop loop\n";
                $loop->stop();
            }
        });

        $loop->run();

        foreach ($pool as $item) {
            $processed[$item['pointer']] = $item['output'];
        }
        $pool = [];
    } while (count($processed) < count($tasks));
}



$tasks = [];
for ($i=0;$i<100;$i++) {
    $id = ($i+1);
    $wait = rand(3, 7);
    $tasks[] = 'php child.php ' . $id . ' ' . $wait;
}
pool($tasks);