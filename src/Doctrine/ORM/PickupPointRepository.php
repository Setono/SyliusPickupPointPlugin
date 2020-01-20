<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Doctrine\ORM;

use Doctrine\ORM\NonUniqueResultException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class PickupPointRepository extends EntityRepository implements PickupPointRepositoryInterface
{
    /**
     * @throws NonUniqueResultException
     */
    public function findOneByProviderIdAndProviderAndCountry(
        string $providerId,
        string $provider,
        string $country
    ): ?PickupPointInterface {
        return $this->createQueryBuilder('o')
            ->andWhere('o.providerId = :providerId')
            ->andWhere('o.provider = :provider')
            ->andWhere('o.country = :country')
            ->setParameters([
                'providerId' => $providerId,
                'provider' => $provider,
                'country' => $country,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
