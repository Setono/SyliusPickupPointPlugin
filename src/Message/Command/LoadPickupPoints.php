<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Message\Command;

use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Webmozart\Assert\Assert;

final class LoadPickupPoints implements CommandInterface
{
    /** @var string */
    private $provider;

    /**
     * @param mixed|ProviderInterface $provider
     */
    public function __construct($provider)
    {
        if ($provider instanceof ProviderInterface) {
            $provider = $provider->getCode();
        }

        Assert::string($provider);

        $this->provider = $provider;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }
}
