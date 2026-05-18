<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPickupPointPlugin\Message\Command\LoadPickupPoints;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final class LoadPickupPointsHandler
{
    use ORMTrait;

    /**
     * @param class-string $pickupPointClass
     */
    public function __construct(
        private readonly ServiceRegistryInterface $providerRegistry,
        private readonly PickupPointRepositoryInterface $pickupPointRepository,
        ManagerRegistry $managerRegistry,
        private readonly string $pickupPointClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(LoadPickupPoints $message): void
    {
        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($message->getProvider());

        $pickupPoints = $provider->findAllPickupPoints();

        $manager = $this->getManager($this->pickupPointClass);

        $i = 1;

        foreach ($pickupPoints as $pickupPoint) {
            $pickupPointCode = $pickupPoint->getCode();
            Assert::notNull($pickupPointCode);

            $localPickupPoint = $this->pickupPointRepository->findOneByCode($pickupPointCode);

            // if it's found, we will update the properties, else we will just persist this object
            if (null === $localPickupPoint) {
                $manager->persist($pickupPoint);
            } else {
                $localPickupPoint->setName($pickupPoint->getName());
                $localPickupPoint->setAddress($pickupPoint->getAddress());
                $localPickupPoint->setZipCode($pickupPoint->getZipCode());
                $localPickupPoint->setCity($pickupPoint->getCity());
                $localPickupPoint->setCountry($pickupPoint->getCountry());
                $localPickupPoint->setLatitude($pickupPoint->getLatitude());
                $localPickupPoint->setLongitude($pickupPoint->getLongitude());
            }

            if ($i % 50 === 0) {
                $manager->flush();
                $manager->clear();
                $manager = $this->getManager($this->pickupPointClass);
            }

            ++$i;
        }

        $manager->flush();
        $manager->clear();
    }
}
