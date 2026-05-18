<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use function preg_replace;
use Psr\Http\Client\NetworkExceptionInterface;
use Setono\DAO\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class DAOProvider extends Provider
{
    private readonly ClientInterface $client;

    public function __construct(ClientInterface $client, private readonly FactoryInterface $pickupPointFactory)
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
     * @return iterable<PickupPointInterface>
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

    private function populatePickupPoint(array $servicePoint): PickupPointInterface
    {
        $countryCode = 'DK'; // DAO only operates in Denmark

        /** @var PickupPointInterface|object $pickupPoint */
        $pickupPoint = $this->pickupPointFactory->createNew();

        Assert::isInstanceOf($pickupPoint, PickupPointInterface::class);

        $pickupPoint->setCode(new PickupPointCode($servicePoint['shopId'], $this->getCode(), $countryCode));
        $pickupPoint->setName($servicePoint['navn']);
        $pickupPoint->setAddress($servicePoint['adresse']);
        $pickupPoint->setZipCode($servicePoint['postnr']);
        $pickupPoint->setCity($servicePoint['bynavn']);
        $pickupPoint->setCountry($countryCode);

        $pickupPoint->setLatitude((float) $servicePoint['latitude']);
        $pickupPoint->setLongitude((float) $servicePoint['longitude']);

        return $pickupPoint;
    }
}
