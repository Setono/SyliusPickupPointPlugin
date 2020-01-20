<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\DAO\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPoint;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPointId;
use Sylius\Component\Core\Model\OrderInterface;

final class DAOProvider extends Provider
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            return [];
        }

        yield from $this->_findPickupPoints([
            'postnr' => $shippingAddress->getPostcode(),
            'adresse' => $shippingAddress->getStreet(),
            'antal' => 10,
        ]);
    }

    public function findPickupPoint(PickupPointId $id): ?PickupPoint
    {
        foreach ($this->_findPickupPoints([
            'shopid' => $id->getIdPart(),
        ]) as $pickupPoint) {
            return $pickupPoint;
        }

        return null;
    }

    public function findAllPickupPoints(): iterable
    {
        yield from $this->_findPickupPoints([
            'postnr' => '9999', // Notice that this is a hack to get all pickup points
            'antal' => 5000,
        ]);
    }

    /**
     * @return PickupPoint[]
     */
    private function _findPickupPoints(array $params): iterable
    {
        $result = $this->client->get('/DAOPakkeshop/FindPakkeshop.php', $params);

        $pickupPoints = $result['resultat']['pakkeshops'] ?? [];

        if (!is_array($pickupPoints)) {
            return [];
        }

        foreach ($pickupPoints as $pickupPoint) {
            yield $this->populatePickupPoint($pickupPoint);
        }
    }

    public function getCode(): string
    {
        return 'dao';
    }

    public function getName(): string
    {
        return 'DAO';
    }

    private function populatePickupPoint(array $servicePoint): PickupPoint
    {
        return new PickupPoint(
            new PickupPointId($servicePoint['shopId'], $this->getCode()),
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
