<?php

declare(strict_types=1);

namespace spec\Setono\SyliusPickupPointPlugin\Controller\Action;

use FOS\RestBundle\View\ViewHandlerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Setono\SyliusPickupPointPlugin\Controller\Action\FindPickupPointsAction;
use Setono\SyliusPickupPointPlugin\Manager\ProviderManagerInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\ShippingMethodRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class FindPickupPointsActionSpec extends ObjectBehavior
{
    function let(
        ViewHandlerInterface $viewHandler,
        CartContextInterface $cartContext,
        CsrfTokenManagerInterface $csrfTokenManager,
        ProviderManagerInterface $providerManager,
        ShippingMethodRepositoryInterface $shippingMethodRepository
    ): void {
        $this->beConstructedWith(
            $viewHandler,
            $cartContext,
            $csrfTokenManager,
            $providerManager,
            $shippingMethodRepository
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(FindPickupPointsAction::class);
    }

    function it_finds_pickup_points(
        Request $request,
        CartContextInterface $cartContext,
        OrderInterface $order,
        ProviderManagerInterface $providerManager,
        CsrfTokenManagerInterface $csrfTokenManager,
        ProviderInterface $provider,
        ViewHandlerInterface $viewHandler,
        Response $response
    ): void {
        $order->getId()->willReturn(1);
        $cartContext->getCart()->willReturn($order);
        $request->get('_csrf_token')->willReturn('token');
        $providerManager->findByCode('gls')->willReturn($provider);
        $csrfTokenManager->isTokenValid(Argument::any())->willReturn(true);
        $viewHandler->handle(Argument::any())->willReturn($response);

        $provider->findPickupPoints($order)->willReturn([])->shouldBeCalled();

        $this->__invoke($request, 'gls');
    }
}
