<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use function Safe\preg_replace;
use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ConnectionException;
use Setono\GLS\Webservice\Exception\NoResultException;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class GlsProvider extends Provider
{
    /** @var ClientInterface */
    private $client;

    /** @var array */
    private $countryCodes;

    public function __construct(
        ClientInterface $client,
        array $countryCodes = ['DK', 'SE']
    ) {
        $this->client = $client;
        $this->countryCodes = $countryCodes;
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            return [];
        }

        $street = $shippingAddress->getStreet();
        $postCode = $shippingAddress->getPostcode();
        $countryCode = $shippingAddress->getCountryCode();
        if (null === $street || null === $postCode || null === $countryCode) {
            return [];
        }

        try {
            $parcelShops = $this->client->searchNearestParcelShops(
                $street,
                preg_replace('/\s+/', '', $postCode),
                $countryCode,
                10
            );
        } catch (ConnectionException $e) {
            throw new TimeoutException($e);
        }

        $pickupPoints = [];
        foreach ($parcelShops as $item) {
            $pickupPoints[] = $this->transform($item);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        try {
            $parcelShop = $this->client->getOneParcelShop($code->getIdPart());

            return $this->transform($parcelShop);
        } catch (ParcelShopNotFoundException $e) {
            return null;
        } catch (ConnectionException $e) {
            throw new TimeoutException($e);
        }
    }

    public function findAllPickupPoints(): iterable
    {
        try {
            foreach ($this->countryCodes as $countryCode) {
                $parcelShops = $this->client->getAllParcelShops($countryCode);

                foreach ($parcelShops as $item) {
                    yield $this->transform($item);
                }
            }
        } catch (ConnectionException $e) {
            throw new TimeoutException($e);
        } catch (NoResultException $e) {
            return [];
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
            new PickupPointCode($parcelShop->getNumber(), $this->getCode(), $parcelShop->getCountryCode()),
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
