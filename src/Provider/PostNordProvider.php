<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\PostNord\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class PostNordProvider implements ProviderInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function findPickupPoints(OrderInterface $order): array
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

        if (!isset($result['servicePointInformationResponse']['servicePoints'])) {
            return [];
        }

        $servicePoints = $result['servicePointInformationResponse']['servicePoints'];

        if (!is_array($servicePoints) || count($servicePoints) <= 0) {
            return [];
        }

        $pickupPoints = [];
        foreach ($servicePoints as $servicePoint) {
            $pickupPoints[] = $this->populatePickupPoint($shippingAddress->getCountryCode(), $servicePoint);
        }

        return $pickupPoints;
    }

    public function findOnePickupPointById(string $id): ?PickupPointInterface
    {
        // @todo Implementation
    }

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    private function populatePickupPoint(string $countryCode, array $servicePoint): PickupPointInterface
    {
        return new PickupPoint(
            $this->getCode(),
            $this->transformId($countryCode, $servicePoint['servicePointId']),
            $servicePoint['name'],
            $servicePoint['deliveryAddress']['streetName'] . ' ' . $servicePoint['deliveryAddress']['streetNumber'],
            (string) $servicePoint['deliveryAddress']['postalCode'],
            $servicePoint['deliveryAddress']['city'],
            (string) $servicePoint['deliveryAddress']['countryCode'],
            isset($servicePoint['coordinate']) ? (string) $servicePoint['coordinate']['northing'] : '',
            isset($servicePoint['coordinate']) ? (string) $servicePoint['coordinate']['easting'] : ''
        );
    }

    private function transformId(string $countryCode, string $servicePointId): string
    {
        return $countryCode . '|' . $servicePointId;
    }
}
