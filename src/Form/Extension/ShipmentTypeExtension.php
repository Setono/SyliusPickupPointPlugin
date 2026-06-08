<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ShipmentType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ShipmentTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // A hidden field, always present. Whether a pickup point is *required* depends on the chosen
        // shipping method, which is enforced by the HasPickupPointSelected validator on the shipment —
        // not here — so the field itself is optional at the form level.
        $builder->add('pickupPoint', PickupPointType::class, [
            'required' => false,
            'label' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShipmentType::class];
    }
}
