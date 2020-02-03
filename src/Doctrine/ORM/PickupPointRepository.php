<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Doctrine\ORM;

use Doctrine\ORM\NonUniqueResultException;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

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

    public function findByOrder(OrderInterface $order, string $provider): array
    {
        $shippingAddress = $order->getShippingAddress();
        Assert::notNull($shippingAddress);

        $countryCode = $shippingAddress->getCountryCode();
        Assert::notNull($countryCode);

        return $this->createQueryBuilder('o')
            ->andWhere('o.code.provider = :provider')
            ->andWhere('o.code.country = :country')
            ->setParameters([
                'provider' => $provider,
                'country' => $countryCode,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
