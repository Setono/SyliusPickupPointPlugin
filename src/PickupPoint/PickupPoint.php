<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\PickupPoint;

use Safe\Exceptions\StringsException;
use function Safe\sprintf;

final class PickupPoint
{
    /** @var PickupPointId */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $address;

    /** @var string */
    private $zipCode;

    /** @var string */
    private $city;

    /** @var string */
    private $country;

    /** @var string|null */
    private $latitude;

    /** @var string|null */
    private $longitude;

    public function __construct(PickupPointId $id, string $name, string $address, string $zipCode, string $city, string $country, string $latitude = null, string $longitude = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->country = $country;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getId(): PickupPointId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @throws StringsException
     */
    public function getLocation(): string
    {
        return sprintf(
            '%s, %s, %s %s',
            $this->getName(),
            $this->getAddress(),
            $this->getZipCode(),
            $this->getCity()
        );
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }
}
