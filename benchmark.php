<?php

require('url-shortener.php');

$reps = 10000;

$time_start = microtime(true);

for ($i = 0; $i < $reps; $i++)
{
	$shortener = new Shortener();
	$var = $shortener->get('94d4');
	unset($shortener, $var);
}

$total_time = microtime(true) - $time_start;

echo 'Repetitions: ' . $reps . '<br />' . PHP_EOL;
echo 'Total time: ' . ($total_time * 1000) . ' miliseconds<br />' . PHP_EOL;
echo 'Average time per request: ' . ($total_time * 1000 / $reps) . ' miliseconds';

//log average time per request
file_put_contents('bench_log', ($total_time * 1000 / $reps) . PHP_EOL, FILE_APPEND);