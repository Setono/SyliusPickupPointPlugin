<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Controller\Action;

use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoderInterface;
use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns, for the current cart, the pickup points of every shipping method that is available for the
 * cart's shipment(s) and has a provider, keyed by shipping method code. It resolves the available methods
 * with the shipping-methods resolver — the same methods Sylius renders as the radios (channel + zone +
 * shipping category), not merely every channel-enabled method — so it only calls the providers behind
 * methods the shopper can actually select.
 *
 * The checkout page calls this asynchronously *after* it has rendered, so a slow or down carrier API never
 * blocks the shipping page — and a single failing provider only empties its own entry (the per-provider
 * try/catch) instead of taking the others down.
 *
 * Each point carries an opaque `value` token ({@see PickupPointEncoderInterface}) that the shop JS uses as
 * the selected radio's value; on submit the hidden field's transformer decodes it back into the point, so
 * nothing is re-fetched server-side.
 */
final readonly class PickupPointsAction
{
    public function __construct(
        private CartContextInterface $cartContext,
        private ProviderRegistryInterface $providerRegistry,
        private PickupPointEncoderInterface $encoder,
        private ShippingMethodsResolverInterface $shippingMethodsResolver,
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

        foreach ($order->getShipments() as $shipment) {
            foreach ($this->shippingMethodsResolver->getSupportedMethods($shipment) as $method) {
                if (!$method instanceof ShippingMethodInterface || !$method->hasPickupPointProvider()) {
                    continue;
                }

                $code = (string) $method->getCode();
                if (isset($result[$code])) {
                    // Already computed for an earlier shipment; the points only depend on the provider.
                    continue;
                }

                $providerCode = $method->getPickupPointProvider();
                if (null === $providerCode || !$this->providerRegistry->has($providerCode)) {
                    continue;
                }

                $result[$code] = $this->pickupPoints($providerCode, $address);
            }
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
            try {
                $value = $this->encoder->encode($pickupPoint);
            } catch (\JsonException) {
                // A point we cannot encode (e.g. a carrier returning a non-UTF-8 string) is skipped
                // rather than 500-ing the whole endpoint; the other points and carriers still load.
                continue;
            }

            $result[] = [
                'value' => $value,
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
