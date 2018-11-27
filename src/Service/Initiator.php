<?php

namespace Syntax\Service;


use Ratchet\ConnectionInterface;
use Syntax\Model\AbstractModelWithStatus;
use Syntax\Model\ChangeColor;
use Syntax\Model\ChangeState;
use Syntax\Service\UC\LED;
use Syntax\Service\UC\Relays;

class Initiator
{
    /**
     * @var LED
     */
    private $led;

    /**
     * @var Relays
     */
    private $relays;

    /**
     * Initiator constructor.
     * @param LED $led
     * @param Relays $relays
     */
    public function __construct(LED $led, Relays $relays)
    {
        $this->led = $led;
        $this->relays = $relays;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function init(ConnectionInterface $conn)
    {
        // INIT RELAYS
        foreach($this->relays->getStates() as $id => $state) {
            $changeState = new ChangeState([]);
            $changeState->id = sha1(uniqid().time().uniqid());
            $changeState->status = AbstractModelWithStatus::STATUS_CURRENT;

            $changeState->channel = $id;
            $changeState->state = $state;

            $conn->send(json_encode($changeState));
        }

        // INIT LED
        $changeColor = new ChangeColor([]);
        $changeColor->id = sha1(uniqid().time().uniqid());
        $changeColor->status = AbstractModelWithStatus::STATUS_CURRENT;

        list($r, $g, $b) =  $this->led->getColor();
        $changeColor->r = $r;
        $changeColor->g = $g;
        $changeColor->b = $b;


        $conn->send(json_encode($changeColor));
    }
}