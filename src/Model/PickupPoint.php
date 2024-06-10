<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use function sprintf;
use Webmozart\Assert\Assert;

class PickupPoint implements PickupPointInterface
{
    protected ?int $id;

    protected ?PickupPointCode $code = null;

    protected ?string $name = null;

    protected ?string $address = null;

    protected ?string $zipCode = null;

    protected ?string $city = null;

    protected ?string $country = null;

    protected ?float $latitude = null;

    protected ?float $longitude = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?PickupPointCode
    {
        return $this->code;
    }

    public function setCode(PickupPointCode $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        Assert::nullOrRange($latitude, -90, 90);

        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        Assert::nullOrRange($longitude, -180, 180);

        $this->longitude = $longitude;
    }

    public function getFullAddress(): string
    {
        return sprintf(
            '%s, %s %s',
            $this->getAddress(),
            $this->getZipCode(),
            $this->getCity(),
        );
    }
}
