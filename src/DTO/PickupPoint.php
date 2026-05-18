<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DTO;

final class PickupPoint
{
    public ?string $provider = null;

    public ?string $id = null;

    public ?string $name = null;

    public ?string $address = null;

    public ?string $zipCode = null;

    public ?string $city = null;

    public ?string $country = null;

    public ?float $latitude = null;

    public ?float $longitude = null;
}
