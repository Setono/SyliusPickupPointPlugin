# Sylius Pickup Point Plugin

[![Latest Version][ico-version]][link-packagist]
[![Latest Unstable Version][ico-unstable-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Quality Score][ico-code-quality]][link-code-quality]

Add a `<select>` that contains pickup points to your select shipping checkout step.

- [Screenshots](#screenshots)
- [Installation](#installation)

![List of pickup points](docs/pickup-points.png)

**Supported providers**
- DAO
- GLS
- PostNord
- Fake provider (for development/playing purposes)

## Screenshots

### Shop

This is the shipping method step in the checkout process where you can choose a pickup point.

![Screenshot showing checkout select shipping step with pickup points available](docs/images/shop-checkout-select-shipping-pickup-point.png)

On the complete order step in checkout you can see the pickup point you have chosen.

![Screenshot showing checkout complete step with pickup point address](docs/images/shop-checkout-complete-shipping-pickup-point.png)

### Admin

On the order you can see what pickup point the customer has chosen.

![Screenshot showing admin order shipping page with pickup point address](docs/images/admin-order-shipping-pickup-point.png)

When you edit shipping method you can associate a pickup point provider to that shipping method.

![Screenshot showing admin shipping method with some pickup point providers](docs/images/admin-shipping-method-pickup-point-provider.png)

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
# config/routes/setono_sylius_pickup_point.yaml
setono_sylius_pickup_point_plugin:
    resource: "@SetonoSyliusPickupPointPlugin/Resources/config/routing.yaml"
````

#### Import application config

````yaml
# config/packages/setono_sylius_pickup_point.yaml
imports:
    - { resource: "@SetonoSyliusPickupPointPlugin/Resources/config/app/config.yaml" }    
````

#### (Optional) Import fixtures to play in your app

````yaml
# config/packages/setono_sylius_pickup_point.yaml
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

Next add the following to the admin template `SyliusAdminBundle/Order/Show/_shipment.html.twig`
after shipment header:

```twig
{% include "@SetonoSyliusPickupPointPlugin/Shop/Label/Shipment/pickupPoint.html.twig" %}
```

See an example [here](tests/Application/templates/bundles/SyliusAdminBundle/Order/Show/_shipment.html.twig).

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

### Step 5: Configure plugin

**Enable desired providers**

Note that:
- `faker` provider will not work on prod environment
- `gls` provider require `setono/gls-webservice-bundle` to be installed
- `dao` provider require `setono/dao-bundle` to be installed
- `post_nord` provider require `setono/post-nord-bundle` to be installed

```yaml
# config/packages/setono_sylius_pickup_point.yaml
setono_sylius_pickup_point:
    providers:
        faker: true
        gls: true
        post_nord: true
        dao: true
```

**If you want to use cache**

Cache disabled by default. To enable it, make next configuration:

```yaml
# config/packages/setono_sylius_pickup_point.yaml
framework:
    cache:
        pools:
            setono_sylius_pickup_point.provider_cache_pool:
                adapter: cache.app

setono_sylius_pickup_point:
    cache:
        enabled: true
        pool: setono_sylius_pickup_point.provider_cache_pool
```

### Step 6: Update database schema

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate 
```

### Step 7: Update validation groups

Add `checkout_select_shipping` to `sylius.form.type.checkout_select_shipping.validation_groups`:

```yaml
# config/packages/_sylius.yaml

parameters:
    sylius.form.type.checkout_select_shipping.validation_groups: ['sylius', 'checkout_select_shipping']
```

# Step 8: Install assets

```bash
bin/console sylius:install:assets  
bin/console sylius:theme:assets:install
```

## Troubleshooting

* At `/en_US/checkout/select-shipping` step you see `No results found` at `Pickup point id` field.
  
  - Check your browser's developer console and make sure JS scripts loaded correctly.
  Also make sure `setono-pickup-point.js` compiled (read as you not forgot to run `sylius:install:assets`).

  - Make sure content of plugin's `src/Resources/views/_javascripts.html.twig` actually rendered. 
  If not - probably, you erased `{{ sonata_block_render_event('sylius.shop.layout.javascripts') }}` 
  from your custom `layout.html.twig`.
  
  Also, make sure `{{ sonata_block_render_event('sylius.admin.layout.javascripts') }}` in place at 
  your admin's `layout.html.twig` if it was customized.
  
  - If you're using themes, make sure you executed `sylius:theme:assets:install` after plugin installation.

* `The service "setono_sylius_pickup_point.registry.provider" has a dependency on a non-existent service "setono_post_nord.http_client".`

  You should specify `setono_post_nord.http_client` configuration or define `Buzz\Client\BuzzClientInterface` service to use as default http client.
  See https://github.com/Setono/PostNordBundle/issues/1
  
  You should add [config/packages/buzz.yaml](tests/Application/config/packages/buzz.yaml) and 
  [config/packages/nyholm_psr7.yaml](tests/Application/config/packages/nyholm_psr7.yaml) configs.

* You're facing `Pickup point cannot be blank.` validation error at `/checkout/address` step at your application

  Make sure you're followed instructions from `Installation step 7`. 

[ico-version]: https://poser.pugx.org/setono/sylius-pickup-point-plugin/v/stable
[ico-unstable-version]: https://poser.pugx.org/setono/sylius-pickup-point-plugin/v/unstable
[ico-license]: https://poser.pugx.org/setono/sylius-pickup-point-plugin/license
[ico-github-actions]: https://github.com/Setono/SyliusPickupPointPlugin/workflows/build/badge.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Setono/SyliusPickupPointPlugin.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/setono/sylius-pickup-point-plugin
[link-github-actions]: https://github.com/Setono/SyliusPickupPointPlugin/actions
[link-code-quality]: https://scrutinizer-ci.com/g/Setono/SyliusPickupPointPlugin
