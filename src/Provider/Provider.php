<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

abstract class Provider implements ProviderInterface
{
    public function __toString(): string
    {
        return $this->getCode();
    }
}
