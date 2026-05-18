<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\PostNord\Client\ClientInterface;
use Setono\PostNord\Request\Query\ServicePoints\ByIdsQuery;
use Setono\PostNord\Request\Query\ServicePoints\NearestByAddressQuery;
use Setono\PostNord\Response\ServicePoints\ServicePoint;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Sylius\Component\Core\Model\OrderInterface;

/**
 * @see https://developer.postnord.com/api/docs/location
 */
final class PostNordProvider extends Provider
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
        if (null === $street) {
            return [];
        }

        $streetParts = explode(' ', $street);
        if (count($streetParts) < 2) {
            return [];
        }

        $streetNumber = array_pop($streetParts);
        $street = implode(' ', $streetParts);

        $postCode = $shippingAddress->getPostcode();
        $city = $shippingAddress->getCity();
        $countryCode = $shippingAddress->getCountryCode();
        if (null === $postCode || null === $city || null === $countryCode) {
            return [];
        }

        $result = $this->client->servicePoints()->getNearestByAddress(NearestByAddressQuery::create(
            streetName: $street,
            streetNumber: $streetNumber,
            postalCode: $postCode,
            city: $city,
            countryCode: $countryCode,
        ));

        if ([] === $result->servicePoints) {
            return [];
        }

        $pickupPoints = [];
        foreach ($result->servicePoints as $servicePoint) {
            $pickupPoints[] = $this->transform($servicePoint);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPoint
    {
        $result = $this->client->servicePoints()->getByIds(ByIdsQuery::create(
            ids: [$code->getIdPart()],
            countryCode: $code->getCountryPart(),
        ));

        if ([] === $result->servicePoints) {
            return null;
        }

        return $this->transform($result->servicePoints[0]);
    }

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    private function transform(ServicePoint $servicePoint): PickupPoint
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->code = new PickupPointCode(
            $servicePoint->servicePointId,
            $this->getCode(),
            $servicePoint->visitingAddress->countryCode,
        );
        $pickupPoint->name = $servicePoint->name;
        $pickupPoint->address = $servicePoint->visitingAddress->streetName . ' ' . $servicePoint->visitingAddress->streetNumber;
        $pickupPoint->zipCode = $servicePoint->visitingAddress->postalCode;
        $pickupPoint->city = $servicePoint->visitingAddress->city;
        $pickupPoint->country = $servicePoint->visitingAddress->countryCode;

        return $pickupPoint;
    }
}
