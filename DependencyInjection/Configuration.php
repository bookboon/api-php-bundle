<?php

namespace Bookboon\ApiBundle\DependencyInjection;

use Bookboon\Api\Cache\RedisCache;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bookboonapi');

        $rootNode->children()
            ->scalarNode('id')->isRequired()->end()
            ->scalarNode('secret')->isRequired()->end()
            ->scalarNode('branding')->end()
            ->scalarNode('rotation')->end()
            ->scalarNode('currency')->end()
            ->scalarNode('impersonator_id')->defaultNull()->end()
            ->scalarNode('redirect')->defaultNull()->end()
            ->scalarNode('cache_service')->defaultValue(RedisCache::class)->end()
            ->arrayNode('languages')->isRequired()->prototype('scalar')->end()->end()
            ->arrayNode('scopes')->isRequired()->prototype('scalar')->end()->end()
            ->integerNode('premium_level')->end()
            ->scalarNode('override_api_uri')->defaultNull()->end()
            ->scalarNode('override_auth_uri')->defaultNull()->end()
            ;

        return $treeBuilder;
    }
}