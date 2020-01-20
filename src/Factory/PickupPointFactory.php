<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Factory;

use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Webmozart\Assert\Assert;

final class PickupPointFactory implements PickupPointFactoryInterface
{
    /** @var string */
    private $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function createNew(
        PickupPointCode $code = null,
        string $name = null,
        string $address = null,
        string $zipCode = null,
        string $city = null,
        string $country = null,
        string $latitude = null,
        string $longitude = null
    ): PickupPointInterface {
        Assert::allNotNull([$code, $name, $address, $zipCode, $city, $country]);

        return new $this->className($code, $name, $address, $zipCode, $city, $country, $latitude, $longitude);
    }
}
