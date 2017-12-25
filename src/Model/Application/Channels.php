<?php

namespace Syntax\Model\Application;

class Channels
{
    public static $definition = [
        [
            'identity' => 0,
            'label' => 'White center'
        ],
        [
            'identity' => 2,
            'label' => 'Icicles'
        ],
        [
            'identity' => 1,
            'label' => 'White bottom'
        ],
        [
            'identity' => 3,
            'label' => 'Red serpent'
        ]
    ];
    
    public static function getById($identity) {
        foreach(self::$definition as $channel) {
            if($channel['identity'] = $identity) return $channel;
        }
        return null;
    }
}