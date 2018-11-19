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
    private $providers;

    public function __construct()
    {
        $this->providers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function addProvider(ProviderInterface $provider): void
    {
        foreach ($this->providers as $item) {
            if ($provider->getCode() === $item->getCode()) {
                throw NonUniqueProviderCodeException::create($provider);
            }
        }

        $this->providers[] = $provider;
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
