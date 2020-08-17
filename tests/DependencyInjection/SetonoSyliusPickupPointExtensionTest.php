<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusPickupPointPlugin\DependencyInjection\SetonoSyliusPickupPointExtension;

final class SetonoSyliusPickupPointExtensionTest extends AbstractExtensionTestCase
{
    protected function getMinimalConfiguration(): array
    {
        return [
            'cache' => [
                'enabled' => false,
            ],
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

    /**
     * @test
     */
    public function after_loading_the_correct_parameters_has_been_set(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('setono_sylius_pickup_point.local', true);
        $this->assertContainerBuilderHasParameter('setono_sylius_pickup_point.cache.enabled', false);
    }
}
