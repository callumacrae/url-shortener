<?php

require('url-shortener.php');

$time_start = microtime(true);

for ($i = 0; $i < 10000; $i++)
{
	$shortener = new Shortener();
	$var = $shortener->get('94d4');
	unset($shortener, $var);
}

echo microtime(true)-$time_start;