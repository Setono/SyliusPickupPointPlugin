# Customizing the checkout pickup-point chooser (JavaScript)

The shop checkout pickup-point chooser is a framework-free ES module
(`public/js/setono-pickup-point.js`, loaded as `<script type="module">`). Once the shipping page is on screen it
fetches the pickup points for the current cart, then builds the UI by **cloning the `<template>` elements** from
`templates/shop/checkout/_pickup_point_templates.html.twig`.

You can customize it at four levels, from easiest to most powerful — reach for the lowest one that does the job.

## 1. Restyle the markup — override the Twig templates (no JavaScript)

The chooser's entire markup lives in `_pickup_point_templates.html.twig` as `<template>`s. Copy it into your app
at `templates/bundles/SetonoSyliusPickupPointPlugin/shop/checkout/_pickup_point_templates.html.twig` and change
the classes/structure however you like — add a map, show opening hours from a point's `metadata`, swap icons.
The JavaScript only reads these hooks, so keep them on the right elements:

- `[data-slot="name|address|label|current-name"]` — text is written into them.
- `[data-role="body|list|required|header|radio|badge"]` — structural anchors / toggled elements.
- `[data-action="change|keep"]` — buttons the script wires up.
- `[data-state="loading|empty|error"]` — the message spans.

The five templates are `#setono-pickup-point-section`, `-summary`, `-list`, `-row` and `-message`.

## 2. Toggle behaviour — `window.setonoSyliusPickupPointConfig`

Set a global config object before the module boots:

```html
<script>
    window.setonoSyliusPickupPointConfig = {
        autoSelectNearest: false,        // don't pre-select the nearest point; show the list instead
        summaryAddressSeparator: ', ',   // separator in the compact summary
        listAddressSeparator: ' — ',     // separator in the list rows
    };
</script>
```

## 3. React to the chooser — DOM events

The chooser dispatches bubbling `CustomEvent`s on the field element (they bubble up to `document`):

| Event | `detail` | Notes |
| --- | --- | --- |
| `setono:pickup-points:loaded` | `{ chooser, field, methodCode, points }` | points fetched for the selected method |
| `setono:pickup-points:error` | `{ chooser, field, error }` | the fetch failed |
| `setono:pickup-point:selected` | `{ chooser, field, point, token, source }` | a point was chosen (`source`: `auto` / `list` / `keep`); **cancelable** |

```js
document.addEventListener('setono:pickup-point:selected', (event) => {
    analytics.track('pickup_point_selected', event.detail.point);
    // event.preventDefault() vetoes writing the token (e.g. to block auto-select)
});
```

## 4. Change behaviour — subclass `PickupPointChooser`

Register a subclass on `window.SetonoSyliusPickupPointChooser`; the boot instantiates it instead of the default.
Override one of the seams: `renderRow`, `renderSummary`, `renderList`, `formatAddress`, `shouldAutoSelect`,
`fetchPoints`.

```html
<script type="module">
    import { PickupPointChooser } from '/bundles/setonosyliuspickuppointplugin/js/setono-pickup-point.js';

    window.SetonoSyliusPickupPointChooser = class extends PickupPointChooser {
        renderRow(point, currentIdentity) {
            const node = super.renderRow(point, currentIdentity);
            // ... tweak the cloned row node ...
            return node;
        }
    };
</script>
```

Because module scripts are deferred and `DOMContentLoaded` waits for them, your registration runs before the
chooser's boot reads the global — no ordering tricks needed.
