<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

interface PickupPointInterface
{
    public function getCode(): ?PickupPointCode;

    public function setCode(PickupPointCode $code): void;

    public function getName(): ?string;

    public function setName(string $name): void;

    public function getAddress(): ?string;

    public function setAddress(string $address): void;

    public function getZipCode(): ?string;

    public function setZipCode(string $zipCode): void;

    public function getCity(): ?string;

    public function setCity(string $city): void;

    /**
     * This is the alpha 2 country code
     */
    public function getCountry(): ?string;

    public function setCountry(string $country): void;

    public function getLatitude(): ?float;

    /**
     * @throws \InvalidArgumentException if the $latitude is invalid
     */
    public function setLatitude(?float $latitude): void;

    public function getLongitude(): ?float;

    /**
     * @throws \InvalidArgumentException if the $longitude is invalid
     */
    public function setLongitude(?float $longitude): void;

    public function getFullAddress(): string;
}
