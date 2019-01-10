<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Extension;

use Setono\SyliusPickupPointPlugin\Manager\ProviderManagerInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ShippingMethodChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ProviderManagerInterface
     */
    private $providerManager;

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

    /**
     * @param ProviderManagerInterface $providerManager
     * @param RouterInterface $router
     * @param CartContextInterface $cartContext
     * @param CsrfTokenManagerInterface $csrfTokenManager
     */
    public function __construct(ProviderManagerInterface $providerManager, RouterInterface $router, CartContextInterface $cartContext, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->providerManager = $providerManager;
        $this->router = $router;
        $this->cartContext = $cartContext;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaultAttr = ['class' => 'input-shipping-method'];

        $resolver->setDefault('choice_attr', function (PickupPointProviderAwareInterface $choiceValue, $key, $value) use ($defaultAttr) {
            if ($choiceValue->hasPickupPointProvider()) {
                $provider = $this->providerManager->findByClassName($choiceValue->getPickupPointProvider());
                if (!$provider) {
                    return $defaultAttr;
                }

                return [
                        'data-pickup-point-provider' => $provider->getCode(),
                        'data-pickup-point-provider-url' => $this->router->generate('setono_sylius_pickup_point_shop_ajax_find_pickup_points', ['providerCode' => $provider->getCode()]),
                        'data-csrf-token' => $this->csrfTokenManager->getToken((string) $this->cartContext->getCart()->getId()),
                    ] + $defaultAttr;
            }

            return $defaultAttr;
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodChoiceType::class];
    }
}
