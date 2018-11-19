<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Manager;

use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

interface ProviderManagerInterface
{
    /**
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider): void;

    /**
     * @return ProviderInterface[]
     */
    public function all(): array;

    /**
     * @param string $class
     *
     * @return ProviderInterface|null
     */
    public function findByClassName(string $class): ?ProviderInterface;

    /**
     * @param string $code
     *
     * @return null|ProviderInterface
     */
    public function findByCode(string $code): ?ProviderInterface;
}
