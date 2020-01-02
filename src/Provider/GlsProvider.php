<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class GlsProvider implements ProviderInterface
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function findPickupPoints(OrderInterface $order): array
    {
        if (null === $order->getShippingAddress()) {
            return [];
        }

        $parcelShops = $this->client->searchNearestParcelShops(
            $order->getShippingAddress()->getStreet(),
            $order->getShippingAddress()->getPostcode(),
            $order->getShippingAddress()->getCountryCode(),
            10
        );

        $pickupPoints = [];
        foreach ($parcelShops as $item) {
            $pickupPoints[] = $this->transform($item);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(string $id): ?PickupPointInterface
    {
        try {
            $parcelShop = $this->client->getOneParcelShop($id);

            return $this->transform($parcelShop);
        } catch (ParcelShopNotFoundException $e) {
            return null;
        }
    }

    public function getCode(): string
    {
        return 'gls';
    }

    public function getName(): string
    {
        return 'GLS';
    }

    private function transform(ParcelShop $parcelShop): PickupPointInterface
    {
        return new PickupPoint(
            $this->getCode(),
            $parcelShop->getNumber(),
            $parcelShop->getCompanyName(),
            $parcelShop->getStreetName(),
            $parcelShop->getZipCode(),
            $parcelShop->getCity(),
            $parcelShop->getCountryCode(),
            $parcelShop->getLatitude(),
            $parcelShop->getLongitude()
        );
    }
}
