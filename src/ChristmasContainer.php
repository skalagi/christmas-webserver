<?php

namespace Syntax;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
}