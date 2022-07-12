<?php

namespace Bookboon\ApiBundle\DependencyInjection;

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
        $holder = $this->getConfiguration($configs, $container);
        if (!$holder) {
            throw new \InvalidArgumentException("nulled config");
        }

        $config = $this->processConfiguration($holder, $configs);

        $container->register(ConfigurationHolder::class, ConfigurationHolder::class)
            ->addArgument($config)
            ->setPublic(false);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');
    }

    public function getAlias() : string
    {
        return 'bookboonapi';
    }

    public function getXsdValidationBasePath() : string
    {
        return 'http://bookboon.com/schema/dic/' . $this->getAlias();
    }
}
