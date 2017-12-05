<?php

namespace Syntax\Model\Transport\ControllerInput;

use Syntax\Model\Transport\AbstractTransportJSON;

/**
 * Class ChangeState
 * @property $identity public
 * @property $state public
 *
 * @package Syntax\Model\Transport
 */
class ChangeState extends AbstractTransportJSON
{
    /**
     * @inheritdoc
     */
    public function __construct(array $fields = [])
    {
        $this->fields['identity'] = null;
        $this->fields['state'] = null;

        parent::__construct($fields);
    }
}