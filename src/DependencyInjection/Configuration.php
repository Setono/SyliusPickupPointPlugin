<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection;

use Setono\SyliusPickupPointPlugin\Provider\PostNordProvider;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('setono_sylius_pickup_point');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('postnord')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('apiKey')->end()
                        ->scalarNode('mode')->defaultValue(PostNordProvider::MODE_PRODUCTION)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
