<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\PostNord\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
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

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    private function populatePickupPoint(string $countryCode, array $servicePoint): PickupPoint
    {
        return new PickupPoint(
            $this->transformId($countryCode, $servicePoint['servicePointId']),
            $servicePoint['name'],
            $servicePoint['deliveryAddress']['streetName'] . ' ' . $servicePoint['deliveryAddress']['streetNumber'],
            $servicePoint['deliveryAddress']['postalCode'],
            $servicePoint['deliveryAddress']['city'],
            $servicePoint['deliveryAddress']['countryCode'],
            $servicePoint['coordinate']['northing'],
            $servicePoint['coordinate']['easting']
        );
    }

    private function transformId(string $countryCode, string $servicePointId): string
    {
        return $countryCode . '|' . $servicePointId;
    }

    private function reverseTransformId(string $id): array
    {
        return explode('|', $id);
    }
}
