<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ShippingMethodTypeExtension extends AbstractTypeExtension
{
    /**
     * @var array
     */
    private $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pickupPointProvider', ChoiceType::class, [
            'placeholder' => 'setono_sylius_pickup_point.form.shipping_method.select_pickup_point_provider',
            'label' => 'setono_sylius_pickup_point.form.shipping_method.pickup_point_provider',
            'choices' => array_flip($this->providers),
        ]);
    }

    public function getExtendedTypes(): iterable
    {
        return [ShippingMethodType::class];
    }
}
