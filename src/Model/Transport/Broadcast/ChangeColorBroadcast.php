<?php

namespace Syntax\Model\Transport\Broadcast;

use Syntax\Model\Transport\AbstractTransportJSON;

class ChangeColorBroadcast extends AbstractTransportJSON
{
    /**
     * @inheritdoc
     */
    public function __construct(array $fields = [])
    {
        $this->fields['action'] = 'ChangeColor';
        $this->fields['value'] = null;

        parent::__construct($fields);
    }
}