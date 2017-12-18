<?php

namespace Syntax\WebSocket\InMemory;

use React\EventLoop\LoopInterface;
use Syntax\Model\Application\ColorChange;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Syntax\Model\Transport\Broadcast\ChangeColorBroadcast;
use Syntax\Service\Database;
use Syntax\Service\WebSocket\AVRService;

class ColorChangesQueue
{
    /**
     * @var array|ColorChange[]
     */
    private $changes = [];

    /**
     * @var null|ColorChange
     */
    private $lastChange;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var AVRService
     */
    private $avr;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var Clients
     */
    private $clients;

    /**
     * Default duration in s
     *
     * @var int
     */
    private $defaultDuration;

    /**
     * ColorChangesQueue constructor.
     * @param LoopInterface $loop
     * @param AVRService $avr
     * @param Database $database
     * @param Clients $clients
     * @param $defaultDuration
     */
    public function __construct(LoopInterface $loop, AVRService $avr, Database $database, Clients $clients, $defaultDuration)
    {
        $this->avr = $avr;
        $this->loop = $loop;
        $this->database = $database;
        $this->clients = $clients;
        $this->defaultDuration = (int)$defaultDuration;
    }

    /**
     * @param ColorChange $change
     * @return ColorChangesQueue
     */
    public function addChange(ColorChange $change)
    {
        $this->changes[] = $change;
        return $this;
    }


    /**
     * Main function called in loop
     */
    public function queueLoopCall()
    {
        $nextChange = $this->getNextChange();
        $duration = $this->defaultDuration;

        if($nextChange instanceof ColorChange) {
            $duration = $nextChange->duration ? $nextChange->duration : $duration;
            $this->avr->send('L'.$nextChange->hex);

            $this->clients->broadcastMessage(new ChangeColorBroadcast([
                'value' => [
                    'hex' => $nextChange->hex
                ]
            ]), null);

            $this->logQueueExecute(
                $nextChange->hex,
                $duration,
                $nextChange->uid,
                $nextChange->ip
            );

            $this->lastChange = $nextChange;
        }

        $this->loop->addTimer($duration, [$this, 'queueLoopCall']);
    }

    /**
     * @return callable
     */
    private function getNextChange()
    {
        if(!$this->changes) {
            return null;
        }

        $current = reset($this->changes);
        unset($this->changes[0]);
        $this->changes = array_values($this->changes);
        return $current;
    }

    /**
     * @param string $hex
     * @param int $duration
     * @param int $rid
     * @param string $ip
     */
    private function logQueueExecute($hex, $duration, $rid, $ip)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime;
        $log->initiator = self::class.':'.__LINE__;
        $log->name = LogEvents::COLOR_QUEUE_EXEC;
        $log->data['hex'] = $hex;
        $log->data['duration'] = $duration ?: $this->defaultDuration;
        $log->data['rid'] = $rid;
        $log->data['ip'] = $ip;

        $this->database->addLog($log);
    }


    /**
     * @return null|ColorChange
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }
}