<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\DAO\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class DAOProvider implements ProviderInterface
{
    /** @var ClientInterface */
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

        return $this->_findPickupPoints([
            'postnr' => $shippingAddress->getPostcode(),
            'adresse' => $shippingAddress->getStreet(),
            'antal' => 10,
        ]);
    }

    public function findPickupPoint(string $id): ?PickupPointInterface
    {
        $pickupPoints = $this->_findPickupPoints([
            'shopid' => $id,
        ]);

        if (count($pickupPoints) < 1) {
            return null;
        }

        return $pickupPoints[0];
    }

    /**
     * @return PickupPointInterface[]
     */
    private function _findPickupPoints(array $params): array
    {
        $result = $this->client->get('/DAOPakkeshop/FindPakkeshop.php', $params);

        $pickupPoints = $result['resultat']['pakkeshops'] ?? [];

        if (!is_array($pickupPoints) || count($pickupPoints) <= 0) {
            return [];
        }

        $ret = [];
        foreach ($pickupPoints as $pickupPoint) {
            $ret[] = $this->populatePickupPoint($pickupPoint);
        }

        return $ret;
    }

    public function getCode(): string
    {
        return 'dao';
    }

    public function getName(): string
    {
        return 'DAO';
    }

    private function populatePickupPoint(array $servicePoint): PickupPointInterface
    {
        return new PickupPoint(
            $this->getCode(),
            $servicePoint['shopId'],
            $servicePoint['navn'],
            $servicePoint['adresse'],
            $servicePoint['postnr'],
            $servicePoint['bynavn'],
            'DK', // DAO only operates in Denmark
            $servicePoint['latitude'],
            $servicePoint['longitude']
        );
    }
}
