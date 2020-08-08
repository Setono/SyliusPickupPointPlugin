<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Message\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusPickupPointPlugin\Message\Command\LoadPickupPoints;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

final class LoadPickupPointsHandler implements MessageHandlerInterface
{
    /** @var ServiceRegistryInterface */
    private $providerRegistry;

    /** @var PickupPointRepositoryInterface */
    private $pickupPointRepository;

    /** @var EntityManagerInterface */
    private $pickupPointManager;

    public function __construct(
        ServiceRegistryInterface $providerRegistry,
        PickupPointRepositoryInterface $pickupPointRepository,
        EntityManagerInterface $pickupPointManager
    ) {
        $this->providerRegistry = $providerRegistry;
        $this->pickupPointRepository = $pickupPointRepository;
        $this->pickupPointManager = $pickupPointManager;
    }

    public function __invoke(LoadPickupPoints $message): void
    {
        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($message->getProvider());

        $pickupPoints = $provider->findAllPickupPoints();

        $i = 1;

        foreach ($pickupPoints as $pickupPoint) {
            $pickupPointCode = $pickupPoint->getCode();
            Assert::notNull($pickupPointCode);

            $localPickupPoint = $this->pickupPointRepository->findOneByCode($pickupPointCode);

            // if it's found, we will update the properties, else we will just persist this object
            if (null === $localPickupPoint) {
                $this->pickupPointManager->persist($pickupPoint);
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
                $this->flush();
            }

            ++$i;
        }

        $this->flush();
    }

    private function flush(): void
    {
        $this->pickupPointManager->flush();
        $this->pickupPointManager->clear();
    }
}
