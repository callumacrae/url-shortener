<?php

require('url-shortener.php');

$shortener = new Shortener();

echo $shortener->submit('test');