<?php

if(!isset($_GET['ctrl'])) {
    echo 'Invalid controller!'; exit;
}

require_once __DIR__.'/../vendor/autoload.php';

$container = \Syntax\ChristmasContainer::getInstance();

switch($_GET['ctrl']) {
    default:
        echo 'Invalid controller!'; exit;

    case 'getEndpoint':
        header('Content-Type: application/json');
        echo json_encode([
            'endpoint' => preg_match('/192\.168/', $_SERVER['REMOTE_ADDR']) ?
                $container->getParameter('local_ws_endpoint') : $container->getParameter('external_ws_endpoint')
        ]);
        break;
}