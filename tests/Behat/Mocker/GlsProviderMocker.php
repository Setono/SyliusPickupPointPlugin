<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Mocker;

use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Provider\Provider;
use Sylius\Component\Core\Model\OrderInterface;

class GlsProviderMocker extends Provider
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

    public function findPickupPoints(OrderInterface $order): iterable
    {
        return [
            $this->findPickupPoint(new PickupPointCode('', '', '')),
        ];
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        return new PickupPoint(
            new PickupPointCode(self::PICKUP_POINT_ID, $this->getCode(), 'DK'),
            'Somewhere',
            '1 Rainbow str',
            '4499',
            'Aalborg',
            'DK',
            '23N',
            '180E'
        );
    }

    public function findAllPickupPoints(): iterable
    {
        return [
            $this->findPickupPoint(new PickupPointCode('', '', '')),
        ];
    }
}
