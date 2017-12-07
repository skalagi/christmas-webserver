<?php

namespace Syntax\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Syntax\ChristmasContainer;
use Syntax\Exception\ControllersDispatcherException;
use Syntax\Model\Transport\AbstractTransportJSON;
use Syntax\WebSocket\ControllerInterface;

class ControllersDispatcher
{
    const CONTROLLERS_NAMESPACE = '\\Syntax\\Controller\\';
    const INPUT_MODEL_NAMESPACE = '\\Syntax\\Model\\Transport\\ControllerInput\\';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ControllersDispatcher constructor.
     */
    public function __construct()
    {
        $this->container = ChristmasContainer::getInstance();
    }

    /**
     * @param array $input
     * @return ControllerInterface
     * @throws ControllersDispatcherException
     */
    public function findController(array $input)
    {
        $this->_validateInput($input);

        $service = $this->container->get($input['controller']);
        if(!$service instanceof ControllerInterface) {
            throw new ControllersDispatcherException(sprintf(
                'Service "%s" is not controller',
                $input['controller']
            ));
        }

        return $service;
    }

    /**
     * @param array $input
     * @throws ControllersDispatcherException
     */
    private function _validateInput(array $input)
    {
        if(!isset($input['controller'])) {
            throw new ControllersDispatcherException('Missing "controller" field');
        }
        if(!isset($input['value'])) {
            throw new ControllersDispatcherException('Missing "value" field');
        }

        if(!$this->container->has($input['controller'])) {
            throw new ControllersDispatcherException('Not found controller!');
        }
    }

    /**
     * @param array $input
     * @return AbstractTransportJSON
     */
    public function prepareInput(array $input)
    {
        $inputClassName = self::INPUT_MODEL_NAMESPACE.$input['controller'];

        $instance = new $inputClassName;
        foreach($input['value'] as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }
}