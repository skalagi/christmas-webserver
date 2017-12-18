<?php

namespace Syntax\Model\Transport\Broadcast;
use Syntax\Model\Transport\AbstractTransportJSON;

/**
 * Class ChangeStateBroadcast
 * @property $input public
 * @package Syntax\Model\Transport
 */
class ChangeStateBroadcast extends AbstractTransportJSON
{
    /**
     * @inheritdoc
     */
    public function __construct(array $fields = [])
    {
        $this->fields['action'] = 'ChangeState';
        $this->fields['value'] = null;

        parent::__construct($fields);
    }
}