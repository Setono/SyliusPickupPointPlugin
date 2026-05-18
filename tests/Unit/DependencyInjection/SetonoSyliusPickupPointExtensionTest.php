<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusPickupPointPlugin\DependencyInjection\SetonoSyliusPickupPointExtension;

final class SetonoSyliusPickupPointExtensionTest extends AbstractExtensionTestCase
{
    protected function getMinimalConfiguration(): array
    {
        return [
            'providers' => [
                'faker' => false,
                'dao' => false,
                'gls' => false,
                'post_nord' => false,
            ],
        ];
    }

    protected function getContainerExtensions(): array
    {
        return [
            new SetonoSyliusPickupPointExtension(),
        ];
    }

    public function testItRegistersTheProviderRegistry(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService('setono_sylius_pickup_point.registry.provider');
    }
}
