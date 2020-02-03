<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class LocalProvider extends Provider
{
    /** @var ProviderInterface */
    private $provider;

    /** @var PickupPointRepositoryInterface */
    private $pickupPointRepository;

    public function __construct(ProviderInterface $provider, PickupPointRepositoryInterface $pickupPointRepository)
    {
        $this->provider = $provider;
        $this->pickupPointRepository = $pickupPointRepository;
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        try {
            return $this->provider->findPickupPoints($order);
        } catch (TimeoutException $e) {
            return $this->pickupPointRepository->findByOrder($order, $this->provider->getCode());
        }
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        try {
            return $this->provider->findPickupPoint($code);
        } catch (TimeoutException $e) {
            return $this->pickupPointRepository->findOneByCode($code);
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
