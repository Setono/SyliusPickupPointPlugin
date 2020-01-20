<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\PickupPoint;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class PickupPointId
{
    private const DELIMITER = '---';

    /** @var string */
    private $id;

    /** @var string */
    private $provider;

    /**
     * Some providers will only have unique ids per country
     * hence we need the country to make it completely unique in these cases
     *
     * @var string
     */
    private $country;

    /**
     * @param mixed $id
     */
    public function __construct($id, string $provider, string $country)
    {
        Assert::scalar($id);

        $this->id = (string) $id;
        $this->provider = $provider;
        $this->country = $country;
    }

    public static function createFromString(string $val): self
    {
        $parts = explode(self::DELIMITER, $val);

        if (!isset($parts[0])) {
            throw new InvalidArgumentException('No provider part provided');
        }

        if (!isset($parts[1])) {
            throw new InvalidArgumentException('No id part provided');
        }

        if (!isset($parts[2])) {
            throw new InvalidArgumentException('No country part provided');
        }

        return new self($parts[1], $parts[0], $parts[2]);
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        return $this->provider . self::DELIMITER . $this->id . self::DELIMITER . $this->country;
    }

    public function getIdPart(): string
    {
        return $this->id;
    }

    public function getProviderPart(): string
    {
        return $this->provider;
    }

    public function getCountryPart(): string
    {
        return $this->country;
    }
}
