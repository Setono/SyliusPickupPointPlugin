<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\PostNord\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPoint;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPointId;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

/**
 * @see https://developer.postnord.com/api/docs/location
 */
final class PostNordProvider implements ProviderInterface
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return PickupPoint[]
     */
    public function findPickupPoints(OrderInterface $order): iterable
    {
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            return [];
        }

        $result = $this->client->get('/rest/businesslocation/v1/servicepoint/findNearestByAddress.json', [
            'countryCode' => $shippingAddress->getCountryCode(),
            'postalCode' => $shippingAddress->getPostcode(),
            'streetName' => $shippingAddress->getStreet(),
            'numberOfServicePoints' => 10,
        ]);

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

    public function findPickupPoint(PickupPointId $id): ?PickupPoint
    {
        Assert::notNull($id->getCountryPart());

        $result = $this->client->get('/rest/businesslocation/v1/servicepoint/findByServicePointId.json', [
            'countryCode' => $id->getCountryPart(),
            'servicePointId' => $id->getIdPart(),
        ]);

        $servicePoints = $result['servicePointInformationResponse']['servicePoints'] ?? null;
        if (!is_array($servicePoints) || count($servicePoints) < 1) {
            return null;
        }

        return $this->transform($servicePoints[0]);
    }

    public function findAllPickupPoints(): iterable
    {
        $result = $this->client->get('/rest/businesslocation/v1/servicepoint/getServicePointInformation.json');

        $servicePoints = $result['servicePointInformationResponse']['servicePoints'] ?? [];
        if (!is_array($servicePoints)) {
            return [];
        }

        foreach ($servicePoints as $servicePoint) {
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

    private function transform(array $servicePoint): PickupPoint
    {
        $id = new PickupPointId(
            $servicePoint['servicePointId'],
            $this->getCode(),
            $servicePoint['visitingAddress']['countryCode']
        );

        return new PickupPoint(
            $id,
            $servicePoint['name'],
            $servicePoint['visitingAddress']['streetName'] . ' ' . $servicePoint['visitingAddress']['streetNumber'],
            (string) $servicePoint['visitingAddress']['postalCode'],
            $servicePoint['visitingAddress']['city'],
            (string) $servicePoint['visitingAddress']['countryCode'],
            $servicePoint['coordinate']['northing'] ?? null,
            $servicePoint['coordinate']['easting'] ?? null
        );
    }
}
