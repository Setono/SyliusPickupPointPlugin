<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Type;

use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointToIdentifierTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A hidden field whose model data is a {@see \Setono\SyliusPickupPointPlugin\DTO\PickupPoint} and
 * whose view data is the opaque identifier token (bridged by the model transformer). The visible
 * chooser — the pickup-point radios — is rendered by the shop JS from the AJAX response, not by
 * Symfony's choice machinery, so this is a plain HiddenType, not a ChoiceType.
 */
final class PickupPointType extends AbstractType
{
    public function __construct(private readonly PickupPointToIdentifierTransformer $transformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Maps the entity's PickupPoint object to/from the opaque identifier token the hidden input
        // carries: transform() encodes it for rendering, reverseTransform() decodes + resolves it on submit.
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'error_bubbling' => false,
            // The field's model data is a PickupPoint object but the model transformer renders it as a
            // string token; null data_class stops Symfony expecting the view data to be an object.
            'data_class' => null,
        ]);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'setono_sylius_pickup_point';
    }
}
