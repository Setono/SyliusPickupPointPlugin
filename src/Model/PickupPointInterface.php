<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

interface PickupPointInterface
{
    /**
     * This method must return the id from the provider that identifies the pickup point
     * This is not an internal id
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the address
     *
     * @return string
     */
    public function getAddress(): string;

    /**
     * Returns the zip code
     *
     * @return string
     */
    public function getZipCode(): string;

    /**
     * Returns the city
     *
     * @return string
     */
    public function getCity(): string;

    /**
     * Returns the country
     *
     * @return string
     */
    public function getCountry(): string;

    /**
     * Returns the latitude
     *
     * @return string
     */
    public function getLatitude(): string;

    /**
     * Returns the longitude
     *
     * @return string
     */
    public function getLongitude(): string;
}
