<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

interface PickupPointInterface
{
    public const TYPE_DELIMITER = '-';

    public function getProviderCode(): string;

    public function getId(): string;

    /**
     * A unique id across all providers, @see PickupPoint::getFullId() for an example
     *
     * @return string
     */
    public function getFullId(): string;

    public function getName(): string;

    public function getFullName(): string;

    public function getAddress(): string;

    public function getZipCode(): string;

    public function getCity(): string;

    public function getCountry(): string;

    public function getLatitude(): string;

    public function getLongitude(): string;
}
