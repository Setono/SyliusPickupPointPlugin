<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Functional\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\ShipmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Renders the two admin pages the plugin extends, against the loaded fixtures. These cover the twig-hook
 * wiring (the admin order-show pickup-point label and the shipping-method provider field) which unit tests
 * cannot — e.g. they would have caught the label template referencing a `shipment` variable that the hook
 * does not expose.
 */
final class AdminPickupPointPagesTest extends WebTestCase
{
    public function testTheOrderShowPageRendersTheSelectedPickupPoint(): void
    {
        $client = self::createClient();
        $this->logInAsAdmin($client);

        $order = $this->findOrderWithShipment();

        $shipment = $order->getShipments()->first();
        self::assertInstanceOf(ShipmentInterface::class, $shipment);

        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = 'faker';
        $pickupPoint->id = '42';
        $pickupPoint->name = 'Post office #42';
        $pickupPoint->address = 'Main Street 1';
        $pickupPoint->zipCode = '9000';
        $pickupPoint->city = 'Aalborg';
        $pickupPoint->country = 'DK';
        $shipment->setPickupPoint($pickupPoint);

        /** @var EntityManagerInterface $manager */
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        $manager->flush();

        $client->request('GET', '/admin/orders/' . $order->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.setono-sylius-pickup-point-label', 'Post office #42');
    }

    public function testTheShippingMethodFormRendersThePickupPointProviderField(): void
    {
        $client = self::createClient();
        $this->logInAsAdmin($client);

        $shippingMethod = $this->repository('sylius.repository.shipping_method')->findOneBy([]);
        self::assertInstanceOf(ShippingMethodInterface::class, $shippingMethod);

        $client->request('GET', '/admin/shipping-methods/' . $shippingMethod->getId() . '/edit');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[name*="pickupPointProvider"]');
    }

    private function logInAsAdmin(KernelBrowser $client): void
    {
        $admin = $this->repository('sylius.repository.admin_user')->findOneBy([]);
        self::assertInstanceOf(UserInterface::class, $admin, 'No admin user found — were the test fixtures loaded?');

        $client->loginUser($admin, 'admin');
    }

    private function findOrderWithShipment(): OrderInterface
    {
        foreach ($this->repository('sylius.repository.order')->findBy([], null, 100) as $order) {
            if ($order instanceof OrderInterface && !$order->getShipments()->isEmpty()) {
                return $order;
            }
        }

        self::fail('No order with a shipment found — were the test fixtures loaded?');
    }

    private function repository(string $id): ObjectRepository
    {
        $repository = self::getContainer()->get($id);
        self::assertInstanceOf(ObjectRepository::class, $repository);

        return $repository;
    }
}
