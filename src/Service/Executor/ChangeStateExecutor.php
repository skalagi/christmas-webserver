<?php

namespace Syntax\Service\Executor;

use Syntax\Model\AbstractModelWithStatus;
use Syntax\Model\ChangeState;
use Syntax\Service\UC\Relays;

class ChangeStateExecutor extends AbstractExecutor
{
    /**
     * @var Relays
     */
    private $relays;

    /**
     * ChangeStateExecutor constructor.
     * @param Relays $relays
     */
    public function __construct(Relays $relays)
    {
        $this->relays = $relays;
    }

    /**
     * @param ChangeState $data
     * @throws \Syntax\Exception\AVRException
     */
    public function execute($data)
    {
        $this->relays->setState($data->id, $data->state);
        $data->status = AbstractModelWithStatus::STATUS_EXECUTED;
        $this->clients->broadcastMessage(json_encode($data));
    }
}