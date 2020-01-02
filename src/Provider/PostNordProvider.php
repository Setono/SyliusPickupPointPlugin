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
 *
 * @todo Pass locale parameter so addresses will be localized?
 */
final class PostNordProvider implements ProviderInterface
{
    public const DELIMITER = '_';

    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return PickupPoint[]
     */
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
        if (!is_array($servicePoints) || count($servicePoints) < 1) {
            return [];
        }

        $pickupPoints = [];
        foreach ($servicePoints as $servicePoint) {
            $pickupPoints[] = $this->transform($servicePoint, $shippingAddress->getCountryCode());
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

        if (!isset($result['servicePointInformationResponse']['servicePoints'])) {
            return null;
        }

        $servicePoints = $result['servicePointInformationResponse']['servicePoints'];
        if (!is_array($servicePoints) || count($servicePoints) < 1) {
            return null;
        }

        return $this->transform($servicePoints[0], $id->getCountryPart());
    }

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    private function transform(array $servicePoint, string $countryCode): PickupPoint
    {
        $id = new PickupPointId($servicePoint['servicePointId'], $this->getCode(), $countryCode);

        return new PickupPoint(
            $id,
            $servicePoint['name'],
            $servicePoint['deliveryAddress']['streetName'] . ' ' . $servicePoint['deliveryAddress']['streetNumber'],
            (string) $servicePoint['deliveryAddress']['postalCode'],
            $servicePoint['deliveryAddress']['city'],
            (string) $servicePoint['deliveryAddress']['countryCode'],
            $servicePoint['coordinate']['northing'] ?? null,
            $servicePoint['coordinate']['easting'] ?? null
        );
    }
}
