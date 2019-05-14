<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection;

use Setono\SyliusPickupPointPlugin\Provider\PostNordProvider;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('setono_sylius_pickup_point');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('setono_sylius_pickup_point');
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('post_nord')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api_key')->end()
                        ->scalarNode('mode')->defaultValue(PostNordProvider::MODE_PRODUCTION)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
