<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use Doctrine\Persistence\ObjectRepository;
use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoderInterface;
use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns, for the current cart, the pickup points of every enabled shipping method that has a provider,
 * keyed by shipping method code. The checkout page calls this asynchronously *after* it has rendered, so a
 * slow or down carrier API never blocks the shipping page — and a single failing provider only empties its
 * own entry (the per-provider try/catch) instead of taking the others down.
 *
 * Each point carries an opaque `value` token ({@see PickupPointEncoderInterface}) that the shop JS uses as
 * the selected radio's value; on submit the hidden field's transformer decodes it back into the point, so
 * nothing is re-fetched server-side.
 */
final readonly class PickupPointsAction
{
    /**
     * @param ObjectRepository<ShippingMethodInterface> $shippingMethodRepository
     */
    public function __construct(
        private CartContextInterface $cartContext,
        private ProviderRegistryInterface $providerRegistry,
        private PickupPointEncoderInterface $encoder,
        private ObjectRepository $shippingMethodRepository,
    ) {
    }

    public function __invoke(): Response
    {
        try {
            $order = $this->cartContext->getCart();
        } catch (CartNotFoundException) {
            return new JsonResponse([]);
        }

        if (!$order instanceof OrderInterface) {
            return new JsonResponse([]);
        }

        $address = Address::fromOrder($order);

        $result = [];

        foreach ($this->shippingMethodRepository->findBy(['enabled' => true]) as $method) {
            if (!$method instanceof ShippingMethodInterface || !$method->hasPickupPointProvider()) {
                continue;
            }

            $providerCode = $method->getPickupPointProvider();
            if (null === $providerCode || !$this->providerRegistry->has($providerCode)) {
                continue;
            }

            $result[(string) $method->getCode()] = $this->pickupPoints($providerCode, $address);
        }

        return new JsonResponse($result);
    }

    /**
     * @return list<array<string, string|null>>
     */
    private function pickupPoints(string $providerCode, Address $address): array
    {
        try {
            $pickupPoints = $this->providerRegistry->get($providerCode)->findPickupPoints($address);
        } catch (\Throwable) {
            // A slow/unavailable provider must not break the others; its entry is simply empty.
            return [];
        }

        $result = [];
        foreach ($pickupPoints as $pickupPoint) {
            $result[] = [
                'value' => $this->encoder->encode($pickupPoint),
                'name' => $pickupPoint->name,
                'address' => $pickupPoint->address,
                'zipCode' => $pickupPoint->zipCode,
                'city' => $pickupPoint->city,
                'latitude' => $pickupPoint->latitude,
                'longitude' => $pickupPoint->longitude,
            ];
        }

        return $result;
    }
}
