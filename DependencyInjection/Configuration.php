<?php

namespace Mongolog\Bundle\MongologBrowserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mongolog_browser');

        $rootNode
            ->children()
                ->scalarNode('base_layout')
                    ->cannotBeEmpty()
                    ->defaultValue('MongologBrowserBundle::layout.html.twig')
                ->end()
                ->scalarNode('logs_per_page')
                    ->cannotBeEmpty()
                    ->defaultValue(25)
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function($v) { return (int) $v; })
                    ->end()
                ->end()
                ->arrayNode('mongo')
                    ->children()
                        ->scalarNode('server')->cannotBeEmpty()->end()
                        ->scalarNode('collection')->cannotBeEmpty()->end()
                        ->scalarNode('database')->cannotBeEmpty()->end()
                        ->scalarNode('username')->end()
                        ->scalarNode('password')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
