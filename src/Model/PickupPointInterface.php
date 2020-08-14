<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Sylius\Component\Resource\Model\ResourceInterface;

interface PickupPointInterface extends ResourceInterface
{
    public function getCode(): ?PickupPointCode;

    public function setCode(PickupPointCode $code): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function getAddress(): string;

    public function setAddress(string $address): void;

    public function getZipCode(): string;

    public function setZipCode(string $zipCode): void;

    public function getCity(): string;

    public function setCity(string $city): void;

    /**
     * This is the alpha 2 country code
     */
    public function getCountry(): string;

    public function setCountry(string $country): void;

    public function getLatitude(): ?string;

    public function setLatitude(?string $latitude): void;

    public function getLongitude(): ?string;

    public function setLongitude(?string $longitude): void;

    public function getFullAddress(): string;
}
