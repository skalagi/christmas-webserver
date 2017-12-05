<?php

namespace Syntax\Model\Transport;


/**
 * Class Error
 * @property $reason public
 * @property $type public
 *
 * @package Syntax\Model\Transport
 */
class Error extends AbstractTransportJSON
{
    /**
     * @inheritdoc
     */
    public function __construct(array $fields)
    {
        $this->fields['reason'] = null;
        $this->fields['type'] = null;

        parent::__construct($fields);
    }
}