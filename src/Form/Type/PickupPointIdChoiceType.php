<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PickupPointIdChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_name' => 'full_name',
            'choice_value' => 'full_id',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return PickupPointChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'setono_sylius_pickup_point_id_choice';
    }
}
