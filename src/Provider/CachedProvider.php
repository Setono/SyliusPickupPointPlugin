<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Behat\Transliterator\Transliterator;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use function Safe\sprintf;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPoint;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPointCode;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class CachedProvider extends Provider
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

    /**
     * @throws InvalidArgumentException
     *
     * @return PickupPoint[]
     */
    public function findPickupPoints(OrderInterface $order): iterable
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

        /** @var PickupPoint[] $pickupPoints */
        $pickupPoints = $this->cacheItemPool->getItem($orderCacheKey)->get();

        return $pickupPoints;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findPickupPoint(PickupPointCode $id): ?PickupPoint
    {
        $pickupPointCacheKey = $this->buildPickupPointIdCacheKey($id);
        if (!$this->cacheItemPool->hasItem($pickupPointCacheKey)) {
            $pickupPoint = $this->provider->findPickupPoint($id);
            if (null === $pickupPoint) {
                // Do not cache PickupPoint that wasn't found
                return null;
            }

            $pickupPointCacheItem = $this->cacheItemPool->getItem($pickupPointCacheKey);
            $pickupPointCacheItem->set($pickupPoint);
            $this->cacheItemPool->save($pickupPointCacheItem);
        }

        /** @var PickupPoint $pickupPoint */
        $pickupPoint = $this->cacheItemPool->getItem($pickupPointCacheKey)->get();

        return $pickupPoint;
    }

    public function findAllPickupPoints(): iterable
    {
        yield from $this->provider->findAllPickupPoints();
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
            throw new RuntimeException(sprintf(
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

    private function buildPickupPointIdCacheKey(PickupPointCode $id): string
    {
        return $id->getValue();
    }
}
