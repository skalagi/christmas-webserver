<?php

namespace Syntax\Model\Transport\Broadcast;

use Syntax\Model\Transport\AbstractTransportJSON;

class ServerRestartBroadcast extends AbstractTransportJSON
{
    /**
     * @inheritdoc
     */
    public function __construct(array $fields = [])
    {
        $this->fields['action'] = 'ServerRestart';
        $this->fields['value'] = [];

        parent::__construct($fields);
    }
}