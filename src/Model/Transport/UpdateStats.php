<?php

namespace Syntax\Model\Transport;

/**
 * Class UpdateStats
 * @property $currentOnline public
 * @property $todayVisits public
 * @property $todayChanges public
 * @property $totalVisits public
 * @property $totalChanges public
 *
 * @package Syntax\Model\Transport
 */
class UpdateStats extends AbstractTransportJSON
{
    /**
     * @inheritdoc
     */
    public function __construct(array $fields)
    {
        $this->fields['currentOnline'] = null;
        $this->fields['todayVisits'] = null;
        $this->fields['todayChanges'] = null;
        $this->fields['totalVisits'] = null;
        $this->fields['totalChanges'] = null;

        parent::__construct($fields);
    }
}