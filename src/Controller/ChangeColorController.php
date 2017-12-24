<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use Syntax\Exception\ChangeColorException;
use Syntax\Model\Application\ColorChange;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Syntax\Model\Transport\ControllerInput\ChangeColor;
use Syntax\Model\Transport\ColorChange as TransportColorChange;
use Syntax\Service\Database;
use Syntax\WebSocket\ControllerInterface;
use Syntax\WebSocket\InMemory\ColorChangesQueue;

class ChangeColorController implements ControllerInterface
{
    /**
     * @var ColorChangesQueue
     */
    private $queue;

    /**
     * @var Database
     */
    private $database;

    const MAX_TOTAL_DURATION_OF_CHANGE_SET = 20; // in seconds

    /**
     * ChangeColorController constructor.
     * @param ColorChangesQueue $queue
     * @param Database $database
     */
    public function __construct(ColorChangesQueue $queue, Database $database)
    {
        $this->queue = $queue;
        $this->database = $database;
    }

    /**
     * @param ChangeColor $input
     * @param ConnectionInterface $from
     * @return array
     * @throws ChangeColorException
     */
    public function execute($input, ConnectionInterface &$from)
    {
        $changes = [];
        /** @var TransportColorChange $change */
        foreach($input->changes as $change) {
            /** @noinspection PhpUndefinedFieldInspection */
            $changes[] = $this->createAppChangeColorFromTransport(
                $change,
                (int)$from->resourceId,
                $from->httpRequest->getHeader('X-Forwarded-For')[0]
            );

        }

        if($this->sumTotalDuration($changes) > self::MAX_TOTAL_DURATION_OF_CHANGE_SET) {
            throw new ChangeColorException('Max changes limit reached!');
        }

        /** @var ColorChange $change */
        foreach($changes as $change) {
            $this->queue->addChange($change);
            /** @noinspection PhpUndefinedFieldInspection */
            $this->logColorChange($change->hex, $change->duration, (int)$from->resourceId, $from->httpRequest->getHeader('X-Forwarded-For')[0]);
        }
    }

    /**
     * @param array|ColorChange[] $changes
     * @return int
     */
    private function sumTotalDuration(array $changes)
    {
        $sum = 0;
        foreach($changes as $change) {
            $sum += $change->duration;
        }

        return $sum;
    }

    /**
     * @param TransportColorChange $sourceChange
     * @param int $rid
     * @param string $ip
     * @return ColorChange
     */
    private function createAppChangeColorFromTransport(TransportColorChange $sourceChange, $rid, $ip) {
        $change = new ColorChange();
        $change->rgb = $sourceChange->rgb;
        $change->duration = $sourceChange->duration;
        $change->uid = $rid;
        $change->ip = $ip;
        return $change;
    }

    /**
     * @param string $hex
     * @param int $duration
     * @param int $rid
     * @param string $ip
     */
    private function logColorChange($hex, $duration, $rid, $ip)
    {
        $log = new LogEntity();
        $log->name = LogEvents::CHANGE_COLOR_CONTROLLER;
        $log->createdTime = new \DateTime;
        $log->initiator = __CLASS__.':'.__LINE__;
        $log->data['hex'] = $hex;
        $log->data['duration'] = $duration;
        $log->data['rid'] = $rid;
        $log->data['ip'] = $ip;

        $this->database->addLog($log);
    }
}