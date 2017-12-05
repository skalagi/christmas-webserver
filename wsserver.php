<?php

require __DIR__.'/vendor/autoload.php';


$server = \Syntax\ChristmasContainer::getInstance()->get('server');
$server->run();