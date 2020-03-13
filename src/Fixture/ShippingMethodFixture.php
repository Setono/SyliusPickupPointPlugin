<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\ShippingMethodFixture as BaseShippingMethodFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ShippingMethodFixture extends BaseShippingMethodFixture
{
    use ShippingMethodFixtureTrait;

    public function getName(): string
    {
        return 'setono_sylius_pickup_point_shipping_method';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        parent::configureResourceNode($resourceNode);

        $this->configurePickupPointResourceNode($resourceNode);
    }
}
