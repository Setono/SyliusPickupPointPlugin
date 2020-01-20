<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Faker\Factory;
use Faker\Generator;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Sylius\Component\Core\Model\OrderInterface;

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
        $pickupPoints = [];
        for ($i = 0; $i < 10; ++$i) {
            $pickupPoints[] = $this->createFakePickupPoint((string) $i);
        }

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPoint
    {
        return $this->createFakePickupPoint($code->getIdPart());
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

    private function createFakePickupPoint(string $index): PickupPoint
    {
        $countryCode = $this->faker->countryCode;

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
