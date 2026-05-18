<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

class PickupPoint implements PickupPointInterface
{
    protected ?PickupPointCode $code = null;

    #[Groups(['Detailed', 'Autocomplete'])]
    protected ?string $name = null;

    protected ?string $address = null;

    protected ?string $zipCode = null;

    protected ?string $city = null;

    protected ?string $country = null;

    #[Groups(['Detailed', 'Autocomplete'])]
    protected ?float $latitude = null;

    #[Groups(['Detailed', 'Autocomplete'])]
    protected ?float $longitude = null;

    public function getCode(): ?PickupPointCode
    {
        return $this->code;
    }

    public function setCode(PickupPointCode $code): void
    {
        $this->code = $code;
    }

    #[Groups(['Detailed', 'Autocomplete'])]
    #[SerializedName('code')]
    public function getCodeValue(): ?string
    {
        return null === $this->code ? null : $this->code->getValue();
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

    #[Groups(['Detailed', 'Autocomplete'])]
    #[SerializedName('full_address')]
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
