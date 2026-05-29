<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PickupPointChoiceType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // On Symfony 6.4 (our lowest supported version) FormView::$vars is an untyped
        // property, so PHPStan sees it as `mixed` and rejects direct offset writes. The
        // cast normalizes it to `array` so the writes type-check on both 6.4 and 7.x; the
        // resulting "useless cast" on 7.x is silenced by the scoped ignore in phpstan.neon.
        $vars = (array) $view->vars;
        $vars['multiple'] = $options['multiple'];
        $vars['choice_name'] = $options['choice_name'];
        $vars['choice_value'] = $options['choice_value'];
        $vars['placeholder'] = $options['placeholder'];

        $view->vars = $vars;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'choice_name',
                'choice_value',
            ])
            ->setDefaults([
                'multiple' => false,
                'error_bubbling' => false,
                'placeholder' => '',
            ])
            ->setAllowedTypes('choice_name', ['string'])
            ->setAllowedTypes('choice_value', ['string'])
            ->setAllowedTypes('multiple', ['bool'])
            ->setAllowedTypes('placeholder', ['string'])
        ;
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'setono_sylius_pickup_point_choice';
    }
}
