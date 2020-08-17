<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Provider;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Provider\FakerProvider;
use Setono\SyliusPickupPointPlugin\Provider\LocalProvider;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class LocalProviderTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_code_from_decorated_provider(): void
    {
        $provider = $this->getProvider();
        self::assertSame('faker', $provider->getCode());
    }

    /**
     * @test
     */
    public function it_gets_name_from_decorated_provider(): void
    {
        $provider = $this->getProvider();
        self::assertSame('Faker', $provider->getName());
    }

    /**
     * @test
     */
    public function it_uses_local_provider_if_decorated_timeouts(): void
    {
        $order = $this->prophesize(OrderInterface::class);

        $pickupPoint = new PickupPoint(
            new PickupPointCode('123', 'gls', 'DK'),
            'Service Point', 'Street 123', '1235A', 'Great City', 'DK'
        );

        $repository = $this->prophesize(PickupPointRepositoryInterface::class);
        $repository->findByOrder($order, 'timeout_provider')->willReturn([$pickupPoint]);

        $provider = $this->getProvider(true, $repository->reveal());

        $pickupPoints = $provider->findPickupPoints($order->reveal());

        self::assertNotEmpty($pickupPoints);
        self::assertSame($pickupPoint, $pickupPoints[0]);
    }

    private function getProvider(bool $timeout = false, PickupPointRepositoryInterface $pickupPointRepository = null): LocalProvider
    {
        $provider = new FakerProvider();
        if ($timeout) {
            $provider = new class() implements ProviderInterface {
                public function __toString(): string
                {
                    return $this->getName();
                }

                public function getCode(): string
                {
                    return 'timeout_provider';
                }

                public function getName(): string
                {
                    return 'Timeout provider';
                }

                public function findPickupPoints(OrderInterface $order): iterable
                {
                    throw new TimeoutException();
                }

                public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
                {
                    throw new TimeoutException();
                }

                public function findAllPickupPoints(): iterable
                {
                    throw new TimeoutException();
                }
            };
        }

        if (null === $pickupPointRepository) {
            $repository = $this->prophesize(PickupPointRepositoryInterface::class);
            $pickupPointRepository = $repository->reveal();
        }

        return new LocalProvider($provider, $pickupPointRepository);
    }
}
