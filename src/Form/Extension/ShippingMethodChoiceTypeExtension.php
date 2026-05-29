<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShippingMethodChoiceTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly ProviderRegistryInterface $providerRegistry,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaultAttr = ['class' => 'input-shipping-method'];

        $resolver->setDefault('choice_attr', function (PickupPointProviderAwareInterface $choiceValue, $key, $value) use ($defaultAttr): array {
            if (!$choiceValue->hasPickupPointProvider()) {
                return $defaultAttr;
            }

            /** @var string $pickupPointProviderId */
            $pickupPointProviderId = $choiceValue->getPickupPointProvider();
            if (!$this->providerRegistry->has($pickupPointProviderId)) {
                return $defaultAttr;
            }

            return [
                'data-pickup-point-provider' => $pickupPointProviderId,
            ] + $defaultAttr;
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodChoiceType::class];
    }
}
