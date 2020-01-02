<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Mocker;

use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPoint;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPointId;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;

class GlsProviderMocker implements ProviderInterface
{
    public const PICKUP_POINT_ID = '001';

    public function getCode(): string
    {
        return 'gls';
    }

    public function getName(): string
    {
        return 'GLS';
    }

    public function findPickupPoints(OrderInterface $order): array
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
}
