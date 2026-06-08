<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Controller\Action;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointsAction;
use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoderInterface;
use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class PickupPointsActionTest extends TestCase
{
    public function testItReturnsAnEmptyResponseWhenThereIsNoCart(): void
    {
        $cartContext = $this->createMock(CartContextInterface::class);
        $cartContext->method('getCart')->willThrowException(new CartNotFoundException());

        $action = new PickupPointsAction(
            $cartContext,
            $this->createMock(ProviderRegistryInterface::class),
            $this->createMock(PickupPointEncoderInterface::class),
            $this->createMock(ShippingMethodsResolverInterface::class),
        );

        $response = $action();

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame([], $this->decode($response));
    }

    public function testItKeysEncodedPointsByPickupCapableMethodCode(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->name = 'Post office #0';
        $pickupPoint->address = 'Main Street 1';
        $pickupPoint->zipCode = '9000';
        $pickupPoint->city = 'Aalborg';
        $pickupPoint->latitude = '57.0488';
        $pickupPoint->longitude = '9.9217';

        $provider = $this->createMock(ProviderInterface::class);
        $provider->method('findPickupPoints')->willReturn([$pickupPoint]);

        $registry = $this->createMock(ProviderRegistryInterface::class);
        $registry->method('has')->with('faker')->willReturn(true);
        $registry->method('get')->with('faker')->willReturn($provider);

        $encoder = $this->createMock(PickupPointEncoderInterface::class);
        $encoder->method('encode')->with($pickupPoint)->willReturn('encoded-token');

        $method = $this->pickupMethod('UPS_PICKUP', 'faker');
        $resolver = $this->resolverReturning($method);

        $action = new PickupPointsAction(
            $this->cartContextWithOneShipment(),
            $registry,
            $encoder,
            $resolver,
        );

        $response = $action();

        self::assertSame([
            'UPS_PICKUP' => [
                [
                    'value' => 'encoded-token',
                    'name' => 'Post office #0',
                    'address' => 'Main Street 1',
                    'zipCode' => '9000',
                    'city' => 'Aalborg',
                    'latitude' => '57.0488',
                    'longitude' => '9.9217',
                ],
            ],
        ], $this->decode($response));
    }

    public function testItSkipsMethodsThatHaveNoPickupPointProvider(): void
    {
        $method = $this->createMock(ShippingMethodInterface::class);
        $method->method('hasPickupPointProvider')->willReturn(false);
        $method->expects(self::never())->method('getCode');

        $action = new PickupPointsAction(
            $this->cartContextWithOneShipment(),
            $this->createMock(ProviderRegistryInterface::class),
            $this->createMock(PickupPointEncoderInterface::class),
            $this->resolverReturning($method),
        );

        self::assertSame([], $this->decode($action()));
    }

    public function testItSkipsMethodsWhoseProviderIsNotRegistered(): void
    {
        $method = $this->pickupMethod('UNKNOWN_PICKUP', 'gone');

        $registry = $this->createMock(ProviderRegistryInterface::class);
        $registry->method('has')->with('gone')->willReturn(false);
        $registry->expects(self::never())->method('get');

        $action = new PickupPointsAction(
            $this->cartContextWithOneShipment(),
            $registry,
            $this->createMock(PickupPointEncoderInterface::class),
            $this->resolverReturning($method),
        );

        self::assertSame([], $this->decode($action()));
    }

    public function testAFailingProviderMapsToAnEmptyListWhileOthersStillReturnTheirPoints(): void
    {
        $okPoint = new PickupPoint();
        $okPoint->name = 'Working point';

        $okProvider = $this->createMock(ProviderInterface::class);
        $okProvider->method('findPickupPoints')->willReturn([$okPoint]);

        $failingProvider = $this->createMock(ProviderInterface::class);
        $failingProvider->method('findPickupPoints')->willThrowException(new \RuntimeException('carrier is down'));

        $registry = $this->createMock(ProviderRegistryInterface::class);
        $registry->method('has')->willReturnMap([
            ['ok', true],
            ['failing', true],
        ]);
        $registry->method('get')->willReturnMap([
            ['ok', $okProvider],
            ['failing', $failingProvider],
        ]);

        $encoder = $this->createMock(PickupPointEncoderInterface::class);
        $encoder->method('encode')->with($okPoint)->willReturn('ok-token');

        $okMethod = $this->pickupMethod('OK_PICKUP', 'ok');
        $failingMethod = $this->pickupMethod('FAILING_PICKUP', 'failing');

        $action = new PickupPointsAction(
            $this->cartContextWithOneShipment(),
            $registry,
            $encoder,
            $this->resolverReturning($okMethod, $failingMethod),
        );

        self::assertSame([
            'OK_PICKUP' => [
                [
                    'value' => 'ok-token',
                    'name' => 'Working point',
                    'address' => null,
                    'zipCode' => null,
                    'city' => null,
                    'latitude' => null,
                    'longitude' => null,
                ],
            ],
            'FAILING_PICKUP' => [],
        ], $this->decode($action()));
    }

    public function testItComputesEachMethodOnlyOnceAcrossShipments(): void
    {
        $point = new PickupPoint();
        $point->name = 'Only resolved once';

        $provider = $this->createMock(ProviderInterface::class);
        // The provider must be hit exactly once even though the same method appears on two shipments.
        $provider->expects(self::once())->method('findPickupPoints')->willReturn([$point]);

        $registry = $this->createMock(ProviderRegistryInterface::class);
        $registry->method('has')->with('faker')->willReturn(true);
        $registry->method('get')->with('faker')->willReturn($provider);

        $encoder = $this->createMock(PickupPointEncoderInterface::class);
        $encoder->method('encode')->with($point)->willReturn('token');

        $method = $this->pickupMethod('SHARED_PICKUP', 'faker');

        $resolver = $this->createMock(ShippingMethodsResolverInterface::class);
        $resolver->method('getSupportedMethods')->willReturn([$method]);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getShippingAddress')->willReturn(null);
        $order->method('getShipments')->willReturn(new ArrayCollection([
            $this->createMock(ShipmentInterface::class),
            $this->createMock(ShipmentInterface::class),
        ]));

        $cartContext = $this->createMock(CartContextInterface::class);
        $cartContext->method('getCart')->willReturn($order);

        $action = new PickupPointsAction($cartContext, $registry, $encoder, $resolver);

        self::assertSame([
            'SHARED_PICKUP' => [
                [
                    'value' => 'token',
                    'name' => 'Only resolved once',
                    'address' => null,
                    'zipCode' => null,
                    'city' => null,
                    'latitude' => null,
                    'longitude' => null,
                ],
            ],
        ], $this->decode($action()));
    }

    public function testItPassesTheOrderShippingAddressToTheProvider(): void
    {
        $captured = null;

        $provider = $this->createMock(ProviderInterface::class);
        $provider->method('findPickupPoints')->willReturnCallback(function (Address $address) use (&$captured): array {
            $captured = $address;

            return [];
        });

        $registry = $this->createMock(ProviderRegistryInterface::class);
        $registry->method('has')->with('faker')->willReturn(true);
        $registry->method('get')->with('faker')->willReturn($provider);

        $shippingAddress = $this->createMock(\Sylius\Component\Core\Model\AddressInterface::class);
        $shippingAddress->method('getStreet')->willReturn('Main Street 1');
        $shippingAddress->method('getPostcode')->willReturn('9000');
        $shippingAddress->method('getCity')->willReturn('Aalborg');
        $shippingAddress->method('getCountryCode')->willReturn('DK');

        $order = $this->createMock(OrderInterface::class);
        $order->method('getShippingAddress')->willReturn($shippingAddress);
        $order->method('getShipments')->willReturn(new ArrayCollection([$this->createMock(ShipmentInterface::class)]));

        $cartContext = $this->createMock(CartContextInterface::class);
        $cartContext->method('getCart')->willReturn($order);

        $action = new PickupPointsAction(
            $cartContext,
            $registry,
            $this->createMock(PickupPointEncoderInterface::class),
            $this->resolverReturning($this->pickupMethod('UPS_PICKUP', 'faker')),
        );

        $action();

        self::assertInstanceOf(Address::class, $captured);
        self::assertSame('Main Street 1', $captured->street);
        self::assertSame('9000', $captured->postalCode);
        self::assertSame('Aalborg', $captured->city);
        self::assertSame('DK', $captured->countryCode);
    }

    private function pickupMethod(string $code, string $providerCode): ShippingMethodInterface
    {
        $method = $this->createMock(ShippingMethodInterface::class);
        $method->method('hasPickupPointProvider')->willReturn(true);
        $method->method('getCode')->willReturn($code);
        $method->method('getPickupPointProvider')->willReturn($providerCode);

        return $method;
    }

    private function resolverReturning(ShippingMethodInterface ...$methods): ShippingMethodsResolverInterface
    {
        $resolver = $this->createMock(ShippingMethodsResolverInterface::class);
        $resolver->method('getSupportedMethods')->willReturn($methods);

        return $resolver;
    }

    private function cartContextWithOneShipment(): CartContextInterface
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getShippingAddress')->willReturn(null);
        $order->method('getShipments')->willReturn(new ArrayCollection([$this->createMock(ShipmentInterface::class)]));

        $cartContext = $this->createMock(CartContextInterface::class);
        $cartContext->method('getCart')->willReturn($order);

        return $cartContext;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
