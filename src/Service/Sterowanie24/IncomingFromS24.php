<?php

namespace Syntax\Service\Sterowanie24;

use React\EventLoop\LoopInterface;
use React\Socket\Connector as SocketConnector;
use Ratchet\Client\Connector as ClientConnector;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Ratchet\RFC6455\Messaging\MessageInterface;

class IncomingFromS24 {
    /**
     * @var LoopInterface 
     */
    private $loop;
    
    const DNS = '8.8.8.8';
    const TIMEOUT = 10;
    
    /**
     * @var string 
     */
    private $s24Endpoint;
    
    /**
     * @param LoopInterface $loop
     * @param string $s24Endpoint
     */
    public function __construct(LoopInterface $loop, $s24Endpoint) {
        $this->loop = $loop;
        $this->s24Endpoint = $s24Endpoint;
    }
    
    public function connect() 
    {
        $reactConnector = new SocketConnector($this->loop, [
            'dns' => self::DNS,
            'timeout' => self::TIMEOUT
        ]);
        $connector = new ClientConnector($this->loop, $reactConnector);

        $connector($this->s24Endpoint, ['protocol1', 'subprotocol2'], ['Origin' => 'http://localhost'])
            ->then(function(Ratchet\Client\WebSocket $conn) {
                $conn->on('message', function(MessageInterface $msg) {
                    
                });


                $this->_log(['HOST' => $this->s24Endpoint], LogEvents::S24_INCOMING_CONNECTED);
            }, function(\Exception $e) {
                $this->_log(['e' => get_class($e), 'message' => $e->getMessage()], LogEvents::S24_ERROR);
            });
    }
    
    /**
     * @param mixed $data
     * @param string $type
     */
    private function _log($data, $type)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime;
        $log->initiator = self::class.': '.__LINE__;
        $log->name = $type;
        $log->data['trace'] = $data;
        $this->database->addLog($log);
    }
}
