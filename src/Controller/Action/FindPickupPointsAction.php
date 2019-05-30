<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
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
     * @var ServiceRegistryInterface
     */
    private $providerRegistry;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        CartContextInterface $cartContext,
        CsrfTokenManagerInterface $csrfTokenManager,
        ServiceRegistryInterface $providerRegistry
    ) {
        $this->viewHandler = $viewHandler;
        $this->cartContext = $cartContext;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->providerRegistry = $providerRegistry;
    }

    public function __invoke(Request $request, string $providerCode): Response
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();

        if (!$this->isCsrfTokenValid((string) $order->getId(), $request->get('_csrf_token'))) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Invalid CSRF token.');
        }

        if (!$this->providerRegistry->has($providerCode)) {
            throw new NotFoundHttpException();
        }

        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($providerCode);
        $pickupPoints = $provider->findPickupPoints($order);

        $view = View::create($pickupPoints);
        $view->getContext()->addGroup('Autocomplete');
        return $this->viewHandler->handle($view);
    }

    private function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($id, $token));
    }
}
