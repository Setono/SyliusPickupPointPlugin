<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class PickupPointsSearchByCartAddressAction
{
    public function __construct(
        private CartContextInterface $cartContext,
        private ServiceRegistryInterface $providerRegistry,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();

        $providerCode = $request->query->get('provider');
        if (!is_string($providerCode) || '' === $providerCode) {
            throw new BadRequestHttpException('Empty provider code');
        }

        if (!$this->providerRegistry->has($providerCode)) {
            throw new NotFoundHttpException(sprintf(
                'Provider \'%s\' not recognized. Expecting one of: %s',
                $providerCode,
                implode(', ', array_keys($this->providerRegistry->all())),
            ));
        }

        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($providerCode);

        return new JsonResponse($provider->findPickupPoints($order));
    }
}
