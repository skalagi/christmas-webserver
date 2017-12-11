<?php

namespace Syntax\WebSocket\InMemory;

use Syntax\Exception\AVRException;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Syntax\Service\Database;

class Lights
{
    /**
     * @var array|bool[]
     */
    private $storage = [false, false, false, false];

    /**
     * @var resource
     */
    private $connector;

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
    const LIGHTS_STORAGE_SIZE = 4;

    /**
     * @var array
     */
    private $avr = [];

    /**
     * Lights constructor.
     * @param string $avrHost
     * @param int $avrPort
     * @param int $avrTimeout
     * @param Database $database
     * @throws AVRException
     */
    public function __construct($avrHost, $avrPort, $avrTimeout, Database $database)
    {
        $this->avr['host'] = $avrHost;
        $this->avr['port'] = $avrPort;
        $this->avr['timeout'] = $avrTimeout;
        $this->database = $database;

        $this->reopenConnection();
    }

    /**
     * Reopen connection to worker module
     */
    private function reopenConnection()
    {
        if(!$this->__last_connection || time()-$this->__last_connection > static::CONNECTION_EXPIRE_SECONDS) {
            if($this->connector) {
                fclose($this->connector);
            }

            $this->connector = @stream_socket_client('tcp://'.$this->avr['host'].':'.$this->avr['port'], $err, $errStr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT);
            if (!$this->connector) {
                if($this->__retry_counter++ < static::MAX_RETRY) {
                    $this->addLightsLog(
                        LogEvents::AVR_ERROR,
                        sprintf('Cannot connect to AVR module on %s:%s.'.PHP_EOL.'Retrying after 10 seconds..', $this->avr['host'], $this->avr['port'])
                    );
                    sleep(10);
                    return $this->reopenConnection();
                }

                $this->addLightsLog(LogEvents::AVR_CRITICAL, sprintf('%s (%s)', $errStr, $err));
                throw new AVRException(sprintf('Cannot connect to AVR module on %s:%s.', $this->avr['host'], $this->avr['port']));
            }

            $this->addLightsLog(LogEvents::AVR_CONNECTED, sprintf('Open connection to worker module on %s:%s', $this->avr['host'], $this->avr['port']));
            $this->__last_connection = time();
        }
        return null;
    }

    /**
     * Lights destructor.
     */
    public function __destruct()
    {
        if($this->connector) fclose($this->connector);
    }


    /**
     * @param int $offset
     * @return bool
     */
    public function getState($offset)
    {
        return $this->storage[$offset];
    }

    /**
     * @param int $offset
     * @param bool $value
     * @return $this
     */
    public function changeState($offset, $value)
    {
        $this->reopenConnection();

        $this->storage[$offset] = (bool)$value;
        fwrite($this->connector, 'P'.$this->toByte());
        return $this;
    }

    /**
     * @param int $byte
     * @return $this
     */
    public function setByte($byte)
    {
        $i=0;
        foreach(str_split(sprintf('%04d', decbin($byte))) as $bit) {
            $this->storage[$i++] = (bool)$bit;
        }
        fwrite($this->connector, 'P'.$this->toByte());
        return $this;
    }

    /**
     * @return array|\bool[]
     * @return null|int
     */
    public function states()
    {
        return $this->storage;
    }

    /**
     * @return int
     */
    public function toByte()
    {
        $convertedByte = 0;
        $weight = [8, 4, 2, 1];
        foreach($this->storage as $i => $bit) {
            if($bit) $convertedByte += $weight[$i];
        }
        return $convertedByte;
    }

    /**
     * @param $logEvent
     * @param $message
     */
    private function addLightsLog($logEvent, $message)
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