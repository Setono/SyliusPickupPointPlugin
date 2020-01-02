<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PickupPointIdChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_name' => 'location',
            'choice_value' => 'id',
        ]);
    }

    public function getParent(): string
    {
        return PickupPointChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'setono_sylius_pickup_point_id_choice';
    }
}
