<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Fixture\Factory;

use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ShippingMethodExampleFactory as BaseShippingMethodExampleFactory;
use Sylius\Component\Core\Model\ShippingMethodInterface as BaseShippingMethodInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodExampleFactory extends BaseShippingMethodExampleFactory
{
    public function create(array $options = []): BaseShippingMethodInterface
    {
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = parent::create($options);

        if (array_key_exists('pickup_point_provider', $options)) {
            $shippingMethod->setPickupPointProvider($options['pickup_point_provider']);
        }

        return $shippingMethod;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefined('pickup_point_provider')
            ->setAllowedTypes('pickup_point_provider', ['null', 'string'])
        ;
    }
}
