<?php

namespace Syntax\WebSocket;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Syntax\ChristmasContainer;
use Syntax\Controller\Controller;
use Syntax\Exception\ApplicationException;

class ControllersDispatcher
{
    const CONTROLLERS_NAMESPACE = '\\Syntax\\Controller\\';

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
     * @param array|mixed $input
     * @return Controller
     * @throws ApplicationException
     */
    public function findController($input)
    {
        $this->_validateInput($input);

        $service = $this->container->get($input['controller'].'Controller');
        if(!$service instanceof Controller) {
            throw new ApplicationException(sprintf(
                'Service "%s" is not controller',
                $input['controller']
            ));
        }

        return $service;
    }

    /**
     * @param array|mixed $input
     * @throws ApplicationException
     */
    private function _validateInput($input)
    {
        if(!is_array($input)) {
            throw new ApplicationException('Invalid JSON as input');
        }

        if(!isset($input['controller'])) {
            throw new ApplicationException('Missing "controller" field');
        }

        if(!$this->container->has($input['controller'].'Controller')) {
            throw new ApplicationException('Not found controller!');
        }
    }
}