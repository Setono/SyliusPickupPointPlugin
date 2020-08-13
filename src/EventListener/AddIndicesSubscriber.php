<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;

final class AddIndicesSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();

        if (!is_subclass_of($metadata->name, PickupPointInterface::class, true)) {
            return;
        }

        $tableConfig = [
            'uniqueConstraints' => [
                'unique_code_idx' => [
                    'columns' => [
                        'code_id',
                        'code_provider',
                        'code_country',
                    ],
                ],
            ],
        ];

        $metadata->table = array_merge_recursive($tableConfig, $metadata->table);
    }
}
