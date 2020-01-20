<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Doctrine\ORM;

use Doctrine\ORM\NonUniqueResultException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class PickupPointRepository extends EntityRepository implements PickupPointRepositoryInterface
{
    /**
     * @throws NonUniqueResultException
     */
    public function findOneByCode(PickupPointCode $code): ?PickupPointInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.code.id = :codeId')
            ->andWhere('o.code.provider = :codeProvider')
            ->andWhere('o.code.country = :codeCountry')
            ->setParameters([
                'codeId' => $code->getIdPart(),
                'codeProvider' => $code->getProviderPart(),
                'codeCountry' => $code->getCountryPart(),
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
