<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use Generator;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class PickupPointsSearchByCartAddressAction
{
    public function __construct(
        private SerializerInterface $serializer,
        private CartContextInterface $cartContext,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private ServiceRegistryInterface $providerRegistry,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();

        if (!$this->isCsrfTokenValid((string) $order->getId(), $request->get('_csrf_token'))) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Invalid CSRF token.');
        }

        $providerCode = $request->get('providerCode');
        if (!is_string($providerCode) || '' === $providerCode) {
            throw new NotFoundHttpException('Empty provider code');
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
        $pickupPoints = $provider->findPickupPoints($order);

        if ($pickupPoints instanceof Generator) {
            $pickupPoints = iterator_to_array($pickupPoints);
        }

        return new JsonResponse(
            $this->serializer->serialize($pickupPoints, 'json', ['groups' => ['Autocomplete']]),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    private function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($id, $token));
    }
}
