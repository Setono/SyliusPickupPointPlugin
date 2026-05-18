<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\Budbee\Client\ClientInterface;
use Setono\Budbee\DTO\Box;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Sylius\Component\Core\Model\OrderInterface;

final class BudbeeProvider extends Provider
{
    public function __construct(private readonly ClientInterface $client)
    {
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
        $city = $shippingAddress->getCity();
        if (null === $street || null === $postCode || null === $countryCode || null === $city) {
            return [];
        }

        $boxes = $this->client->boxes()->getAvailableLockers(
            $countryCode,
            $postCode,
        );

        $pickupPoints = [];
        foreach ($boxes as $item) {
            $pickupPoints[] = $this->transform($item);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(string $id, string $country): ?PickupPoint
    {
        $box = $this->client->boxes()->getLockerByIdentifier($id);
        if (null === $box) {
            return null;
        }

        return $this->transform($box);
    }

    public function getCode(): string
    {
        return 'budbee';
    }

    public function getName(): string
    {
        return 'Budbee';
    }

    private function transform(Box $box): PickupPoint
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = $this->getCode();
        $pickupPoint->id = (string) $box->id;
        $pickupPoint->name = $box->name;
        $pickupPoint->address = $box->address->street;
        $pickupPoint->zipCode = $box->address->postalCode;
        $pickupPoint->city = $box->address->city;
        $pickupPoint->country = $box->address->country;
        $pickupPoint->latitude = (string) $box->address->coordinate->latitude;
        $pickupPoint->longitude = (string) $box->address->coordinate->longitude;

        return $pickupPoint;
    }
}
