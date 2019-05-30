<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class FakerProvider implements ProviderInterface
{
    /** @var \Faker\Generator */
    private $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    public function findPickupPoints(OrderInterface $order): array
    {
        $pockupPoints = [];
        for ($i=0; $i < 10; $i++) {
            $pockupPoints[] = $this->createFakePickupPoint((string)$i);
        }
        return $pockupPoints;
    }

    public function findOnePickupPointById(string $id): ?PickupPointInterface
    {
        return $this->createFakePickupPoint($id);
    }

    public function getCode(): string
    {
        return 'faker';
    }

    public function getName(): string
    {
        return 'Faker';
    }

    private function createFakePickupPoint(string $index): PickupPointInterface
    {
        return new PickupPoint(
            $this->getCode(),
            $index,
            "Post office #$index",
            $this->faker->streetAddress,
            (string) $this->faker->numberBetween(11111, 99999),
            $this->faker->city,
            $this->faker->countryCode,
            (string) $this->faker->latitude,
            (string) $this->faker->longitude
        );
    }
}
