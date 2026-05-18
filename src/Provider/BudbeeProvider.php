<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\Budbee\Client\ClientInterface;
use Setono\Budbee\DTO\Box;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class BudbeeProvider extends Provider
{
    public function __construct(private readonly ClientInterface $client)
    {
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

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        $box = $this->client->boxes()->getLockerByIdentifier($code->getIdPart());
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

    private function transform(Box $box): PickupPointInterface
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->setCode(new PickupPointCode(
            $box->id,
            $this->getCode(),
            $box->address->country,
        ));
        $pickupPoint->setName($box->name);
        $pickupPoint->setAddress($box->address->street);
        $pickupPoint->setZipCode($box->address->postalCode);
        $pickupPoint->setCity($box->address->city);
        $pickupPoint->setCountry($box->address->country);
        $pickupPoint->setLatitude($box->address->coordinate->latitude);
        $pickupPoint->setLongitude($box->address->coordinate->longitude);

        return $pickupPoint;
    }
}
