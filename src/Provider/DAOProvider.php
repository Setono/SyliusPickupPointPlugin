<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Psr\Http\Client\NetworkExceptionInterface;
use function Safe\preg_replace;
use Setono\DAO\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
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

        $street = $shippingAddress->getStreet();
        $postCode = $shippingAddress->getPostcode();
        if (null === $street || null === $postCode) {
            return [];
        }

        yield from $this->_findPickupPoints([
            'postnr' => preg_replace('/\s+/', '', $postCode),
            'adresse' => $street,
            'antal' => 10,
        ]);
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        foreach ($this->_findPickupPoints([
            'shopid' => $code->getIdPart(),
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
        try {
            $result = $this->client->get('/DAOPakkeshop/FindPakkeshop.php', $params);
        } catch (NetworkExceptionInterface $e) {
            throw new TimeoutException($e);
        }

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
        $countryCode = 'DK';

        return new PickupPoint(
            new PickupPointCode($servicePoint['shopId'], $this->getCode(), $countryCode),
            $servicePoint['navn'],
            $servicePoint['adresse'],
            $servicePoint['postnr'],
            $servicePoint['bynavn'],
            $countryCode, // DAO only operates in Denmark
            $servicePoint['latitude'],
            $servicePoint['longitude']
        );
    }
}
