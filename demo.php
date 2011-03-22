<?php

require('url-shortener.php');

$shortener = new Shortener();

//echo $shortener->get('test');
print_r($shortener->get(10, url));
//$shortener->submit('http://' . rand() . '.rand.com/');