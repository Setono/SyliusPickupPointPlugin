<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Setono\SyliusPickupPointPlugin\Manager\ProviderManagerInterface;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ShippingMethodTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ProviderManagerInterface
     */
    private $providerManager;

    /**
     * @param ProviderManagerInterface $providerManager
     */
    public function __construct(ProviderManagerInterface $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pickupPointProvider', ChoiceType::class, [
            'placeholder' => 'setono_sylius_pickup_point.form.shipping_method.select_pickup_point_provider',
            'label' => 'setono_sylius_pickup_point.form.shipping_method.pickup_point_provider',
            'choices' => $this->getChoices(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedTypes(): iterable
    {
        return [ShippingMethodType::class];
    }

    /**
     * @return array
     */
    private function getChoices(): array
    {
        $choices = [];

        foreach ($this->providerManager->all() as $provider) {
            $choices[$provider->getName()] = get_class($provider);
        }

        return $choices;
    }
}
