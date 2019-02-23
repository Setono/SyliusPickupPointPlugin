<?php

declare(strict_types=1);

namespace spec\Setono\SyliusPickupPointPlugin\Form\Extension;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodChoiceTypeExtension;
use Setono\SyliusPickupPointPlugin\Manager\ProviderManagerInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ShippingMethodChoiceTypeExtensionSpec extends ObjectBehavior
{
    public function let(ProviderManagerInterface $providerManager, RouterInterface $router, CartContextInterface $cartContext, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->beConstructedWith($providerManager, $router, $cartContext, $csrfTokenManager);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ShippingMethodChoiceTypeExtension::class);
    }

    public function it_is_an_abstract_type_extension_form(): void
    {
        $this->shouldHaveType(AbstractTypeExtension::class);
    }

    public function it_takes_arguments(FormBuilderInterface $builder): void
    {
        $builder->add(Argument::type('string'), Argument::type('string'), Argument::any())->willReturn($builder);
        $this->buildForm($builder, []);
    }
}
