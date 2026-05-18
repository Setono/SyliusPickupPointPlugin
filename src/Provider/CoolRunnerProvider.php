<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\CoolRunner\Client\ClientInterface;
use Setono\CoolRunner\DTO\Servicepoint;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Sylius\Component\Core\Model\OrderInterface;

final class CoolRunnerProvider extends Provider
{
    public function __construct(private readonly ClientInterface $client, private readonly string $carrier)
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

        $servicepoints = $this->client->servicepoints()->find(
            $this->carrier,
            $countryCode,
            $street,
            $postCode,
            $city,
        );

        $pickupPoints = [];
        foreach ($servicepoints as $item) {
            $pickupPoints[] = $this->transform($item);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(string $id, string $country): ?PickupPoint
    {
        $servicepoint = $this->client->servicepoints()->findById($this->carrier, $id);
        if (null === $servicepoint) {
            return null;
        }

        return $this->transform($servicepoint);
    }

    public function getCode(): string
    {
        return sprintf('coolrunner_%s', $this->carrier);
    }

    public function getName(): string
    {
        return sprintf('CoolRunner %s', ucfirst($this->carrier));
    }

    private function transform(Servicepoint $servicepoint): PickupPoint
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = $this->getCode();
        $pickupPoint->id = (string) $servicepoint->id;
        $pickupPoint->name = $servicepoint->name;
        $pickupPoint->address = $servicepoint->address->street;
        $pickupPoint->zipCode = $servicepoint->address->zipCode;
        $pickupPoint->city = $servicepoint->address->city;
        $pickupPoint->country = $servicepoint->address->countryCode;
        $pickupPoint->latitude = $servicepoint->coordinates->latitude;
        $pickupPoint->longitude = $servicepoint->coordinates->longitude;

        return $pickupPoint;
    }
}
