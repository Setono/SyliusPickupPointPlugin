<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Message\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusPickupPointPlugin\Message\Command\LoadPickupPoints;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class LoadPickupPointsHandler implements MessageHandlerInterface
{
    /** @var ServiceRegistryInterface */
    private $providerRegistry;

    /** @var PickupPointRepositoryInterface */
    private $pickupPointRepository;

    /** @var FactoryInterface */
    private $pickupPointFactory;

    /** @var EntityManagerInterface */
    private $pickupPointManager;

    public function __construct(
        ServiceRegistryInterface $providerRegistry,
        PickupPointRepositoryInterface $pickupPointRepository,
        FactoryInterface $pickupPointFactory,
        EntityManagerInterface $pickupPointManager
    ) {
        $this->providerRegistry = $providerRegistry;
        $this->pickupPointRepository = $pickupPointRepository;
        $this->pickupPointFactory = $pickupPointFactory;
        $this->pickupPointManager = $pickupPointManager;
    }

    public function __invoke(LoadPickupPoints $message): void
    {
        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($message->getProvider());

        $pickupPoints = $provider->findAllPickupPoints();

        $i = 1;

        foreach ($pickupPoints as $pickupPoint) {
            $obj = $this->pickupPointRepository->findOneByProviderIdAndProviderAndCountry(
                $pickupPoint->getId()->getIdPart(),
                $pickupPoint->getId()->getProviderPart(),
                $pickupPoint->getCountry()
            );

            if (null === $obj) {
                /** @var PickupPointInterface $obj */
                $obj = $this->pickupPointFactory->createNew();
            }

            $obj->setProviderId($pickupPoint->getId()->getIdPart());
            $obj->setProvider($pickupPoint->getId()->getProviderPart());
            $obj->setName($pickupPoint->getName());
            $obj->setAddress($pickupPoint->getAddress());
            $obj->setZipCode($pickupPoint->getZipCode());
            $obj->setCity($pickupPoint->getCity());
            $obj->setCountry($pickupPoint->getCountry());
            $obj->setLatitude($pickupPoint->getLatitude());
            $obj->setLongitude($pickupPoint->getLongitude());

            $this->pickupPointManager->persist($obj);

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
