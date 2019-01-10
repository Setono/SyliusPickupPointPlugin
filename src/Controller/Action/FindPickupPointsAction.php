<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Setono\SyliusPickupPointPlugin\Manager\ProviderManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class FindPickupPointsAction
{
    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @var CartContextInterface
     */
    private $cartContext;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var ProviderManagerInterface
     */
    private $providerManager;

    /**
     * @param ViewHandlerInterface $viewHandler
     * @param CartContextInterface $cartContext
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param ProviderManagerInterface $providerManager
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        CartContextInterface $cartContext,
        CsrfTokenManagerInterface $csrfTokenManager,
        ProviderManagerInterface $providerManager
    ) {
        $this->viewHandler = $viewHandler;
        $this->cartContext = $cartContext;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->providerManager = $providerManager;
    }

    /**
     * @param Request $request
     * @param string $providerCode
     *
     * @return Response
     */
    public function __invoke(Request $request, string $providerCode): Response
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();

        if (!$this->isCsrfTokenValid((string) $order->getId(), $request->get('_csrf_token'))) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Invalid CSRF token.');
        }

        $provider = $this->providerManager->findByCode($providerCode);

        if (null === $provider) {
            throw new NotFoundHttpException();
        }

        $pickupPoints = $provider->findPickupPoints($order);

        return $this->viewHandler->handle(View::create($pickupPoints));
    }

    /**
     * @param string $id
     * @param string|null $token
     *
     * @return bool
     */
    private function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($id, $token));
    }
}
