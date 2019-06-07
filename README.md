# Sylius Pickup Point Plugin

[![Latest Version][ico-version]][link-packagist]
[![Latest Unstable Version][ico-unstable-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]

Add a `<select>` that containts pickup points to your select shipping checkout step.

**Supported providers**
- DAO
- GLS
- PostNord

## Installation


### Step 1: Install and enable plugin

Open a command console, enter your project directory and execute the following command to download the latest stable version of this plugin:

```bash
$ composer require setono/sylius-pickup-point-plugin
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Add bundle to your `config/bundles.php`:

```php
<?php
# config/bundles.php

return [
    // ...
    Setono\SyliusPickupPointPlugin\SetonoSyliusPickupPointPlugin::class => ['all' => true],
    // ...
];

```

### Step 2: Import routing and configs

#### Import routing
 
````yaml
# config/routes/setono_sylius_pickup_point_plugin.yaml
setono_sylius_pickup_point_plugin:
    resource: "@SetonoSyliusPickupPointPlugin/Resources/config/routing.yaml"
````

#### Import application config

````yaml
# config/packages/_sylius.yaml
imports:
    - { resource: "@SetonoSyliusPickupPointPlugin/Resources/config/app/config.yaml" }    
````

#### (Optional) Import fixtures to play in your app

````yaml
# config/packages/_sylius.yaml
imports:
    - { resource: "@SetonoSyliusPickupPointPlugin/Resources/config/app/fixtures.yaml" }    
````

### Step 3: Update templates

Add the following to the admin template `SyliusAdminBundle/ShippingMethod/_form.html.twig`

```twig
{{ form_row(form.pickupPointProvider) }}
```

See an example [here](tests/Application/templates/bundles/SyliusAdminBundle/ShippingMethod/_form.html.twig).

Next add the following to the shop template `SyliusShopBundle/Checkout/SelectShipping/_shipment.html.twig`

```twig
{% form_theme form.pickupPointId '@SetonoSyliusPickupPointPlugin/Form/theme.html.twig' %}

{{ form_row(form.pickupPointId) }}
```

See an example [here](tests/Application/templates/bundles/SyliusShopBundle/Checkout/SelectShipping/_shipment.html.twig).

Next add the following to the shop template `SyliusShopBundle/Common/Order/_shipments.html.twig`
after shipment method header:

```twig
{% include "@SetonoSyliusPickupPointPlugin/Shop/Label/Shipment/pickupPoint.html.twig" %}
```

See an example [here](tests/Application/templates/bundles/SyliusShopBundle/Common/Order/_shipments.html.twig).

### Step 4: Customize resources

**Shipment resource**

If you haven't extended the shipment resource yet, here is what it should look like:

```php
<?php
// src/Entity/Shipment.php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPickupPointPlugin\Model\PickupPointAwareTrait;
use Setono\SyliusPickupPointPlugin\Model\ShipmentInterface;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sylius_shipment")
 */
class Shipment extends BaseShipment implements ShipmentInterface
{
    use PickupPointAwareTrait;
}
```

**Shipping method resource**

If you haven't extended the shipping method resource yet, here is what it should look like:

```php
<?php
// src/Entity/ShippingMethod.php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareTrait;
use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sylius_shipping_method")
 */
class ShippingMethod extends BaseShippingMethod implements ShippingMethodInterface
{
    use PickupPointProviderAwareTrait;
}

```

You can read about extending resources [here](https://docs.sylius.com/en/latest/customization/model.html).

**Update shipping resources config**

Next you need to tell Sylius that you will use your own extended resources:

```yaml
# config/packages/_sylius.yaml

sylius_shipping:
    resources:
        shipment:
            classes:
                model: App\Entity\Shipment
        shipping_method:
            classes:
                model: App\Entity\ShippingMethod
```

[ico-version]: https://poser.pugx.org/setono/sylius-pickup-point-plugin/v/stable
[ico-unstable-version]: https://poser.pugx.org/setono/sylius-pickup-point-plugin/v/unstable
[ico-license]: https://poser.pugx.org/setono/sylius-pickup-point-plugin/license
[ico-travis]: https://travis-ci.com/Setono/SyliusPickupPointPlugin.svg?branch=master
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Setono/SyliusPickupPointPlugin.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/setono/sylius-pickup-point-plugin
[link-travis]: https://travis-ci.com/Setono/SyliusPickupPointPlugin
[link-code-quality]: https://scrutinizer-ci.com/g/Setono/SyliusPickupPointPlugin
