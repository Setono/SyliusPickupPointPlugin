<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use function preg_replace;
use Setono\DAO\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Sylius\Component\Core\Model\OrderInterface;

#[AsProvider(code: 'dao', name: 'DAO')]
final class DAOProvider extends Provider
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    public function findPickupPoints(OrderInterface $order): array
    {
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            return [];
        }

        $street = $shippingAddress->getStreet();
        $postCode = $shippingAddress->getPostcode();
        if (null === $street || null === $postCode) {
            return [];
        }

        return $this->_findPickupPoints([
            'postnr' => preg_replace('/\s+/', '', $postCode),
            'adresse' => $street,
            'antal' => 10,
        ]);
    }

    public function findPickupPoint(string $id, string $country): ?PickupPoint
    {
        return $this->_findPickupPoints([
            'shopid' => $id,
        ])[0] ?? null;
    }

    /**
     * @return list<PickupPoint>
     */
    private function _findPickupPoints(array $params): array
    {
        $result = $this->client->get('/DAOPakkeshop/FindPakkeshop.php', $params);

        $pickupPoints = $result['resultat']['pakkeshops'] ?? [];

        if (!is_array($pickupPoints)) {
            return [];
        }

        $list = [];
        foreach ($pickupPoints as $pickupPoint) {
            $list[] = $this->populatePickupPoint($pickupPoint);
        }

        return $list;
    }

    private function populatePickupPoint(array $servicePoint): PickupPoint
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = $this->getCode();
        $pickupPoint->id = (string) $servicePoint['shopId'];
        $pickupPoint->name = $servicePoint['navn'];
        $pickupPoint->address = $servicePoint['adresse'];
        $pickupPoint->zipCode = $servicePoint['postnr'];
        $pickupPoint->city = $servicePoint['bynavn'];
        $pickupPoint->country = 'DK'; // DAO only operates in Denmark
        $pickupPoint->latitude = (string) $servicePoint['latitude'];
        $pickupPoint->longitude = (string) $servicePoint['longitude'];

        return $pickupPoint;
    }
}
