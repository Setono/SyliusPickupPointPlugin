<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\ShippingMethodFixture as BaseShippingMethodFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ShippingMethodFixture extends BaseShippingMethodFixture
{
    public function getName(): string
    {
        return 'setono_sylius_pickup_point_shipping_method';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        parent::configureResourceNode($resourceNode);

        $resourceNode
            ->children()
                ->scalarNode('pickup_point_provider')
                    ->cannotBeEmpty()
                ->end()
        ;
    }
}
