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
    const BUSY_ERROR = 'busy_error';
    const MESSAGE_COMPONENT_ERROR = 'mc_error';
    const S24_ERROR = 's24_error';

    # COLORS QUEUE
    const COLOR_QUEUE_EXEC = 'color_queue_exec';

    # AVR SUCCESS
    const AVR_CONNECTED = 'avr_connected';
    
    # STEROWANIE24
    const S24_INCOMING_CONNECTED = 's24_inc_connected';
    const S24_OUTGOING_CONNECTED = 's24_out_connected';
    const S24_INCOMING_STATE = 's24_incoming_state';
    const s24_OUTGOING_STATE = 's24_outgoing_state';
    

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
        self::BUSY_ERROR => 'Zajętość modułu przekaźnikowego',
        self::S24_ERROR => 'Błąd obsługi STEROWANIE24',
        self::COLOR_QUEUE_EXEC => 'Wysłanie zmiany koloru do AVR',
        self::S24_INCOMING_CONNECTED => 'Podłączono klienta odbiorcu STEROWANIE24',
        self::S24_OUTGOING_CONNECTED => 'Podłączono klienta nadawania STEROWANIE24',
        self::S24_INCOMING_STATE => 'Przychodząca zmiana stanu STEROWANIE24',
        self::s24_OUTGOING_STATE => 'Wychodząca zmiana stanu STEROWANIE24',
    ];

    /**
     * List types of logs which is an errors
     *
     * @var array
     */
    private static $errors = [
        self::AVR_ERROR, self::AVR_CRITICAL,
        self::BUSY_ERROR, self::MESSAGE_COMPONENT_ERROR
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