<?php

namespace Syntax\Model\Transport;

/**
 * Class ColorChange
 * @property $hex public
 * @property $duration public
 * @package Syntax\Model\Transport
 */
class ColorChange extends AbstractTransportJSON
{
    /**
     * @inheritdoc
     */
    public function __construct(array $fields = [])
    {
        $this->fields['hex'] = null;
        $this->fields['duration'] = null;

        parent::__construct($fields);
    }
}