<?php

namespace Bookboon\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('bookboonapi');

        $treeBuilder
            ->getRootNode()
            ->children()
            ->scalarNode('id')->isRequired()->end()
            ->scalarNode('secret')->isRequired()->end()
            ->scalarNode('branding')->end()
            ->scalarNode('rotation')->end()
            ->scalarNode('currency')->end()
            ->scalarNode('impersonator_id')->defaultNull()->end()
            ->scalarNode('redirect')->defaultNull()->end()
            ->arrayNode('languages')->isRequired()->prototype('scalar')->end()->end()
            ->arrayNode('scopes')->isRequired()->prototype('scalar')->end()->end()
            ->scalarNode('override_auth_uri')->defaultNull()->end()
            ->integerNode('premium_level')->setDeprecated('api-php-bundle', 'v4.5')->end()
            ;

        return $treeBuilder;
    }
}
