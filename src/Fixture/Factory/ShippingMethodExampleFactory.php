<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Fixture\Factory;

use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ShippingMethodExampleFactory as BaseShippingMethodExampleFactory;
use Sylius\Component\Core\Model\ShippingMethodInterface as BaseShippingMethodInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodExampleFactory extends BaseShippingMethodExampleFactory
{
    use ShippingMethodExampleFactoryTrait;

    public function create(array $options = []): BaseShippingMethodInterface
    {
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = parent::create($options);

        $this->setPickupPointOptions($shippingMethod, $options);

        return $shippingMethod;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $this->configurePickupPointOptions($resolver);
    }
}
