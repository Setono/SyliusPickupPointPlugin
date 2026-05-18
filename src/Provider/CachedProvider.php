<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Generator;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use function sprintf;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Webmozart\Assert\Assert;

final class CachedProvider extends Provider
{
    public function __construct(private readonly CacheItemPoolInterface $cacheItemPool, private readonly ProviderInterface $provider)
    {
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        $orderCacheKey = $this->buildOrderCacheKey($order);
        if (!$this->cacheItemPool->hasItem($orderCacheKey)) {
            $pickupPoints = $this->provider->findPickupPoints($order);

            if ($pickupPoints instanceof Generator) {
                $pickupPoints = iterator_to_array($pickupPoints);
            }

            $pickupPointsCacheItem = $this->cacheItemPool->getItem($orderCacheKey);
            $pickupPointsCacheItem->set($pickupPoints);
            $this->cacheItemPool->save($pickupPointsCacheItem);

            // Store separate PickupPoints to retrieve at findOnePickupPointById
            /** @var PickupPointInterface $pickupPoint */
            foreach ($pickupPoints as $pickupPoint) {
                $pickupPointCacheKey = $this->buildPickupPointIdCacheKey($pickupPoint->getCode());
                $pickupPointCacheItem = $this->cacheItemPool->getItem($pickupPointCacheKey);
                $pickupPointCacheItem->set($pickupPoint);
                $this->cacheItemPool->save($pickupPointCacheItem);
            }
        }

        /** @var PickupPointInterface[] $pickupPoints */
        $pickupPoints = $this->cacheItemPool->getItem($orderCacheKey)->get();

        return $pickupPoints;
    }

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface
    {
        $pickupPointCacheKey = $this->buildPickupPointIdCacheKey($code);
        if (!$this->cacheItemPool->hasItem($pickupPointCacheKey)) {
            $pickupPoint = $this->provider->findPickupPoint($code);
            if (null === $pickupPoint) {
                // Do not cache PickupPoint that wasn't found
                return null;
            }

            $pickupPointCacheItem = $this->cacheItemPool->getItem($pickupPointCacheKey);
            $pickupPointCacheItem->set($pickupPoint);
            $this->cacheItemPool->save($pickupPointCacheItem);
        }

        /** @var PickupPointInterface $pickupPoint */
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
                $order->getNumber(),
            ));
        }

        $countryCode = $shippingAddress->getCountryCode();
        Assert::notNull($countryCode);

        $postCode = $shippingAddress->getPostcode();
        Assert::notNull($postCode);

        $street = $shippingAddress->getStreet();
        Assert::notNull($street);

        $slugger = new AsciiSlugger();

        // As far as DAO/Gls/PostNord using only these 3 fields to
        // search for pickup points, we should build cache key based on them only
        return sprintf(
            '%s-%s-%s-%s',
            $this->getCode(),
            $slugger->slug($countryCode)->toString(),
            $slugger->slug($postCode)->toString(),
            $slugger->slug($street)->toString(),
        );
    }

    private function buildPickupPointIdCacheKey(PickupPointCode $id): string
    {
        return $id->getValue();
    }
}
