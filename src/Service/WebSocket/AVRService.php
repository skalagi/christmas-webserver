<?php

namespace Syntax\Service\WebSocket;

use Syntax\Exception\AVRException;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Syntax\Service\Database;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;

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
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var Database
     */
    private $database;
    
    /**
     * @var LoopInterface
     */
    private $loop;
    
    /**
     * @var boolean
     */
    private $__trying_reopen_after_failed_write = false;

    /**
     * AVRService constructor.
     * @param string $avrHost
     * @param int $avrPort
     * @param int $avrTimeout
     * @param Database $database
     * @param LoopInterface $loop
     * @throws AVRException
     */
    public function __construct($avrHost, $avrPort, $avrTimeout, Database $database, LoopInterface $loop)
    {
        $this->host = $avrHost;
        $this->port = $avrPort;
        $this->timeout = $avrTimeout;

        $this->database = $database;
        $this->loop = $loop;
    }

    /**
     * Reopen connection to worker module
     */
    public function reopenConnection()
    {
        if($this->connection) {
            $this->connection->end();
            $this->connection->close();
        }
        $connector = new \React\Socket\Connector($this->loop);
        $connector->connect('tcp://'.$this->host.':'.$this->port)->then(function (ConnectionInterface $conn) {
            $this->connection = $conn;
            
            $this->connection->on('data', function($chunk) {
                if($chunk == 'R') {
                    $this->loop->addTimer(15, [$this, 'reopenConnection']);
                }
            });
            
            $this->addAVRLog(LogEvents::AVR_CONNECTED, sprintf('Open connection to worker module on %s:%s', $this->host, $this->port));
        }, function(\Exception $e) {
            $this->addAVRLog(LogEvents::AVR_CRITICAL, sprintf('%s (%s)', $e->getMessage(), get_class($e)));
            throw new AVRException(sprintf('Cannot connect to AVR module on %s:%s.', $this->host, $this->port));           
        });
    }

    /**
     * @param $message
     * @return int
     */
    public function send($message)
    {
        try {
            $this->connection->write($message);
        } catch (\Exception $ex) {
            throw new AVRException($ex->getMessage());
        }
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