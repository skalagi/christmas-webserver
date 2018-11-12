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
        $endPoint = $container->getParameter(
            preg_match('/192\.168/', $_SERVER['REMOTE_ADDR']) ? 'local_ws_endpoint' : 'external_ws_endpoint'
        );
        $protocol = empty($_SERVER['HTTPS']) ? 'ws://' : 'wss://';

        echo json_encode([
            'endpoint' => $protocol.$endPoint
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
}