<?php

if(!isset($_GET['ctrl'])) {
    echo 'Invalid controller!'; exit;
}

require_once __DIR__.'/../vendor/autoload.php';

$container = \Syntax\ChristmasContainer::getInstance();

header('Access-Control-Allow-Origin: *');
switch($_GET['ctrl']) {
    default:
        echo 'Invalid controller!'; exit;

    case 'getMyIP':
        echo json_encode([
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        break;

    case 'getEndpoint':
        header('Content-Type: application/json');
        $endPoint = $container->getParameter(
            preg_match('/192\.168/', $_SERVER['REMOTE_ADDR']) ? 'local_ws_endpoint' : 'external_ws_endpoint'
        );
        $protocol = empty($_SERVER['HTTPS']) ? 'ws://' : 'wss://';

        echo json_encode([
            'local' => 'ws://'.$container->getParameter('local_ws_endpoint'),
            'external' => 'wss://'.$container->getParameter('external_ws_endpoint'),
        ]);
        break;

    case 'getStream':
        header('Content-Type: application/json');
        echo json_encode([
            'type' => 'yt',
            'value' => $container->getParameter('stream')
        ]);
        break;

    case 'getChannels':
        header('Content-Type: application/json');
        echo json_encode($container->getParameter('relays'));
        break;

    case '_test':
        echo file_get_contents(__DIR__.'/test.html');
        break;

    case '_test_js_color':
        header('Content-Type: application/javascript');
        echo file_get_contents(__DIR__.'/jscolor.js');
        break;
}