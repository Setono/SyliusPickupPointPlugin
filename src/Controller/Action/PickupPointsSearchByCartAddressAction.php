<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class PickupPointsSearchByCartAddressAction
{
    public function __construct(
        private CartContextInterface $cartContext,
        private ProviderRegistryInterface $providerRegistry,
        private SerializerInterface $serializer,
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

        // The PickupPointNormalizer adds the encoded `identifier` to each serialized pickup point;
        // it owns that step because the identifier needs the encoder service, which the value
        // object cannot hold. Serializing (rather than json_encode) is what lets the normalizer run.
        return new JsonResponse(
            $this->serializer->serialize(
                $this->providerRegistry->get($providerCode)->findPickupPoints(Address::fromOrder($order)),
                'json',
            ),
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
