<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Faker\Factory;
use Faker\Generator;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

#[AsProvider(code: 'faker', name: 'Faker')]
final class FakerProvider extends Provider
{
    private readonly Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function findPickupPoints(OrderInterface $order): array
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

    public function findPickupPoint(string $id, string $country): PickupPoint
    {
        return $this->createFakePickupPoint($id, $country);
    }

    private function createFakePickupPoint(string $index, ?string $countryCode = null): PickupPoint
    {
        if (null === $countryCode) {
            $countryCode = $this->faker->countryCode;
        }

        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = $this->getCode();
        $pickupPoint->id = $index;
        $pickupPoint->name = "Post office #$index";
        $pickupPoint->address = $this->faker->streetAddress;
        $pickupPoint->zipCode = (string) $this->faker->numberBetween(11111, 99999);
        $pickupPoint->city = $this->faker->city;
        $pickupPoint->country = $countryCode;
        $pickupPoint->latitude = (string) $this->faker->latitude;
        $pickupPoint->longitude = (string) $this->faker->longitude;

        return $pickupPoint;
    }
}
