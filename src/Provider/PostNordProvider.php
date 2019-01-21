<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Lsv\PdDk\Client;
use Lsv\PdDk\Exceptions\MalformedAddressException;
use Lsv\PdDk\Exceptions\ParcelNotFoundException;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class PostNordProvider implements ProviderInterface
{
    use ContainerAwareTrait;

    public const MODE_PRODUCTION = 'production';
    public const MODE_SANDBOX = 'sandbox';

    /**
     * {@inheritdoc}
     */
    public function findPickupPoints(OrderInterface $order): array
    {
        if (null === $order->getShippingAddress()) {
            return [];
        }

        $client = $this->getClient();

        try {
            $result = $client->getParcelshopsNearAddress(
                $order->getShippingAddress()->getStreet(),
                $order->getShippingAddress()->getPostcode(),
                10
            );
        } catch (MalformedAddressException $e) {
            return [];
        }

        if (!count($result) > 0) {
            return [];
        }

        $pickupPoints = [];
        foreach ($result as $parcelShop) {
            [$lng, $lat] = explode(',', $parcelShop->getCoordinate());
            $pickupPoints[] = new PickupPoint(
                sprintf('%s.%s', $parcelShop->getNumber(), $parcelShop->getZipcode()),
                $parcelShop->getCompanyname(),
                $parcelShop->getStreetname(),
                $parcelShop->getZipcode(),
                $parcelShop->getCity(),
                $parcelShop->getCountrycode(),
                $lat,
                $lng
            );
        }

        return $pickupPoints;
    }

    /**
     * {@inheritdoc}
     */
    public function getPickupPointById(string $id): ?PickupPointInterface
    {
        $client = $this->getClient();

        [$id, $zipCode] = explode('.', $id);

        try {
            $parcelShop = $client->getParcelshop($zipCode, (int) $id);
        } catch (ParcelNotFoundException $e) {
            return null;
        }

        [$lng, $lat] = explode(',', $parcelShop->getCoordinate());
        $pickupPoint = new PickupPoint(
            sprintf('%s.%s', $parcelShop->getNumber(), $parcelShop->getZipcode()),
            $parcelShop->getCompanyname(),
            $parcelShop->getStreetname(),
            $parcelShop->getZipcode(),
            $parcelShop->getCity(),
            $parcelShop->getCountrycode(),
            $lat,
            $lng
        );

        return $pickupPoint;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        $client = new Client($this->container->getParameter('setono_sylius_pickup_point_post_nord_apikey'));

        if ($this->container->getParameter('setono_sylius_pickup_point_post_nord_mode') == self::MODE_SANDBOX) {
            $client->setUseSandbox(true);
        }

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'post_nord';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'PostNord';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->container->hasParameter('setono_sylius_pickup_point_post_nord_apikey');
    }
}
