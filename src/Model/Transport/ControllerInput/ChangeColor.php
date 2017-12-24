<?php

namespace Syntax\Model\Transport\ControllerInput;

use Syntax\Model\Transport\AbstractTransportJSON;
use Syntax\Model\Transport\ColorChange;

/**
 * Class ChangeColor
 * @property array|mixed changes
 * @package Syntax\Model\Transport\ControllerInput
 */
class ChangeColor extends AbstractTransportJSON
{
    /**
     * @inheritdoc
     */
    public function __construct(array $fields = [])
    {
        $this->fields['changes'] = [];

        parent::__construct($fields);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if($name == 'changes') {
            $changes = [];
            foreach($this->fields['changes'] as $sourceChange) {
                $change = new ColorChange();
                $change->hex = $sourceChange['rgb'];
                $change->duration = $sourceChange['duration'];
                $changes[] = $change;
            }

            return $changes;
        }


        return parent::__get($name);
    }
}