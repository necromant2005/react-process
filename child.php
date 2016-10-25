<?php


list($id, $wait) = array_slice($_SERVER['argv'], 1);

// block all the things!
for ($i=0;$i<$wait;$i++) {
    // echo $id . ' => ' . $i . "\n";
    sleep(1);
}

echo $id . ':' . $i;
