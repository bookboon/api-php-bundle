<?php

namespace Bookboon\ApiBundle\DependencyInjection;

use Bookboon\Api\Cache\Cache;
use Bookboon\ApiBundle\Configuration\ApiConfiguration;
use Bookboon\ApiBundle\Helper\ConfigurationHolder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BookboonApiExtension extends Extension
{

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->register(ConfigurationHolder::class, ConfigurationHolder::class)
            ->addArgument($config)
            ->setPublic(false);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'bookboonapi';
    }

    public function getXsdValidationBasePath()
    {
        return 'http://bookboon.com/schema/dic/' . $this->getAlias();
    }
}
