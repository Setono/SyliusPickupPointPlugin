<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Webmozart\Assert\Assert;

final class PickupPoint implements PickupPointInterface
{
    /**
     * Should not contain self::TYPE_DELIMITER
     *
     * @var string
     */
    private $providerCode;

    /** @var string */
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

    /** @var string */
    private $latitude;

    /** @var string */
    private $longitude;

    public function __construct(string $providerCode, string $id, string $name, string $address, string $zipCode, string $city, string $country, string $latitude, string $longitude)
    {
        Assert::notContains($providerCode, self::TYPE_DELIMITER, sprintf('The provider code "%s" should not contain the delimiter "%s"', $providerCode, self::TYPE_DELIMITER));

        $this->providerCode = $providerCode;
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->country = $country;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getProviderCode(): string
    {
        return $this->providerCode;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFullId(): string
    {
        return sprintf(
            '%s%s%s',
            $this->getProviderCode(),
            self::TYPE_DELIMITER,
            $this->getId()
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        return sprintf(
            '%s, %s, %s, %s',
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

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }
}
