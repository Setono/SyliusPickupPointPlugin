<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\Type;

use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoder;
use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointTransformer;
use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Verifies — without booting the kernel — that the hidden field round-trips the chosen point through its
 * opaque token: a submitted token decodes to the exact {@see PickupPoint} (persisted to `pickup_point`),
 * and an existing point renders back as a token. No provider is involved.
 */
final class PickupPointTypeTest extends TypeTestCase
{
    private PickupPointEncoder $encoder;

    private PickupPoint $point;

    protected function setUp(): void
    {
        $this->encoder = new PickupPointEncoder();

        $this->point = new PickupPoint();
        $this->point->provider = 'faker';
        $this->point->id = '0';
        $this->point->name = 'Post office #0';
        $this->point->country = 'DK';

        parent::setUp();
    }

    public function testSubmittingATokenDecodesToThePickupPoint(): void
    {
        $form = $this->factory->create(PickupPointType::class);
        $form->submit($this->encoder->encode($this->point));

        self::assertTrue($form->isSynchronized());
        self::assertEquals($this->point, $form->getData());
    }

    public function testAnExistingPickupPointRendersBackAsAToken(): void
    {
        $form = $this->factory->create(PickupPointType::class, $this->point);

        self::assertSame($this->encoder->encode($this->point), $form->getViewData());
    }

    public function testSubmittingAnEmptyValueResultsInNull(): void
    {
        $form = $this->factory->create(PickupPointType::class);
        $form->submit('');

        self::assertTrue($form->isSynchronized());
        self::assertNull($form->getData());
    }

    public function testSubmittingAMalformedTokenFailsTransformation(): void
    {
        $form = $this->factory->create(PickupPointType::class);
        $form->submit('!!! not a valid token !!!');

        self::assertFalse($form->isSynchronized());
        self::assertNull($form->getData());
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new PickupPointType(new PickupPointTransformer($this->encoder)),
            ], []),
        ];
    }
}
