<?php

namespace Syntax\Service\Executor;

use Syntax\Model\AbstractModelWithStatus;
use Syntax\Model\ChangeColor;
use Syntax\Service\UC\LED;

class ChangeColorExecutor extends AbstractExecutor
{
    /**
     * @var LED
     */
    private $led;

    /**
     * ChangeColorExecutor constructor.
     * @param LED $led
     */
    public function __construct(LED $led)
    {
        $this->led = $led;
    }

    /**
     * @param ChangeColor $data
     */
    public function execute($data)
    {
        $this->led->setColor($data->r, $data->g, $data->b);
        $data->status = AbstractModelWithStatus::STATUS_EXECUTED;
        $this->clients->broadcastMessage(json_encode($data));
    }
}