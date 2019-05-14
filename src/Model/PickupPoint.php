<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

class PickupPoint implements PickupPointInterface
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $address;

    /** @var string */
    protected $zipCode;

    /** @var string */
    protected $city;

    /** @var string */
    protected $country;

    /** @var string */
    protected $latitude;

    /** @var string */
    protected $longitude;

    public function __construct(string $id, string $name, string $address, string $zipCode, string $city, string $country, string $latitude, string $longitude)
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }
}
