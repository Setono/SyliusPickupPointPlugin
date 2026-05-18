<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\Budbee\Client\ClientInterface;
use Setono\Budbee\DTO\Box;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Throwable;
use Webmozart\Assert\Assert;

final class BudbeeProvider extends Provider
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
        $countryCode = $shippingAddress->getCountryCode();
        $city = $shippingAddress->getCity();
        if (null === $street || null === $postCode || null === $countryCode || null === $city) {
            return [];
        }

        try {
            $boxes = $this->client->boxes()->getAvailableLockers(
                $countryCode,
                $postCode,
            );
        } catch (\Throwable $e) {
            throw new TimeoutException($e);
        }

        $pickupPoints = [];
        foreach ($boxes as $item) {
            $pickupPoints[] = $this->transform($item);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        try {
            $box = $this->client->boxes()->getLockerByIdentifier($code->getIdPart());
            if (null === $box) {
                return null;
            }

            return $this->transform($box);
        } catch (Throwable $e) {
            throw new TimeoutException($e);
        }
    }

    public function findAllPickupPoints(): iterable
    {
        return [];
    }

    public function getCode(): string
    {
        return 'budbee';
    }

    public function getName(): string
    {
        return 'Budbee';
    }

    private function transform(Box $box): PickupPointInterface
    {
        /** @var PickupPointInterface|object $pickupPoint */
        $pickupPoint = $this->pickupPointFactory->createNew();

        Assert::isInstanceOf($pickupPoint, PickupPointInterface::class);

        $pickupPoint->setCode(new PickupPointCode(
            $box->id,
            $this->getCode(),
            $box->address->country,
        ));
        $pickupPoint->setName($box->name);
        $pickupPoint->setAddress($box->address->street);
        $pickupPoint->setZipCode($box->address->postalCode);
        $pickupPoint->setCity($box->address->city);
        $pickupPoint->setCountry($box->address->country);
        $pickupPoint->setLatitude($box->address->coordinate->latitude);
        $pickupPoint->setLongitude($box->address->coordinate->longitude);

        return $pickupPoint;
    }
}
