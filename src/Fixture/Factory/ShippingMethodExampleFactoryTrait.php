<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Fixture\Factory;

use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait ShippingMethodExampleFactoryTrait
{
    /**
     * @param array<string, mixed> $options
     */
    protected function setPickupPointOptions(ShippingMethodInterface $shippingMethod, array $options): void
    {
        if (!array_key_exists('pickup_point_provider', $options)) {
            return;
        }

        $value = $options['pickup_point_provider'];
        if (null !== $value && !is_string($value)) {
            return;
        }

        $shippingMethod->setPickupPointProvider($value);
    }

    protected function configurePickupPointOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('pickup_point_provider')
            ->setAllowedTypes('pickup_point_provider', ['null', 'string'])
        ;
    }
}
