<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPoint;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPointId;
use Sylius\Component\Core\Model\OrderInterface;

final class GlsProvider implements ProviderInterface
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function findPickupPoints(OrderInterface $order): iterable
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

    public function findPickupPoint(PickupPointId $id): ?PickupPoint
    {
        try {
            $parcelShop = $this->client->getOneParcelShop($id->getIdPart());

            return $this->transform($parcelShop);
        } catch (ParcelShopNotFoundException $e) {
            return null;
        }
    }

    public function findAllPickupPoints(): iterable
    {
        // todo these country codes should come from a config somewhere, probably on the shipping method
        $countryCodes = ['DK', 'SE'];

        foreach ($countryCodes as $countryCode) {
            $parcelShops = $this->client->getAllParcelShops($countryCode);

            foreach ($parcelShops as $item) {
                yield $this->transform($item);
            }
        }
    }

    public function getCode(): string
    {
        return 'gls';
    }

    public function getName(): string
    {
        return 'GLS';
    }

    private function transform(ParcelShop $parcelShop): PickupPoint
    {
        return new PickupPoint(
            new PickupPointId($parcelShop->getNumber(), $this->getCode()),
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
