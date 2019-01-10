<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

class NonUniqueProviderCodeException extends \InvalidArgumentException
{
    /**
     * @param ProviderInterface $provider
     */
    public function __construct(ProviderInterface $provider)
    {
        parent::__construct(sprintf('The code %s is not unique. Found in %s', $provider->getCode(), get_class($provider)));
    }
}
