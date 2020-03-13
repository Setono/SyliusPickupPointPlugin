<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Fixture;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

trait ShippingMethodFixtureTrait
{
    protected function configurePickupPointResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
                ->scalarNode('pickup_point_provider')
                    ->cannotBeEmpty()
                ->end()
        ;
    }
}
