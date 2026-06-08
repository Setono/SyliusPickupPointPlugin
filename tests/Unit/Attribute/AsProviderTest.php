<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Attribute;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;

final class AsProviderTest extends TestCase
{
    public function testItExposesCodeAndNameFromTheConstructor(): void
    {
        $attribute = new AsProvider('gls', 'GLS');

        self::assertSame('gls', $attribute->code);
        self::assertSame('GLS', $attribute->name);
    }

    public function testItIsDeclaredAsAnAttributeTargetingClasses(): void
    {
        $reflection = new \ReflectionClass(AsProvider::class);

        $attributes = $reflection->getAttributes(\Attribute::class);

        self::assertCount(1, $attributes);
        self::assertSame(\Attribute::TARGET_CLASS, $attributes[0]->newInstance()->flags);
    }
}
