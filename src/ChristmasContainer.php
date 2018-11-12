<?php

namespace Syntax;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Syntax\Service\Logger;

class ChristmasContainer
{
    const CONFIG_DIRECTORY =  __DIR__.'/..';
    const SERVICES_YAML_PATH = self::CONFIG_DIRECTORY.'/Resources/config/services.yml';
    const PARAMETERS_YAML_PATH = self::CONFIG_DIRECTORY.'/Resources/config/parameters.yml';

    /**
     * @var ChristmasContainer
     */
    private static $_instance;

    /**
     * @return ContainerInterface
     * @throws \Exception
     */
    public static function getInstance()
    {
        if(self::$_instance === null) {
            self::buildContainer();
        }

        return self::$_instance;
    }

    /**
     * Build DI container from configurations
     * @throws \Exception
     */
    public static function buildContainer()
    {
        $container = new ContainerBuilder();

        $loaderParameters = new YamlFileLoader($container, new FileLocator(self::CONFIG_DIRECTORY));
        $loaderParameters->load(self::PARAMETERS_YAML_PATH);

        $loaderServices = new YamlFileLoader($container, new FileLocator(self::CONFIG_DIRECTORY));
        $loaderServices->load(self::SERVICES_YAML_PATH);

        $container->compile();

        self::$_instance = $container;
    }


    /**
     * @return Logger|object
     * @throws \Exception
     */
    public static function getLogger()
    {
        return self::getInstance()->get('logger');
    }
}