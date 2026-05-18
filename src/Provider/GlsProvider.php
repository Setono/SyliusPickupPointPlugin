<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use function preg_replace;
use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Sylius\Component\Core\Model\OrderInterface;

final class GlsProvider extends Provider
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function findPickupPoints(OrderInterface $order): iterable
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

    public function getCode(): string
    {
        return 'gls';
    }

    public function getName(): string
    {
        return 'GLS';
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
        $pickupPoint->latitude = (float) $parcelShop->getLatitude();
        $pickupPoint->longitude = (float) $parcelShop->getLongitude();

        return $pickupPoint;
    }
}
