<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Stamps `data-pickup-point-provider="<code>"` onto each shipping-method radio that has a (registered)
 * provider. The shop JS reads this to know — immediately, before the AJAX response arrives — which
 * methods need pickup points, so it can show a "loading…" placeholder for the selected pickup method
 * instead of a blank gap while a (possibly slow) provider is being fetched.
 */
final class ShippingMethodChoiceTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly ProviderRegistryInterface $providerRegistry,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('choice_attr', function (PickupPointProviderAwareInterface $choiceValue): array {
            if (!$choiceValue->hasPickupPointProvider()) {
                return [];
            }

            $pickupPointProvider = $choiceValue->getPickupPointProvider();
            if (null === $pickupPointProvider || !$this->providerRegistry->has($pickupPointProvider)) {
                return [];
            }

            return ['data-pickup-point-provider' => $pickupPointProvider];
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodChoiceType::class];
    }
}
