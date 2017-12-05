<?php

namespace Syntax\WebSocket\InMemory;

class Lights
{
    /**
     * @var array|bool[]
     */
    private $storage = [];

    /**
     * @var resource
     */
    private $connector;

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

    private $avr = [];

    /**
     * Lights constructor.
     * @param string $avrHost
     * @param int $avrPort
     * @param int $avrTimeout
     */
    public function __construct($avrHost, $avrPort, $avrTimeout)
    {
        for($i=0; $i<self::LIGHTS_STORAGE_SIZE; $i++) $this->storage[] = false;
        $this->avr['host'] = $avrHost;
        $this->avr['port'] = $avrPort;
        $this->avr['timeout'] = $avrTimeout;

        $this->reopenConnection();
    }

    /**
     * Reopen connection to worker module
     */
    private function reopenConnection()
    {
        if($this->connector) {
            fclose($this->connector);
        }

        if(!$this->__last_connection || time()-$this->__last_connection > static::CONNECTION_EXPIRE_SECONDS) {
            $this->connector = @fsockopen($this->avr['host'], $this->avr['port'], $errNo, $errStr, $this->avr['timeout']);
            if (!$this->connector) {
                if($this->__retry_counter++ < static::MAX_RETRY) {
//                    echo Output::put(sprintf('Cannot connect to worker module on %s:%s.', $this->avr['host'], $this->avr['port']), Output::F_RED) . PHP_EOL;
//                    echo Output::put(sprintf('%s (%s)', $errStr, $errNo), Output::F_RED) . PHP_EOL;
//                    echo Output::put('Retrying after 10 seconds..', Output::F_CYAN) . PHP_EOL;
                    sleep(10);
                    return $this->reopenConnection();
                }
//                echo Output::put(sprintf('Cannot connect to worker module on %s:%s.', $this->avr['host'], $this->avr['port']), Output::F_RED) . PHP_EOL;
//                echo Output::put(sprintf('%s (%s)', $errStr, $errNo), Output::F_RED) . PHP_EOL;
                exit;
            }

//            echo Output::put(sprintf('Open connection to worker module on %s:%s', $this->avr['host'], $this->avr['port']), Output::F_CYAN).PHP_EOL;
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
        fwrite($this->connector, $this->toByte());
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
        fwrite($this->connector, $this->toByte());
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
}