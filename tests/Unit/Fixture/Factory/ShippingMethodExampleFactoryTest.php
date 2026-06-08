<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Fixture\Factory;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Fixture\Factory\ShippingMethodExampleFactory;
use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ShippingMethodExampleFactory as BaseShippingMethodExampleFactory;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Shipping\Calculator\DefaultCalculators;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class ShippingMethodExampleFactoryTest extends TestCase
{
    public function testItExtendsTheSyliusExampleFactory(): void
    {
        self::assertInstanceOf(BaseShippingMethodExampleFactory::class, $this->createFactory());
    }

    public function testItRegistersThePickupPointProviderOption(): void
    {
        $shippingMethod = $this->createMock(ShippingMethodInterface::class);
        $shippingMethod->expects(self::once())->method('setPickupPointProvider')->with('dao');

        $factory = $this->createFactory($shippingMethod);

        $result = $factory->create($this->baseOptions(['pickup_point_provider' => 'dao']));

        self::assertSame($shippingMethod, $result);
    }

    public function testItAcceptsANullPickupPointProvider(): void
    {
        $shippingMethod = $this->createMock(ShippingMethodInterface::class);
        $shippingMethod->expects(self::once())->method('setPickupPointProvider')->with(null);

        $factory = $this->createFactory($shippingMethod);

        $factory->create($this->baseOptions(['pickup_point_provider' => null]));
    }

    public function testItDoesNotTouchTheProviderWhenTheOptionIsAbsent(): void
    {
        $shippingMethod = $this->createMock(ShippingMethodInterface::class);
        $shippingMethod->expects(self::never())->method('setPickupPointProvider');

        $factory = $this->createFactory($shippingMethod);

        $result = $factory->create($this->baseOptions());

        self::assertSame($shippingMethod, $result);
    }

    public function testItDelegatesToTheWrappedFactoryToCreateTheShippingMethod(): void
    {
        $shippingMethod = $this->createMock(ShippingMethodInterface::class);
        $shippingMethod->expects(self::once())->method('setCode')->with('dao-pickup');

        $shippingMethodFactory = $this->createMock(FactoryInterface::class);
        $shippingMethodFactory->expects(self::once())->method('createNew')->willReturn($shippingMethod);

        $factory = $this->createFactory($shippingMethod, $shippingMethodFactory);

        $factory->create($this->baseOptions(['code' => 'dao-pickup', 'pickup_point_provider' => 'dao']));
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function baseOptions(array $overrides = []): array
    {
        return array_merge([
            'code' => 'pickup-method',
            'name' => 'Pickup method',
            'enabled' => true,
            'zone' => null,
            'channels' => [],
            'calculator' => [
                'type' => DefaultCalculators::FLAT_RATE,
                'configuration' => [],
            ],
            'archived_at' => null,
        ], $overrides);
    }

    private function createFactory(
        ?ShippingMethodInterface $shippingMethod = null,
        ?FactoryInterface $shippingMethodFactory = null,
    ): ShippingMethodExampleFactory {
        if (null === $shippingMethodFactory) {
            $shippingMethodFactory = $this->createMock(FactoryInterface::class);
            $shippingMethodFactory
                ->method('createNew')
                ->willReturn($shippingMethod ?? $this->createMock(ShippingMethodInterface::class))
            ;
        }

        $localeRepository = $this->createMock(RepositoryInterface::class);
        $localeRepository->method('findAll')->willReturn([]);

        return new ShippingMethodExampleFactory(
            $shippingMethodFactory,
            $this->createMock(RepositoryInterface::class),
            $this->createMock(RepositoryInterface::class),
            $localeRepository,
            $this->createMock(ChannelRepositoryInterface::class),
            $this->createMock(RepositoryInterface::class),
        );
    }
}
