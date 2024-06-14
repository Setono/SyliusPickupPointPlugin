<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Psr\Http\Client\NetworkExceptionInterface;
use Setono\PostNord\Client\ClientInterface;
use Setono\PostNord\Request\Query\ServicePoints\ByIdsQuery;
use Setono\PostNord\Request\Query\ServicePoints\NearestByAddressQuery;
use Setono\PostNord\Response\ServicePoints\ServicePoint;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

/**
 * @see https://developer.postnord.com/api/docs/location
 */
final class PostNordProvider extends Provider
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly FactoryInterface $pickupPointFactory,
    ) {
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

        try {
            $result = $this->client->servicePoints()->getNearestByAddress(NearestByAddressQuery::create(
                streetName: $street,
                streetNumber: $streetNumber,
                postalCode: $postCode,
                city: $city,
                countryCode: $countryCode,
            ));
        } catch (NetworkExceptionInterface $e) {
            throw new TimeoutException($e);
        }

        if ([] === $result->servicePoints) {
            return [];
        }

        $pickupPoints = [];
        foreach ($result->servicePoints as $servicePoint) {
            $pickupPoints[] = $this->transform($servicePoint);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        try {
            $result = $this->client->servicePoints()->getByIds(ByIdsQuery::create(
                ids: [$code->getIdPart()],
                countryCode: $code->getCountryPart(),
            ));
        } catch (NetworkExceptionInterface $e) {
            throw new TimeoutException($e);
        }

        if ([] === $result->servicePoints) {
            return null;
        }

        return $this->transform($result->servicePoints[0]);
    }

    public function findAllPickupPoints(): iterable
    {
        // todo implement this
        return [];
    }

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    private function transform(ServicePoint $servicePoint): PickupPointInterface
    {
        $id = new PickupPointCode(
            $servicePoint->servicePointId,
            $this->getCode(),
            $servicePoint->visitingAddress->countryCode,
        );

        /** @var PickupPointInterface|object $pickupPoint */
        $pickupPoint = $this->pickupPointFactory->createNew();

        Assert::isInstanceOf($pickupPoint, PickupPointInterface::class);

        $pickupPoint->setCode($id);
        $pickupPoint->setName($servicePoint->name);
        $pickupPoint->setAddress($servicePoint->visitingAddress->streetName . ' ' . $servicePoint->visitingAddress->streetNumber);
        $pickupPoint->setZipCode($servicePoint->visitingAddress->postalCode);
        $pickupPoint->setCity($servicePoint->visitingAddress->city);
        $pickupPoint->setCountry($servicePoint->visitingAddress->countryCode);

        return $pickupPoint;
    }

    private static function isValidServicePoint(array $servicePoint): bool
    {
        // some service points will not have a city because they are special internal service points
        // we exclude these service points since they don't make any sense for the end user
        if (!isset($servicePoint['visitingAddress']['city'])) {
            return false;
        }

        return true;
    }
}
