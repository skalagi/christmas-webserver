<?php

namespace Syntax\Model\Application;

class LogEvents
{
    # WEBSOCKET SERVER
    const OPEN_CONNECTION = 'open_connection';
    const CLOSE_CONNECTION = 'close_connection';

    # CONTROLLERS
    const CHANGE_STATE_CONTROLLER = 'change_state_ctrl';
    const CHANGE_COLOR_CONTROLLER = 'change_color_ctrl';

    # ERRORS
    const AVR_ERROR = 'avr_error';
    const AVR_CRITICAL = 'avr_critical';
    const MESSAGE_COMPONENT_ERROR = 'mc_error';

    # COLORS QUEUE
    const COLOR_QUEUE_EXEC = 'color_queue_exec';

    # AVR 
    const AVR_CONNECTED = 'avr_connected';
    const AVR_MESSAGE = 'avr_msg';

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
        self::AVR_MESSAGE => 'Wiadomość ze sterownika AVR',
        self::AVR_ERROR => 'Błąd sterownika AVR',
        self::AVR_CRITICAL => 'Krytyczny błąd łączenia AVR',
        self::MESSAGE_COMPONENT_ERROR => 'Błąd serwera WS',
        self::COLOR_QUEUE_EXEC => 'Wysłanie zmiany koloru do AVR'
    ];

    /**
     * List types of logs which is an errors
     *
     * @var array
     */
    private static $errors = [
        self::AVR_ERROR, self::AVR_CRITICAL,
        self::MESSAGE_COMPONENT_ERROR
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

    /**
     * @param $eventName
     * @return bool
     */
    public static function isError($eventName)
    {
        return isset(self::$errors[$eventName]);
    }
}