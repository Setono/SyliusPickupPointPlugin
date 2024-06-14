<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\CoolRunner\Client\ClientInterface;
use Setono\CoolRunner\DTO\Servicepoint;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Throwable;
use Webmozart\Assert\Assert;

final class CoolRunnerProvider extends Provider
{
    private ClientInterface $client;

    private FactoryInterface $pickupPointFactory;

    private string $carrier;

    public function __construct(ClientInterface $client, FactoryInterface $pickupPointFactory, string $carrier)
    {
        $this->client = $client;
        $this->pickupPointFactory = $pickupPointFactory;
        $this->carrier = $carrier;
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
            $servicepoints = $this->client->servicepoints()->find(
                $this->carrier,
                $countryCode,
                $street,
                $postCode,
                $city,
            );
        } catch (\Throwable $e) {
            throw new TimeoutException($e);
        }

        $pickupPoints = [];
        foreach ($servicepoints as $item) {
            $pickupPoints[] = $this->transform($item);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        try {
            $servicepoint = $this->client->servicepoints()->findById($this->carrier, $code->getIdPart());
            if (null === $servicepoint) {
                return null;
            }

            return $this->transform($servicepoint);
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
        return sprintf('coolrunner_%s', $this->carrier);
    }

    public function getName(): string
    {
        return sprintf('CoolRunner %s', ucfirst($this->carrier));
    }

    private function transform(Servicepoint $servicepoint): PickupPointInterface
    {
        /** @var PickupPointInterface|object $pickupPoint */
        $pickupPoint = $this->pickupPointFactory->createNew();

        Assert::isInstanceOf($pickupPoint, PickupPointInterface::class);

        $pickupPoint->setCode(new PickupPointCode(
            $servicepoint->id,
            $this->getCode(),
            $servicepoint->address->countryCode,
        ));
        $pickupPoint->setName($servicepoint->name);
        $pickupPoint->setAddress($servicepoint->address->street);
        $pickupPoint->setZipCode($servicepoint->address->zipCode);
        $pickupPoint->setCity($servicepoint->address->city);
        $pickupPoint->setCountry($servicepoint->address->countryCode);
        $pickupPoint->setLatitude($servicepoint->coordinates->latitude);
        $pickupPoint->setLongitude($servicepoint->coordinates->longitude);

        return $pickupPoint;
    }
}
