<?php

namespace Syntax\Model\Application;

class LogEvents
{
    const OPEN_CONNECTION = 'open_connection';
    const CLOSE_CONNECTION = 'close_connection';
    const MESSAGE_COMPONENT_ERROR = 'mc_error';
    const CHANGE_STATE_CONTROLLER = 'change_state_ctrl';
    const CHANGE_COLOR_CONTROLLER = 'change_color_ctrl';
    const AVR_CONNECTED = 'avr_connected';
    const AVR_ERROR = 'avr_error';
    const AVR_CRITICAL = 'avr_critical';
    const BUSY_ERROR = 'busy_error';

    /**
     * Event name's labels
     *
     * @var array
     */
    private static $labels = [
        self::OPEN_CONNECTION => 'Rozpoczęcie połączenia',
        self::CLOSE_CONNECTION => 'Zakończenie połączenia',
        self::MESSAGE_COMPONENT_ERROR => 'Błąd MessageComponent: %s',
        self::CHANGE_COLOR_CONTROLLER => 'Zmiana koloru',
        self::CHANGE_STATE_CONTROLLER => 'Zmiana stanu',
        self::AVR_CONNECTED => 'Podłączono do sterownika',
        self::AVR_ERROR => 'Błąd sterownika AVR',
        self::BUSY_ERROR => 'Wyrzucenie klienta'
    ];

    /**
     * @param $eventName
     * @return null|string
     */
    public static function getLabel($eventName)
    {
        if(!isset(self::$labels[$eventName])) {
            return null;
        }

        return self::$labels[$eventName];
    }
}