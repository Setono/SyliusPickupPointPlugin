<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointIdChoiceType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ShipmentType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ShipmentTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pickupPointId', PickupPointIdChoiceType::class, [
                'label' => 'setono_sylius_pickup_point.form.shipment.pickup_point',
                'placeholder' => 'setono_sylius_pickup_point.form.shipment.select_pickup_point',
                'required' => true,
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShipmentType::class];
    }
}
