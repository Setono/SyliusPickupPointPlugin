<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Mocker;

use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;

class PostNordProviderMocker implements ProviderInterface
{
    const PICKUP_POINT_ID = '001';

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    public function findPickupPoints(OrderInterface $order): array
    {
        return [
            $this->findOnePickupPointById('')
        ];
    }

    public function findOnePickupPointById(string $id): ?PickupPointInterface
    {
        return new PickupPoint(
            $this->getCode(),
            self::PICKUP_POINT_ID,
            'Somewhere',
            '1 Rainbow str',
            '12345',
            'Kyiv',
            'Ukraine',
            '23N',
            '180E'
        );
    }
}
