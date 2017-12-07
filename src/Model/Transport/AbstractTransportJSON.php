<?php

namespace Syntax\Model\Transport;

abstract class AbstractTransportJSON
{
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * AbstractTransportJSON constructor.
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        foreach($fields as $key => $value) {
            $this->fields[$key] = $value;
        }
    }

    /**
     * @return string
     */
    public function _toJSON()
    {
        return json_encode($this->fields);
    }

    /**
     * @param $jsonString
     * @return mixed
     */
    public static function _fromJson($jsonString)
    {
        $className = static::class;
        return new $className(json_decode($jsonString, JSON_OBJECT_AS_ARRAY));
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if(!isset($this->fields[$name])) return null;

        return $this->fields[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}