<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use function preg_replace;
use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Sylius\Component\Core\Model\OrderInterface;

#[AsProvider(code: 'gls', name: 'GLS')]
final class GlsProvider extends Provider
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function findPickupPoints(OrderInterface $order): array
    {
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            return [];
        }

        $street = $shippingAddress->getStreet();
        $postCode = $shippingAddress->getPostcode();
        $countryCode = $shippingAddress->getCountryCode();
        if (null === $street || null === $postCode || null === $countryCode) {
            return [];
        }

        $parcelShops = $this->client->searchNearestParcelShops(
            $street,
            preg_replace('/\s+/', '', $postCode),
            $countryCode,
            10,
        );

        $pickupPoints = [];
        foreach ($parcelShops as $item) {
            $pickupPoints[] = $this->transform($item);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(string $id, string $country): ?PickupPoint
    {
        try {
            $parcelShop = $this->client->getOneParcelShop($id);
        } catch (ParcelShopNotFoundException) {
            return null;
        }

        return $this->transform($parcelShop);
    }

    private function transform(ParcelShop $parcelShop): PickupPoint
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = $this->getCode();
        $pickupPoint->id = (string) $parcelShop->getNumber();
        $pickupPoint->name = $parcelShop->getCompanyName();
        $pickupPoint->address = $parcelShop->getStreetName();
        $pickupPoint->zipCode = $parcelShop->getZipCode();
        $pickupPoint->city = $parcelShop->getCity();
        $pickupPoint->country = $parcelShop->getCountryCode();
        $pickupPoint->latitude = (string) $parcelShop->getLatitude();
        $pickupPoint->longitude = (string) $parcelShop->getLongitude();

        return $pickupPoint;
    }
}
