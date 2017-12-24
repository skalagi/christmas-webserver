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
     * @var boolean
     */
    private $__is_sending = false;

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
        if($this->__is_sending) return;
        
        if($this->tcpHandle) {
            fclose($this->tcpHandle);
        }

        $this->tcpHandle = stream_socket_client('tcp://'.$this->host.':'.$this->port, $err, $errStr, $this->timeout, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT);
        if (!$this->tcpHandle) {

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
        $this->__is_sending = true;
        $result = fwrite($this->tcpHandle, $message);
        $this->__is_sending = false;
        return $result;
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