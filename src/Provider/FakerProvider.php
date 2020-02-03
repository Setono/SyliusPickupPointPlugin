<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Faker\Factory;
use Faker\Generator;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class FakerProvider extends Provider
{
    /** @var Generator */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        $address = $order->getShippingAddress();
        Assert::notNull($address);

        $countryCode = $address->getCountryCode();
        Assert::notNull($countryCode);

        $pickupPoints = [];
        for ($i = 0; $i < 10; ++$i) {
            $pickupPoints[] = $this->createFakePickupPoint((string) $i, $countryCode);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        return $this->createFakePickupPoint($code->getIdPart(), $code->getCountryPart());
    }

    public function findAllPickupPoints(): iterable
    {
        for ($i = 0; $i < 10; ++$i) {
            yield $this->createFakePickupPoint((string) $i);
        }
    }

    public function getCode(): string
    {
        return 'faker';
    }

    public function getName(): string
    {
        return 'Faker';
    }

    private function createFakePickupPoint(string $index, ?string $countryCode = null): PickupPoint
    {
        if (null === $countryCode) {
            $countryCode = $this->faker->countryCode;
        }

        return new PickupPoint(
            new PickupPointCode($index, $this->getCode(), $countryCode),
            "Post office #$index",
            $this->faker->streetAddress,
            (string) $this->faker->numberBetween(11111, 99999),
            $this->faker->city,
            $countryCode,
            (string) $this->faker->latitude,
            (string) $this->faker->longitude
        );
    }
}
