<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use function Safe\sprintf;

/**
 * It is not intended to extend/override this class
 *
 * @final
 */
class PickupPoint implements PickupPointInterface
{
    /** @var int|null */
    protected $id;

    /** @var PickupPointCode */
    protected $code;

    /** @var string|null */
    protected $name;

    /** @var string|null */
    protected $address;

    /** @var string|null */
    protected $zipCode;

    /** @var string|null */
    protected $city;

    /** @var string|null */
    protected $country;

    /** @var string|null */
    protected $latitude;

    /** @var string|null */
    protected $longitude;

    public function __construct(
        PickupPointCode $code,
        string $name,
        string $address,
        string $zipCode,
        string $city,
        string $country,
        string $latitude = null,
        string $longitude = null
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->address = $address;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->country = $country;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): PickupPointCode
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

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
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

    public function getFullAddress(): string
    {
        return sprintf(
            '%s, %s %s',
            $this->getAddress(),
            $this->getZipCode(),
            $this->getCity()
        );
    }
}
