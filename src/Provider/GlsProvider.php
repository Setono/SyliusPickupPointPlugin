<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use function preg_replace;
use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ConnectionException;
use Setono\GLS\Webservice\Exception\NoResultException;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class GlsProvider extends Provider
{
    private readonly ClientInterface $client;

    public function __construct(
        ClientInterface $client,
        private readonly FactoryInterface $pickupPointFactory,
        private readonly array $countryCodes = ['DK', 'SE'],
    ) {
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
        $countryCode = $shippingAddress->getCountryCode();
        if (null === $street || null === $postCode || null === $countryCode) {
            return [];
        }

        try {
            $parcelShops = $this->client->searchNearestParcelShops(
                $street,
                preg_replace('/\s+/', '', $postCode),
                $countryCode,
                10,
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
        } catch (ParcelShopNotFoundException) {
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
        } catch (NoResultException) {
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

    private function transform(ParcelShop $parcelShop): PickupPointInterface
    {
        /** @var PickupPointInterface|object $pickupPoint */
        $pickupPoint = $this->pickupPointFactory->createNew();

        Assert::isInstanceOf($pickupPoint, PickupPointInterface::class);

        $pickupPoint->setCode(new PickupPointCode($parcelShop->getNumber(), $this->getCode(), $parcelShop->getCountryCode()));
        $pickupPoint->setName($parcelShop->getCompanyName());
        $pickupPoint->setAddress($parcelShop->getStreetName());
        $pickupPoint->setZipCode($parcelShop->getZipCode());
        $pickupPoint->setCity($parcelShop->getCity());
        $pickupPoint->setCountry($parcelShop->getCountryCode());
        $pickupPoint->setLatitude((float) $parcelShop->getLatitude());
        $pickupPoint->setLongitude((float) $parcelShop->getLongitude());

        return $pickupPoint;
    }
}
