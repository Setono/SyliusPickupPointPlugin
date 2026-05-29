<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use InvalidArgumentException;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoderInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class PickupPointByIdentifierAction
{
    public function __construct(
        private SerializerInterface $serializer,
        private PickupPointIdentifierEncoderInterface $encoder,
        private ProviderRegistryInterface $providerRegistry,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $identifier = $request->query->get('identifier');
        if (!is_string($identifier) || '' === $identifier) {
            throw new NotFoundHttpException();
        }

        try {
            $decoded = $this->encoder->decode($identifier);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException();
        }

        if (!$this->providerRegistry->has($decoded->provider)) {
            throw new NotFoundHttpException();
        }

        $pickupPoint = $this->providerRegistry->get($decoded->provider)->findPickupPoint($decoded->id, $decoded->metadata);
        if (null === $pickupPoint) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse(
            $this->serializer->serialize($pickupPoint, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
