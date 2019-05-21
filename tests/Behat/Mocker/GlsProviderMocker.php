<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Mocker;

use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GlsProviderMocker implements ProviderInterface
{
    public const PICKUP_POINT_ID = '001';

    /** @var ContainerInterface */
    private $container;

    // todo do not inject container
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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
            [
                'id' => self::PICKUP_POINT_ID,
                'name' => 'Somewhere',
                'address' => 'Rainbow',
                'zipCode' => '12345',
                'city' => 'Nice City',
                'country' => 'Nice City',
            ],
        ];
    }

    public function getPickupPointById(string $id): ?PickupPoint
    {
        return new PickupPoint(
            self::PICKUP_POINT_ID,
            'Some where',
            'Rainbow',
            '12345',
            'Nice City',
            'Nice City',
            '',
            ''
        );
    }

    public function getClient(): \SoapClient
    {
        return $this->container->get('setono_gls_webservice.factory.soap_client')->create();
    }

    public function isEnabled(): bool
    {
        return true;
    }
}
