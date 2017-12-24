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
            try {
                $this->avr->send('L'.$nextChange->rgb[0].'A'.$nextChange->rgb[1].'A'.$nextChange->rgb[2]);
            } catch (\Syntax\Exception\AVRException $ex) {
                $errTransport = new \Syntax\Model\Transport\Error();
                $errTransport->reason = 'Colors queue fail when connecting to AVR!';
                $errTransport->type = get_class($ex);
                $nextChange->connection->send($errTransport->_toJSON());
                
                $this->addAVRLog(LogEvents::AVR_ERROR, $ex->getMessage());
            }
            

            $this->clients->broadcastMessage(new ChangeColorBroadcast([
                'value' => [
                    'rgb' => $nextChange->rgb
                ]
            ]), null);

            $this->logQueueExecute(
                'R='.$nextChange->rgb[0].' G='.$nextChange->rgb[1].' B='.$nextChange->rgb[2],
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
     * @param string $rgb
     * @param int $duration
     * @param int $rid
     * @param string $ip
     */
    private function logQueueExecute($rgb, $duration, $rid, $ip)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime;
        $log->initiator = self::class.':'.__LINE__;
        $log->name = LogEvents::COLOR_QUEUE_EXEC;
        $log->data['colors'] = $rgb;
        $log->data['duration'] = $duration ?: $this->defaultDuration;
        $log->data['rid'] = $rid;
        $log->data['ip'] = $ip;

        $this->database->addLog($log);
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


    /**
     * @return null|ColorChange
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }
}