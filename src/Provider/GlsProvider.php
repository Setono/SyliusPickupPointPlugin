<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use function preg_replace;
use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
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

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        try {
            $parcelShop = $this->client->getOneParcelShop($code->getIdPart());
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

    private function transform(ParcelShop $parcelShop): PickupPointInterface
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->setCode(new PickupPointCode($parcelShop->getNumber(), $this->getCode(), $parcelShop->getCountryCode()));
        $pickupPoint->setName($parcelShop->getCompanyName());
        $pickupPoint->setAddress($parcelShop->getStreetName());
        $pickupPoint->setZipCode($parcelShop->getZipCode());
        $pickupPoint->setCity($parcelShop->getCity());
        $pickupPoint->setCountry($parcelShop->getCountryCode());
        $pickupPoint->setLatitude((float) $parcelShop->getLatitude());
        $pickupPoint->setLongitude((float) $parcelShop->getLongitude());

        return $pickupPoint;
    }
}
