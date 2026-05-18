<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ShippingMethodChoiceTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly ServiceRegistryInterface $providerRegistry,
        private readonly CartContextInterface $cartContext,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
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

            /** @var ProviderInterface $provider */
            $provider = $this->providerRegistry->get($pickupPointProviderId);

            return [
                'data-pickup-point-provider' => $provider->getCode(),
                'data-csrf-token' => $this->csrfTokenManager->getToken((string) $this->cartContext->getCart()->getId()),
            ] + $defaultAttr;
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodChoiceType::class];
    }
}
