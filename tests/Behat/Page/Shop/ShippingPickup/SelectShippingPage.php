<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Page\Shop\ShippingPickup;

use Sylius\Behat\Page\Shop\Checkout\SelectShippingPage as BaseSelectShippingPage;
use Webmozart\Assert\Assert;

final class SelectShippingPage extends BaseSelectShippingPage implements SelectShippingPageInterface
{
    public function selectPickupPointShippingMethod(string $shippingMethod): void
    {
        Assert::true($this->getDocument()->waitFor(15, function () use ($shippingMethod) {
            return $this->hasElement('shipping_method_select', [
                '%shipping_method%' => $shippingMethod,
            ]);
        }), sprintf('Unable to find shipping method %s', $shippingMethod));

        // @todo Fix proper way?
        sleep(1); // Workaround to render shipping method

        $this->selectShippingMethod($shippingMethod);

        Assert::true($this->getDocument()->waitFor(15, function () {
            return $this->hasElement('pickup_point_field');
        }), "Pickup point field expected to be visible, but it isn't");
    }

    public function chooseFirstShippingPointFromRadio(): void
    {
        $expectedFirstOptionValue = 'faker---0---US';

        Assert::true($this->hasElement('pickup_point_field'));

        Assert::true($this->getDocument()->waitFor(15, function () {
            return $this->hasElement('pickup_point_radio');
        }), 'Pickup point radio not visible');

        $this->getElement('pickup_point_radio_item', [
            '%value%' => $expectedFirstOptionValue,
        ])->click();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'pickup_point_field' => '.setono-sylius-pickup-point-field:not([style*="display: none"])',
            'pickup_point_radio' => '.setono-sylius-pickup-point-field-choices:not([style*="display: none"])',
            'pickup_point_radio_item' => '.setono-sylius-pickup-point-field-choice-field[value="%value%"]',
        ]);
    }
}
