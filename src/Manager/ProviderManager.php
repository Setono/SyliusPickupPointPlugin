<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Manager;

use Setono\SyliusPickupPointPlugin\Exception\NonUniqueProviderCodeException;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

class ProviderManager implements ProviderManagerInterface
{
    /**
     * @var ProviderInterface[]
     */
    private $providers = [];

    public function __construct(ProviderInterface ...$providers)
    {
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function addProvider(ProviderInterface $provider): void
    {
        if ($this->has($provider->getCode())) {
            throw new NonUniqueProviderCodeException($provider);
        }

        if ($provider->isEnabled()) {
            $this->providers[$provider->getCode()] = $provider;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $code): bool
    {
        return \array_key_exists($code, $this->providers);
    }

    /**
     * {@inheritdoc}
     */
    public function findByClassName(string $class): ?ProviderInterface
    {
        foreach ($this->providers as $provider) {
            if (get_class($provider) === $class) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findByCode(string $code): ?ProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->getCode() === $code) {
                return $provider;
            }
        }

        return null;
    }
}
