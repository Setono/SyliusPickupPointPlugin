<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Manager;

use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

interface ProviderManagerInterface
{
    /**
     * @return ProviderInterface[]
     */
    public function all(): array;

    /**
     * @param string $code
     *
     * @return bool
     */
    public function has(string $code): bool;

    /**
     * @param string $class
     *
     * @return ProviderInterface|null
     */
    public function findByClassName(string $class): ?ProviderInterface;

    /**
     * @param string $code
     *
     * @return ProviderInterface|null
     */
    public function findByCode(string $code): ?ProviderInterface;
}
