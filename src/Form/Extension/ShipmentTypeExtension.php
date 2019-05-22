<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ShipmentType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ShipmentTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pickupPointId', ChoiceType::class, [
            'label' => 'setono_sylius_pickup_point.form.shipment.pickup_point_id',
            'required' => false,
            'attr' => [
                'class' => 'input-pickup-point-id'
            ]
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShipmentType::class];
    }
}
