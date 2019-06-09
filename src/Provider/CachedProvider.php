<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Behat\Transliterator\Transliterator;
use Psr\Cache\CacheItemPoolInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class CachedProvider implements ProviderInterface
{
    /** @var CacheItemPoolInterface */
    private $cacheItemPool;

    /** @var ProviderInterface */
    private $provider;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        ProviderInterface $provider
    ) {
        $this->cacheItemPool = $cacheItemPool;
        $this->provider = $provider;
    }

    public function findPickupPoints(OrderInterface $order): array
    {
        $orderCacheKey = $this->buildOrderCacheKey($order);
        if (!$this->cacheItemPool->hasItem($orderCacheKey)) {
            $pickupPoints = $this->provider->findPickupPoints($order);

            $pickupPointsCacheItem = $this->cacheItemPool->getItem($orderCacheKey);
            $pickupPointsCacheItem->set($pickupPoints);
            $this->cacheItemPool->save($pickupPointsCacheItem);

            // Store separate PickupPoints to retrieve at findOnePickupPointById
            foreach ($pickupPoints as $pickupPoint) {
                $pickupPointCacheKey = $this->buildPickupPointIdCacheKey($pickupPoint->getId());
                $pickupPointCacheItem = $this->cacheItemPool->getItem($pickupPointCacheKey);
                $pickupPointCacheItem->set($pickupPoint);
                $this->cacheItemPool->save($pickupPointCacheItem);
            }
        }

        return $this->cacheItemPool->getItem($orderCacheKey)->get();
    }

    public function findOnePickupPointById(string $id): ?PickupPointInterface
    {
        $pickupPointCacheKey = $this->buildPickupPointIdCacheKey($id);
        if (!$this->cacheItemPool->hasItem($pickupPointCacheKey)) {
            $pickupPointCacheItem = $this->cacheItemPool->getItem($pickupPointCacheKey);
            $pickupPointCacheItem->set(
                $this->provider->findOnePickupPointById($id)
            );
            $this->cacheItemPool->save($pickupPointCacheItem);
        }

        return $this->cacheItemPool->getItem($pickupPointCacheKey)->get();
    }

    public function getCode(): string
    {
        return $this->provider->getCode();
    }

    public function getName(): string
    {
        return $this->provider->getName();
    }

    private function buildOrderCacheKey(OrderInterface $order): string
    {
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress instanceof AddressInterface) {
            throw new \RuntimeException(sprintf(
                'Shipping address was not found for order #%s',
                $order->getNumber()
            ));
        }

        // As far as DAO/Gls/PostNord using only these 3 fields to
        // search for pickup points, we should build cache key based on them only
        return sprintf(
            '%s-%s-%s-%s',
            $this->getCode(),
            Transliterator::transliterate($shippingAddress->getCountryCode()),
            Transliterator::transliterate($shippingAddress->getPostcode()),
            Transliterator::transliterate($shippingAddress->getStreet())
        );
    }

    private function buildPickupPointIdCacheKey(string $id): string
    {
        return sprintf(
            '%s-%s',
            $this->getCode(),
            $id
        );
    }
}
