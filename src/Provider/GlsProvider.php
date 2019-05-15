<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use SoapClient;
use Sylius\Component\Core\Model\OrderInterface;

final class GlsProvider implements ProviderInterface
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
        if (null === $order->getShippingAddress()) {
            return [];
        }

        $parcelShops = $this->client->searchNearestParcelShops(
            $order->getShippingAddress()->getStreet(),
            $order->getShippingAddress()->getPostcode(),
            $order->getShippingAddress()->getCountryCode(),
            10
        );

        $pickupPoints = [];
        foreach ($parcelShops as $item) {
            $pickupPoints[] = $this->transform($item);
        }

        return $pickupPoints;
    }

    public function getPickupPointById(string $id): ?PickupPointInterface
    {
        try {
            $parcelShop = $this->client->getOneParcelShop($id);
        } catch (ParcelShopNotFoundException $e) {
            return null;
        }

        return $this->transform($parcelShop);
    }

    // todo remove this
    public function getClient(): SoapClient
    {
        return new SoapClient('http://www.gls.dk/webservices_v4/wsShopFinder.asmx?WSDL');
    }

    public function getCode(): string
    {
        return 'gls';
    }

    public function getName(): string
    {
        return 'GLS';
    }

    public function isEnabled(): bool
    {
        return true;
    }

    private function transform(ParcelShop $parcelShop): PickupPoint
    {
        return new PickupPoint(
            $parcelShop->getNumber(),
            $parcelShop->getCompanyName(),
            $parcelShop->getStreetName(),
            $parcelShop->getZipCode(),
            $parcelShop->getCity(),
            $parcelShop->getCountryCode(),
            $parcelShop->getLatitude(),
            $parcelShop->getLongitude()
        );
    }
}
