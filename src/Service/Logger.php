<?php

namespace Syntax\Service;

class Logger
{
    const FILE_PATH = __DIR__.'/../../ws.log';

    // LOG TYPES
    const SOCKET = 'WEB_SOCKET_DRIVER';
    const CLIENT = 'CLIENT_CONNECTION';
    const AVR = 'AVR_COMMUNICATION';
    const QUEUE = 'QUEUE';
    const EXCEPTION = 'EXCEPTION';

    /**
     * @var bool
     */
    public $echoMode = true;

    /**
     * @param string $type
     * @param string $description
     * @param string $ip
     * @param int $rid
     */
    public function addLog($type, $description, $ip, $rid)
    {
        $line = sprintf('%s [%s] %s', date('d.m.Y H:i:s'), $type, $description);
        if($ip) {
            $line .= ' {'.$ip.'}';
        }
        if($rid) {
            $line .= ' {'.$rid.'}';
        }

        if($this->echoMode) {
            echo $line.PHP_EOL;
        }

        file_put_contents(self::FILE_PATH, $line.PHP_EOL, FILE_APPEND);
    }
}