<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class PickupPointByIdAction
{
    public function __construct(
        private SerializerInterface $serializer,
        private DataTransformerInterface $pickupPointToIdentifierTransformer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $pickupPointId = $request->get('pickupPointId');
        if (!is_scalar($pickupPointId) || '' === $pickupPointId) {
            throw new NotFoundHttpException();
        }

        $pickupPoint = $this->pickupPointToIdentifierTransformer->reverseTransform($pickupPointId);
        if (!$pickupPoint instanceof PickupPoint) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse(
            $this->serializer->serialize($pickupPoint, 'json', ['groups' => ['Detailed']]),
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
