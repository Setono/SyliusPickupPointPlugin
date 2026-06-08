<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Fixture;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Fixture\ShippingMethodFixture;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ShippingMethodFixtureTest extends TestCase
{
    private ShippingMethodFixture $fixture;

    protected function setUp(): void
    {
        $this->fixture = new ShippingMethodFixture(
            $this->createMock(ObjectManager::class),
            $this->createMock(ExampleFactoryInterface::class),
        );
    }

    public function testItHasItsOwnName(): void
    {
        self::assertSame('setono_sylius_pickup_point_shipping_method', $this->fixture->getName());
    }

    public function testItAddsThePickupPointProviderNodeToTheCustomResources(): void
    {
        $resource = $this->processFirstCustomResource([
            'code' => 'pickup_method',
            'pickup_point_provider' => 'dao',
        ]);

        self::assertSame('dao', $resource['pickup_point_provider']);
    }

    public function testItStillAcceptsTheInheritedBaseResourceNodes(): void
    {
        $resource = $this->processFirstCustomResource([
            'code' => 'pickup_method',
            'name' => 'Pickup method',
            'enabled' => true,
            'calculator' => [
                'type' => 'flat_rate',
                'configuration' => ['amount' => 100],
            ],
            'pickup_point_provider' => 'gls',
        ]);

        self::assertSame('pickup_method', $resource['code']);
        self::assertSame('Pickup method', $resource['name']);
        self::assertTrue($resource['enabled']);
        self::assertIsArray($resource['calculator']);
        self::assertSame('flat_rate', $resource['calculator']['type']);
        self::assertSame('gls', $resource['pickup_point_provider']);
    }

    public function testItRejectsAnEmptyPickupPointProvider(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process([
            'custom' => [
                [
                    'code' => 'pickup_method',
                    'pickup_point_provider' => '',
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<array-key, mixed>
     */
    private function process(array $config): array
    {
        return (new Processor())->process(
            $this->fixture->getConfigTreeBuilder()->buildTree(),
            [$config],
        );
    }

    /**
     * Processes a single custom resource and returns its resolved configuration.
     *
     * @param array<string, mixed> $resource
     *
     * @return array<array-key, mixed>
     */
    private function processFirstCustomResource(array $resource): array
    {
        $config = $this->process(['custom' => [$resource]]);

        self::assertIsArray($config['custom']);
        self::assertArrayHasKey(0, $config['custom']);
        self::assertIsArray($config['custom'][0]);

        return $config['custom'][0];
    }
}
