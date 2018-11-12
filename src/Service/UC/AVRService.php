<?php

namespace Syntax\Service\UC;

use Syntax\ChristmasContainer;
use Syntax\Exception\AVRException;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use Syntax\Service\Logger;

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
     * @var LoopInterface
     */
    private $loop;

    /**
     * AVRService constructor.
     * @param string $avrHost
     * @param int $avrPort
     * @param int $avrTimeout
     * @param LoopInterface $loop
     */
    public function __construct($avrHost, $avrPort, $avrTimeout, LoopInterface $loop)
    {
        $this->host = $avrHost;
        $this->port = $avrPort;
        $this->timeout = $avrTimeout;

        $this->loop = $loop;
    }

    /**
     * @param $msg
     * @return void
     * @throws AVRException
     * @throws \Exception
     */
    public function send($msg)
    {
        ChristmasContainer::getLogger()->addLog(Logger::AVR, 'Sending payload: '.$msg, null, null);

        try {
            $connector = new \React\Socket\Connector($this->loop);
            $connector->connect('tcp://'.$this->host.':'.$this->port)->then(function (ConnectionInterface $conn) use($msg) {
                $conn->write($msg);

                $this->loop->addTimer(1, function() use($conn) {
                    $conn->close();
                });
            }, function(\Exception $e) {
                throw new AVRException(sprintf('Cannot connect to AVR module on %s:%s. Reason: '.$e->getMessage().' '.$e->getTraceAsString(), $this->host, $this->port));
            });
        } catch (\Exception $ex) {
            throw new AVRException($ex->getMessage());
        }
    }
}