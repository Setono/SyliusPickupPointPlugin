<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler\RegisterProvidersPass;
use Setono\SyliusPickupPointPlugin\SetonoSyliusPickupPointPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SetonoSyliusPickupPointPluginTest extends TestCase
{
    public function testItIsASymfonyBundle(): void
    {
        self::assertInstanceOf(Bundle::class, new SetonoSyliusPickupPointPlugin());
    }

    public function testItRegistersTheRegisterProvidersCompilerPass(): void
    {
        $container = new ContainerBuilder();

        (new SetonoSyliusPickupPointPlugin())->build($container);

        $hasPass = false;
        foreach ($container->getCompilerPassConfig()->getPasses() as $pass) {
            if ($pass instanceof RegisterProvidersPass) {
                $hasPass = true;

                break;
            }
        }

        self::assertTrue($hasPass, 'Expected build() to register the RegisterProvidersPass compiler pass.');
    }

    public function testItRegistersTheCompilerPassInTheBeforeOptimizationPhase(): void
    {
        $container = new ContainerBuilder();

        (new SetonoSyliusPickupPointPlugin())->build($container);

        $beforeOptimizationPasses = $container
            ->getCompilerPassConfig()
            ->getBeforeOptimizationPasses()
        ;

        $hasPass = false;
        foreach ($beforeOptimizationPasses as $pass) {
            if ($pass instanceof RegisterProvidersPass) {
                $hasPass = true;

                break;
            }
        }

        self::assertTrue($hasPass, 'Expected the pass to be registered in the default before-optimization phase.');
    }

    public function testItsPathPointsAtTheRepositoryRoot(): void
    {
        self::assertSame(\dirname(__DIR__, 2), (new SetonoSyliusPickupPointPlugin())->getPath());
    }
}
