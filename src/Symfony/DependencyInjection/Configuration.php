<?php

namespace eLife\Logging\Symfony\DependencyInjection;

use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('elife_logging');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('channel')->defaultValue('default')->end()
                ->scalarNode('level')->defaultValue(LogLevel::DEBUG)->end()
            ->end();

        return $treeBuilder;
    }
}
