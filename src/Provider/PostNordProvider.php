<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\PostNord\Client\ClientInterface;
use Setono\PostNord\Request\Query\ServicePoints\ByIdsQuery;
use Setono\PostNord\Request\Query\ServicePoints\NearestByAddressQuery;
use Setono\PostNord\Response\ServicePoints\ServicePoint;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;
use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;

/**
 * @see https://developer.postnord.com/api/docs/location
 */
#[AsProvider(code: 'post_nord', name: 'PostNord')]
final class PostNordProvider extends Provider
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    public function findPickupPoints(Address $address): array
    {
        $street = $address->street;
        if (null === $street) {
            return [];
        }

        $streetParts = explode(' ', $street);
        if (count($streetParts) < 2) {
            return [];
        }

        $streetNumber = array_pop($streetParts);
        $street = implode(' ', $streetParts);

        $postCode = $address->postcode;
        $city = $address->city;
        $countryCode = $address->countryCode;
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

    public function findPickupPoint(string $id, array $metadata = []): ?PickupPoint
    {
        $country = $metadata['country'] ?? null;

        $result = $this->client->servicePoints()->getByIds(ByIdsQuery::create(
            ids: [$id],
            countryCode: is_string($country) ? $country : null,
        ));

        if ([] === $result->servicePoints) {
            return null;
        }

        return $this->transform($result->servicePoints[0]);
    }

    private function transform(ServicePoint $servicePoint): PickupPoint
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = $this->getCode();
        $pickupPoint->id = (string) $servicePoint->servicePointId;
        $pickupPoint->name = $servicePoint->name;
        $pickupPoint->address = $servicePoint->visitingAddress->streetName . ' ' . $servicePoint->visitingAddress->streetNumber;
        $pickupPoint->zipCode = $servicePoint->visitingAddress->postalCode;
        $pickupPoint->city = $servicePoint->visitingAddress->city;
        $pickupPoint->country = $servicePoint->visitingAddress->countryCode;

        return $pickupPoint;
    }
}
