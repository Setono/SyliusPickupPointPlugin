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
    private $decoratedProvider;

    /** @var PickupPointRepositoryInterface */
    private $pickupPointRepository;

    public function __construct(ProviderInterface $decoratedProvider, PickupPointRepositoryInterface $pickupPointRepository)
    {
        $this->decoratedProvider = $decoratedProvider;
        $this->pickupPointRepository = $pickupPointRepository;
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        try {
            return $this->decoratedProvider->findPickupPoints($order);
        } catch (TimeoutException $e) {
            return $this->pickupPointRepository->findByOrder($order, $this->decoratedProvider->getCode());
        }
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        try {
            return $this->decoratedProvider->findPickupPoint($code);
        } catch (TimeoutException $e) {
            return $this->pickupPointRepository->findOneByCode($code);
        }
    }

    public function findAllPickupPoints(): iterable
    {
        yield from $this->decoratedProvider->findAllPickupPoints();
    }

    public function getCode(): string
    {
        return $this->decoratedProvider->getCode();
    }

    public function getName(): string
    {
        return $this->decoratedProvider->getName();
    }
}
