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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ShippingMethodChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ServiceRegistryInterface
     */
    private $providerRegistry;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CartContextInterface
     */
    private $cartContext;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    public function __construct(
        ServiceRegistryInterface $providerRegistry,
        RouterInterface $router,
        CartContextInterface $cartContext,
        CsrfTokenManagerInterface $csrfTokenManager
    ) {
        $this->providerRegistry = $providerRegistry;
        $this->router = $router;
        $this->cartContext = $cartContext;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaultAttr = ['class' => 'input-shipping-method'];

        $resolver->setDefault('choice_attr', function (PickupPointProviderAwareInterface $choiceValue, $key, $value) use ($defaultAttr) {
            if (!$choiceValue->hasPickupPointProvider()) {
                return $defaultAttr;
            }

            if (!$this->providerRegistry->has($choiceValue->getPickupPointProvider())) {
                return $defaultAttr;
            }

            /** @var ProviderInterface $provider */
            $provider = $this->providerRegistry->get($choiceValue->getPickupPointProvider());

            return [
                'data-pickup-point-provider' => $provider->getCode(),
                'data-pickup-point-provider-url' => $this->router->generate('setono_sylius_pickup_point_shop_ajax_find_pickup_points', ['providerCode' => $provider->getCode()]),
                'data-csrf-token' => $this->csrfTokenManager->getToken((string) $this->cartContext->getCart()->getId()),
            ] + $defaultAttr;
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodChoiceType::class];
    }
}
