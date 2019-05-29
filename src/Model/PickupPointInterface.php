<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

interface PickupPointInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getAddress(): string;

    public function getZipCode(): string;

    public function getCity(): string;

    public function getCountry(): string;

    public function getLatitude(): string;

    public function getLongitude(): string;
}
