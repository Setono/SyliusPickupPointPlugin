<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection;

use Setono\DAOBundle\SetonoDAOBundle;
use Setono\GlsWebserviceBundle\SetonoGlsWebserviceBundle;
use Setono\PostNordBundle\SetonoPostNordBundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('setono_sylius_pickup_point');
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress MixedMethodCall,PossiblyUndefinedMethod,PossiblyNullReference */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('driver')->defaultValue(SyliusResourceBundle::DRIVER_DOCTRINE_ORM)->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('pool')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('providers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('faker')
                            ->info('Whether to enable the Faker provider')
                            ->defaultValue(false)
                        ->end()
                        ->booleanNode('dao')
                            ->example(true)
                            ->info('Whether to enable the DAO provider')
                            ->defaultValue(class_exists(SetonoDAOBundle::class))
                        ->end()
                        ->booleanNode('gls')
                            ->example(true)
                            ->info('Whether to enable the GLS provider')
                            ->defaultValue(class_exists(SetonoGlsWebserviceBundle::class))
                        ->end()
                        ->booleanNode('post_nord')
                            ->example(true)
                            ->info('Whether to enable the PostNord provider')
                            ->defaultValue(class_exists(SetonoPostNordBundle::class))
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
