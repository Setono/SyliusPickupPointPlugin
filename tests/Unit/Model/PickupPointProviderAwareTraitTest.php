<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareTrait;

final class PickupPointProviderAwareTraitTest extends TestCase
{
    private function getSubject(): PickupPointProviderAwareInterface
    {
        return new class() implements PickupPointProviderAwareInterface {
            use PickupPointProviderAwareTrait;
        };
    }

    public function testItDefaultsToNoProvider(): void
    {
        $subject = $this->getSubject();

        self::assertNull($subject->getPickupPointProvider());
        self::assertFalse($subject->hasPickupPointProvider());
    }

    public function testItSetsAndGetsTheProvider(): void
    {
        $subject = $this->getSubject();

        $subject->setPickupPointProvider('gls');

        self::assertSame('gls', $subject->getPickupPointProvider());
        self::assertTrue($subject->hasPickupPointProvider());
    }

    public function testItReportsNoProviderAfterResettingToNull(): void
    {
        $subject = $this->getSubject();
        $subject->setPickupPointProvider('postnord');

        $subject->setPickupPointProvider(null);

        self::assertNull($subject->getPickupPointProvider());
        self::assertFalse($subject->hasPickupPointProvider());
    }
}
