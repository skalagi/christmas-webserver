<?php

namespace Syntax\Service\WebSocket;

use Syntax\Exception\AVRException;
use Syntax\Exception\AVRBusyException;
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
    private $busy = false;
    
    const MESSAGE_TIMEOUT = 6;

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
     * @param $msg
     * @param callable|null $successCallback
     * @param callable|null $errorCallback
     * @return int
     */
    public function send($msg, $successCallback = null, $errorCallback = null)
    {
        if($this->busy) {
            throw new AVRBusyException('AVR is busy!');
        }
        
        try {
            $connector = new \React\Socket\Connector($this->loop);
            $connector->connect('tcp://'.$this->host.':'.$this->port)->then(function (ConnectionInterface $conn) use($msg, $successCallback, $errorCallback) {
                $this->busy = true;
                $conn->write($msg);
                $errorTimer = $this->loop->addTimer(self::MESSAGE_TIMEOUT, function() use($conn, $errorCallback) {
                    $conn->end();
                    $conn->close();
                    if(is_callable($errorCallback)) {
                        $errorCallback();
                    }
                    $this->busy = false;
                });

                $conn->on('data', function($chunk) use($successCallback, $errorTimer, $conn) {
                    $this->loop->cancelTimer($errorTimer);
                    
                    $conn->end();
                    $conn->close();
                    $this->addAVRLog(LogEvents::AVR_MESSAGE, $chunk);
                    if(is_callable($successCallback)) {
                        $successCallback($chunk);
                    }
                    
                    $this->busy = false;
                });
            }, function(\Exception $e) {
                $this->addAVRLog(LogEvents::AVR_CRITICAL, sprintf('%s (%s)', $e->getMessage(), get_class($e)));
                throw new AVRException(sprintf('Cannot connect to AVR module on %s:%s.', $this->host, $this->port));           
            });
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