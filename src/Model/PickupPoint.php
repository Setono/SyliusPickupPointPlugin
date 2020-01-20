<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

class PickupPoint implements PickupPointInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $providerId;

    /** @var string */
    protected $provider;

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

    /** @var string|null */
    protected $latitude;

    /** @var string|null */
    protected $longitude;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProviderId(): ?string
    {
        return $this->providerId;
    }

    public function setProviderId(string $providerId): void
    {
        $this->providerId = $providerId;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
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

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): void
    {
        $this->longitude = $longitude;
    }
}
