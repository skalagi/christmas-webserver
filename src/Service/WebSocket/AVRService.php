<?php

namespace Syntax\Service\WebSocket;

use Syntax\Exception\AVRException;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Syntax\Service\Database;

class AVRService
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var resource
     */
    private $tcpHandle;


    /**
     * @var Database
     */
    private $database;
    

    /**
     * @var null|int
     */
    private $__last_connection = null;

    /**
     * @var int
     */
    private $__retry_counter = 0;

    const CONNECTION_EXPIRE_SECONDS = 180;
    const MAX_RETRY = 20;

    /**
     * AVRService constructor.
     * @param string $avrHost
     * @param int $avrPort
     * @param int $avrTimeout
     * @param Database $database
     * @throws AVRException
     */
    public function __construct($avrHost, $avrPort, $avrTimeout, Database $database)
    {
        $this->host = $avrHost;
        $this->port = $avrPort;
        $this->timeout = $avrTimeout;

        $this->database = $database;

        $this->reopenConnection();
    }


    /**
     * Lights destructor.
     */
    public function __destruct()
    {
        if($this->tcpHandle) fclose($this->tcpHandle);
    }

    /**
     * Reopen connection to worker module
     */
    public function reopenConnection()
    {
        if($this->tcpHandle) {
            fclose($this->tcpHandle);
        }

        $this->tcpHandle = @stream_socket_client('tcp://'.$this->host.':'.$this->port, $err, $errStr, $this->timeout, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT);
        if (!$this->tcpHandle) {
            if($this->__retry_counter++ < static::MAX_RETRY) {
                $this->addAVRLog(
                    LogEvents::AVR_ERROR,
                    sprintf('Cannot connect to AVR module on %s:%s.'.PHP_EOL.'Retrying after 10 seconds..', $this->host, $this->port)
                );
                sleep(10);
                return $this->reopenConnection();
            }

            $this->addAVRLog(LogEvents::AVR_CRITICAL, sprintf('%s (%s)', $errStr, $err));
            throw new AVRException(sprintf('Cannot connect to AVR module on %s:%s.', $this->host, $this->port));
        }

        $this->addAVRLog(LogEvents::AVR_CONNECTED, sprintf('Open connection to worker module on %s:%s', $this->host, $this->port));
        $this->__last_connection = time();
    }

    /**
     * @param $message
     * @return int
     */
    public function send($message)
    {
        return fwrite($this->tcpHandle, $message);
    }

    /**
     * @param $logEvent
     * @param $message
     */
    private function addAVRLog($logEvent, $message)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime();
        $log->initiator = __CLASS__.': '.__LINE__;
        $log->name = $logEvent;
        $log->data = [
            'message' => $message
        ];
        $this->database->addLog($log);
    }
}