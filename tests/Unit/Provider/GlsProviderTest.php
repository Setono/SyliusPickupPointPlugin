<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Provider;

use PHPUnit\Framework\TestCase;
use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\GLS\Webservice\Exception\ParcelShopNotFoundException;
use Setono\GLS\Webservice\Model\ParcelShop;
use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Provider\GlsProvider;

final class GlsProviderTest extends TestCase
{
    public function testItMapsTheNearestParcelShopsToPickupPoints(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects(self::once())
            ->method('searchNearestParcelShops')
            ->with('Main Street 1', '9000', 'DK', 10)
            ->willReturn([
                new ParcelShop(
                    '123',
                    'Post office #1',
                    'Some street 1',
                    '9000',
                    'Aalborg',
                    'DK',
                    '9.9217',
                    '57.0488',
                ),
                new ParcelShop(
                    '456',
                    'Post office #2',
                    'Some street 2',
                    '8000',
                    'Aarhus',
                    'DK',
                    '10.2039',
                    '56.1629',
                ),
            ]);

        $provider = new GlsProvider($client);
        $provider->setCode('gls');

        $pickupPoints = $provider->findPickupPoints(new Address('Main Street 1', '9000', 'Aalborg', 'DK'));

        self::assertCount(2, $pickupPoints);

        $first = $pickupPoints[0];
        self::assertInstanceOf(PickupPoint::class, $first);
        self::assertSame('gls', $first->provider);
        self::assertSame('123', $first->id);
        self::assertSame('Post office #1', $first->name);
        self::assertSame('Some street 1', $first->address);
        self::assertSame('9000', $first->zipCode);
        self::assertSame('Aalborg', $first->city);
        self::assertSame('DK', $first->country);
        self::assertSame('57.0488', $first->latitude);
        self::assertSame('9.9217', $first->longitude);

        self::assertSame('456', $pickupPoints[1]->id);
    }

    public function testItStripsWhitespaceFromThePostCodeBeforeQuerying(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects(self::once())
            ->method('searchNearestParcelShops')
            ->with('Main Street 1', '9000', 'DK', 10)
            ->willReturn([]);

        $provider = new GlsProvider($client);
        $provider->setCode('gls');

        self::assertSame([], $provider->findPickupPoints(new Address('Main Street 1', '90 00', 'Aalborg', 'DK')));
    }

    public function testItReturnsAnEmptyListWhenTheStreetIsNull(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())->method('searchNearestParcelShops');

        $provider = new GlsProvider($client);
        $provider->setCode('gls');

        self::assertSame([], $provider->findPickupPoints(new Address(null, '9000', 'Aalborg', 'DK')));
    }

    public function testItReturnsAnEmptyListWhenThePostCodeIsNull(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())->method('searchNearestParcelShops');

        $provider = new GlsProvider($client);
        $provider->setCode('gls');

        self::assertSame([], $provider->findPickupPoints(new Address('Main Street 1', null, 'Aalborg', 'DK')));
    }

    public function testItReturnsAnEmptyListWhenTheCountryCodeIsNull(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())->method('searchNearestParcelShops');

        $provider = new GlsProvider($client);
        $provider->setCode('gls');

        self::assertSame([], $provider->findPickupPoints(new Address('Main Street 1', '9000', 'Aalborg', null)));
    }

    public function testItFindsASinglePickupPoint(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects(self::once())
            ->method('getOneParcelShop')
            ->with('123')
            ->willReturn(new ParcelShop(
                '123',
                'Post office #1',
                'Some street 1',
                '9000',
                'Aalborg',
                'DK',
                '9.9217',
                '57.0488',
            ));

        $provider = new GlsProvider($client);
        $provider->setCode('gls');

        $pickupPoint = $provider->findPickupPoint('123');

        self::assertInstanceOf(PickupPoint::class, $pickupPoint);
        self::assertSame('gls', $pickupPoint->provider);
        self::assertSame('123', $pickupPoint->id);
        self::assertSame('Aalborg', $pickupPoint->city);
    }

    public function testItReturnsNullWhenTheParcelShopIsNotFound(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects(self::once())
            ->method('getOneParcelShop')
            ->with('does-not-exist')
            ->willThrowException(new ParcelShopNotFoundException('does-not-exist'));

        $provider = new GlsProvider($client);
        $provider->setCode('gls');

        self::assertNull($provider->findPickupPoint('does-not-exist'));
    }
}
