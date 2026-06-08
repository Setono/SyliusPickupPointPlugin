// Test fixtures for the pickup-point chooser specs.
//
// The TEMPLATES_HTML below mirrors templates/shop/checkout/_pickup_point_templates.html.twig — keep the
// [data-slot] / [data-role] / [data-action] / [data-state] hooks in sync with that partial (the real Twig
// markup, with its translations and Bootstrap classes, is covered by the Playwright/Behat layer). The chooser
// only ever reads those hooks, so the unstyled copy here is enough to exercise the JS.

import { __resetBootForTests } from '../../../../public/js/setono-pickup-point.js';

export const TEMPLATES_HTML = `
<template id="setono-pickup-point-section">
    <div class="setono-pickup-point">
        <hr>
        <div class="fw-bold mb-2"><span data-slot="label">Pickup point</span> <span class="text-danger" data-role="required" hidden>*</span></div>
        <div data-role="body"></div>
    </div>
</template>
<template id="setono-pickup-point-summary">
    <div class="border border-primary">
        <span class="setono-pickup-point__check"><svg viewBox="0 0 16 16"><path d="M0 0"/></svg></span>
        <div><div class="fw-bold" data-slot="name"></div><div data-slot="address"></div></div>
        <button type="button" data-action="change">Change</button>
    </div>
</template>
<template id="setono-pickup-point-list">
    <div>
        <div class="header" data-role="header" hidden><div>Currently: <span data-slot="current-name"></span></div><button type="button" data-action="keep">Keep this</button></div>
        <div class="list-group" data-role="list"></div>
    </div>
</template>
<template id="setono-pickup-point-row">
    <label class="list-group-item"><input type="radio" data-role="radio"><div><div class="fw-bold" data-slot="name"></div><div data-slot="address"></div></div><span class="badge" data-role="badge" hidden>Selected</span></label>
</template>
<template id="setono-pickup-point-message">
    <div><span data-state="loading">Loading…</span><span data-state="empty" hidden>None</span><span data-state="error" hidden>Error</span></div>
</template>
`;

// base64url(JSON) — mirrors the server-side PickupPointEncoder so specs build valid tokens (incl. UTF-8).
export function tokenFor(point) {
    const bytes = new TextEncoder().encode(JSON.stringify(point));
    let binary = '';
    bytes.forEach((b) => {
        binary += String.fromCharCode(b);
    });

    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

// A "fetched point" as the endpoint returns it: flat display fields + the opaque `value` token (base64url of
// the full pickup point, carrying provider/id/country/metadata used by decodeToken/identity).
export function makePoint(overrides = {}) {
    const full = Object.assign(
        { provider: 'faker', id: '0', name: 'Post office #0', address: 'Main Street 1', zipCode: '9000', city: 'Aalborg', country: 'DK', latitude: '57.0', longitude: '9.9', metadata: [] },
        overrides,
    );

    return { value: tokenFor(full), name: full.name, address: full.address, zipCode: full.zipCode, city: full.city, latitude: full.latitude, longitude: full.longitude };
}

export function makePoints(count, provider = 'faker') {
    const points = [];
    for (let i = 0; i < count; i++) {
        points.push(makePoint({ provider: provider, id: String(i), name: 'Point #' + i, address: 'Street ' + i }));
    }

    return points;
}

// Builds the minimal checkout DOM the chooser needs: the <template>s, a shipping-method radio inside a `.card`,
// and the field wrapper with the hidden input.
export function buildDom({ provider = 'faker', methodValue = 'faker', checked = true, templates = true } = {}) {
    document.body.innerHTML = '';

    if (templates) {
        const holder = document.createElement('div');
        holder.innerHTML = TEMPLATES_HTML.trim();
        Array.from(holder.children).forEach((child) => document.body.appendChild(child));
    }

    const card = document.createElement('div');
    card.className = 'card';
    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = 'sylius_shop_checkout_select_shipping[shipments][0][method]';
    radio.value = methodValue;
    if (null !== provider) {
        radio.setAttribute('data-pickup-point-provider', provider);
    }
    radio.checked = checked;
    card.appendChild(radio);
    document.body.appendChild(card);

    const field = document.createElement('div');
    field.setAttribute('data-pickup-point-field', '');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'sylius_shop_checkout_select_shipping[shipments][0][pickupPoint]';
    input.setAttribute('data-pickup-point-input', '');
    field.appendChild(input);
    document.body.appendChild(field);

    return { card, radio, field, input };
}

export function section() {
    return document.querySelector('[data-setono-pickup-section]');
}

export function rows() {
    const list = document.querySelector('[data-role="list"]');

    return list ? Array.from(list.querySelectorAll('label')) : [];
}

export function resetGlobals() {
    __resetBootForTests();
    delete window.setonoSyliusPickupPointUrl;
    delete window.setonoSyliusPickupPointConfig;
    delete window.SetonoSyliusPickupPointChooser;
    document.body.innerHTML = '';
}
