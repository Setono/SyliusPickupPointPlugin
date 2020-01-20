<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Mocker;

use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPoint;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPointId;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;

class PostNordProviderMocker implements ProviderInterface
{
    public const PICKUP_POINT_ID = '001';

    public function getCode(): string
    {
        return 'post_nord';
    }

    public function getName(): string
    {
        return 'PostNord';
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        return [
            $this->findPickupPoint(new PickupPointId('', '')),
        ];
    }

    public function findPickupPoint(PickupPointId $id): ?PickupPoint
    {
        return new PickupPoint(
            new PickupPointId(self::PICKUP_POINT_ID, $this->getCode()),
            'Somewhere',
            '1 Rainbow str',
            '12345',
            'Kyiv',
            'Ukraine',
            '23N',
            '180E'
        );
    }

    public function findAllPickupPoints(): iterable
    {
        return [
            $this->findPickupPoint(new PickupPointId('', '')),
        ];
    }
}
