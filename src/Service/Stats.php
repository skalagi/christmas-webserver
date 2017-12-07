<?php

namespace Syntax\Service;

use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Syntax\Model\Transport\UpdateStats;
use Syntax\WebSocket\InMemory\Clients;

class Stats
{
    /**
     * @var UpdateStats
     */
    private $model;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var Clients
     */
    private $clients;

    /**
     * @var int
     */
    private $currentOnline = 0;

    /**
     * Stats constructor.
     * @param Database $database
     * @param Clients $clients
     */
    public function __construct(Database $database, Clients $clients)
    {
        $this->database = $database;
        $this->clients = $clients;
        $this->model = new UpdateStats();
        $this->getModel();
    }

    /**
     * Add currentOnline and send to all clients
     */
    public function addCurrentOnline()
    {
        $this->model->currentOnline = ++$this->currentOnline;
    }

    /**
     * Remove currentOnline and send to all clients
     */
    public function removeCurrentOnline()
    {
        $this->model->currentOnline = --$this->currentOnline;
    }

    /**
     * @param array $trace
     * @param string $message
     */
    public function addMCError($trace, $message)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime;
        $log->initiator = self::class.': '.__LINE__;
        $log->name = LogEvents::MESSAGE_COMPONENT_ERROR;
        $log->data['trace'] = $trace;
        $log->data['message'] = $message;
        $this->database->addLog($log);
    }

    /**
     * @param int $rid
     * @param string $ip
     */
    public function addOpenConnection($rid, $ip)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime;
        $log->initiator = self::class.': '.__LINE__;
        $log->name = LogEvents::OPEN_CONNECTION;
        $log->data['rid'] = $rid;
        $log->data['ip'] = $ip;
        $this->database->addLog($log);
    }

    /**
     * @param int $rid
     * @param string $ip
     */
    public function addCloseConnection($rid, $ip)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime;
        $log->initiator = self::class.': '.__LINE__;
        $log->name = LogEvents::CLOSE_CONNECTION;
        $log->data['rid'] = $rid;
        $log->data['ip'] = $ip;
        $this->database->addLog($log);
    }

    /**
     * @return UpdateStats
     */
    public function getModel()
    {
        $this->model->todayChanges = count($this->database->getLogs([
            'name' => LogEvents::CHANGE_STATE_CONTROLLER,
            'created_time' => (new \DateTime())->format('Y-m-d').'%'
        ]));

        $this->model->todayChanges += count($this->database->getLogs([
            'name' => LogEvents::CHANGE_COLOR_CONTROLLER,
            'created_time' => (new \DateTime())->format('Y-m-d').'%'
        ]));

        $this->model->totalChanges = count($this->database->getLogs([
            'name' => LogEvents::CHANGE_STATE_CONTROLLER
        ]));

        $this->model->totalChanges += count($this->database->getLogs([
            'name' => LogEvents::CHANGE_COLOR_CONTROLLER
        ]));

        $this->model->todayVisits = count($this->database->getLogs([
            'name' => LogEvents::OPEN_CONNECTION,
            'created_time' => (new \DateTime())->format('Y-m-d').'%'
        ]));

        $this->model->totalVisits = count($this->database->getLogs([
            'name' => LogEvents::OPEN_CONNECTION
        ]));

        return $this->model;
    }

    /**
     * Send UpdateStats action to all connected clients
     */
    public function _sendStats()
    {
        foreach($this->clients->_all() as $client) {
            $client->send(json_encode([
                'action' => 'UpdateStats',
                'value' => json_decode($this->model->_toJSON())
            ]));
        }
    }
}