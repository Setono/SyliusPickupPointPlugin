<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use RuntimeException;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Sylius\Component\Core\Model\OrderInterface;

final class LocalProvider extends Provider
{
    /** @var ProviderInterface */
    private $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        try {
            return $this->provider->findPickupPoints($order);
        } catch (TimeoutException $e) {
            // todo find pickup points in local database
            throw new RuntimeException('Not implemented');
        }
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPoint
    {
        try {
            return $this->provider->findPickupPoint($code);
        } catch (TimeoutException $e) {
            // todo find pickup point in local database
            throw new RuntimeException('Not implemented');
        }
    }

    public function findAllPickupPoints(): iterable
    {
        yield from $this->provider->findAllPickupPoints();
    }

    public function getCode(): string
    {
        return $this->provider->getCode();
    }

    public function getName(): string
    {
        return $this->provider->getName();
    }
}
