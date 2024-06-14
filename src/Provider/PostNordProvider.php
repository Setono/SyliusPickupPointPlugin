<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use function preg_replace;
use Psr\Http\Client\NetworkExceptionInterface;
use Setono\PostNord\Client\ClientInterface;
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
    private ClientInterface $client;

    private FactoryInterface $pickupPointFactory;

    public function __construct(ClientInterface $client, FactoryInterface $pickupPointFactory)
    {
        $this->client = $client;
        $this->pickupPointFactory = $pickupPointFactory;
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

        try {
            $result = $this->client->get('/rest/businesslocation/v1/servicepoint/findNearestByAddress.json', [
                'countryCode' => $countryCode,
                'postalCode' => preg_replace('/\s+/', '', $postCode),
                'streetName' => $street,
                'numberOfServicePoints' => 10,
            ]);
        } catch (NetworkExceptionInterface $e) {
            throw new TimeoutException($e);
        }

        $servicePoints = $result['servicePointInformationResponse']['servicePoints'] ?? [];
        if (!is_array($servicePoints)) {
            return [];
        }

        $pickupPoints = [];
        foreach ($servicePoints as $servicePoint) {
            $pickupPoints[] = $this->transform($servicePoint);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        try {
            $result = $this->client->get('/rest/businesslocation/v1/servicepoint/findByServicePointId.json', [
                'countryCode' => $code->getCountryPart(),
                'servicePointId' => $code->getIdPart(),
            ]);
        } catch (NetworkExceptionInterface $e) {
            throw new TimeoutException($e);
        }

        $servicePoints = $result['servicePointInformationResponse']['servicePoints'] ?? null;
        if (!is_array($servicePoints) || count($servicePoints) < 1) {
            return null;
        }

        return $this->transform($servicePoints[0]);
    }

    public function findAllPickupPoints(): iterable
    {
        try {
            $result = $this->client->get('/rest/businesslocation/v1/servicepoint/getServicePointInformation.json');
        } catch (NetworkExceptionInterface $e) {
            throw new TimeoutException($e);
        }

        $servicePoints = $result['servicePointInformationResponse']['servicePoints'] ?? [];
        if (!is_array($servicePoints)) {
            return [];
        }

        foreach ($servicePoints as $servicePoint) {
            if (!self::isValidServicePoint($servicePoint)) {
                continue;
            }

            yield $this->transform($servicePoint);
        }
    }

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    private function transform(array $servicePoint): PickupPointInterface
    {
        $id = new PickupPointCode(
            $servicePoint['servicePointId'],
            $this->getCode(),
            $servicePoint['visitingAddress']['countryCode'],
        );

        $address = '';

        if (isset($servicePoint['visitingAddress']['streetName'])) {
            $address .= $servicePoint['visitingAddress']['streetName'];
        }

        if (isset($servicePoint['visitingAddress']['streetNumber'])) {
            $address .= ('' !== $address ? ' ' : '') . $servicePoint['visitingAddress']['streetNumber'];
        }

        $latitude = $longitude = null;
        if (isset($servicePoint['coordinates'][0])) {
            $latitude = (float) $servicePoint['coordinates'][0]['northing'];
            $longitude = (float) $servicePoint['coordinates'][0]['easting'];
        }

        /** @var PickupPointInterface|object $pickupPoint */
        $pickupPoint = $this->pickupPointFactory->createNew();

        Assert::isInstanceOf($pickupPoint, PickupPointInterface::class);

        $pickupPoint->setCode($id);
        $pickupPoint->setName($servicePoint['name']);
        $pickupPoint->setAddress($address);
        $pickupPoint->setZipCode((string) $servicePoint['visitingAddress']['postalCode']);
        $pickupPoint->setCity($servicePoint['visitingAddress']['city']);
        $pickupPoint->setCountry((string) $servicePoint['visitingAddress']['countryCode']);
        $pickupPoint->setLatitude($latitude);
        $pickupPoint->setLongitude($longitude);

        return $pickupPoint;
    }

    private static function isValidServicePoint(array $servicePoint): bool
    {
        // some service points will not have a city because they are special internal service points
        // we exclude these service points since they doesn't make any sense for the end user
        if (!isset($servicePoint['visitingAddress']['city'])) {
            return false;
        }

        return true;
    }
}
