<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Safe\Exceptions\StringsException;
use function Safe\sprintf;
use Setono\PostNord\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

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
     * @return PickupPointInterface[]
     *
     * @throws StringsException
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

    /**
     * @throws StringsException
     */
    public function findPickupPoint(string $id): ?PickupPointInterface
    {
        [$countryCode, $id] = $this->reverseTransformId($id);

        $result = $this->client->get('/rest/businesslocation/v1/servicepoint/findByServicePointId.json', [
            'countryCode' => $countryCode,
            'servicePointId' => $id,
        ]);

        if (!isset($result['servicePointInformationResponse']['servicePoints'])) {
            return null;
        }

        $servicePoints = $result['servicePointInformationResponse']['servicePoints'];
        if (!is_array($servicePoints) || count($servicePoints) < 1) {
            return null;
        }

        return $this->transform($servicePoints[0], $countryCode);
    }

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    /**
     * @throws StringsException
     */
    private function transform(array $servicePoint, string $countryCode): PickupPointInterface
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

    /**
     * @throws StringsException
     */
    private function transformId(string $countryCode, string $servicePointId): string
    {
        return sprintf(
            '%s%s%s',
            $countryCode,
            self::DELIMITER,
            $servicePointId
        );
    }

    private function reverseTransformId(string $id): array
    {
        return explode(self::DELIMITER, $id);
    }
}
