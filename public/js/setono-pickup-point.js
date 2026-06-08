/**
 * Pickup-point chooser — a framework-free ES module (no build step, no Stimulus).
 *
 * The shipping page renders without any provider calls. Once it is on screen this module fetches — once per
 * visit, asynchronously — every pickup-capable method's points for the current cart from a single JSON
 * endpoint (window.setonoSyliusPickupPointUrl); a slow or down provider therefore degrades gracefully
 * instead of blocking checkout.
 *
 * The chooser is built by CLONING server-rendered <template> elements (the `_pickup_point_templates.html.twig`
 * partial) and filling their [data-slot]/[data-role]/[data-action] hooks — so the markup, its translations and
 * its styling all live in Twig, and a consumer can restyle it by overriding that partial with no JavaScript.
 * It has two states: a compact summary of the chosen point (with a "Change" button) and an expandable list of
 * points (with a "Selected" badge on the current one and a "Keep this" shortcut). Selecting a point writes its
 * opaque token into the hidden input, which the server decodes on submit (no re-fetch). If a template is
 * absent the chooser renders nothing rather than building markup of its own.
 *
 * Extension points for plugin users:
 *   - subclass {@link PickupPointChooser} and register it on `window.SetonoSyliusPickupPointChooser` to override
 *     a seam (renderRow, formatAddress, fetchPoints, shouldAutoSelect, …) — the boot uses that global if set;
 *   - listen for the bubbling CustomEvents `setono:pickup-points:loaded` / `:error` and `setono:pickup-point:selected`
 *     (the last is cancelable: preventDefault() vetoes writing the token);
 *   - set `window.setonoSyliusPickupPointConfig` to tweak behaviour, e.g. `{ autoSelectNearest: false }`.
 *
 * Sylius' shop navigates with Turbo (checkout steps are AJAX body swaps), so the boot is bound to both
 * DOMContentLoaded and turbo:load; a per-field flag keeps it from running twice on the same DOM. Loaded as
 * `<script type="module">`.
 */

const SECTION_ATTR = 'data-setono-pickup-section';

const DEFAULTS = {
    autoSelectNearest: true,
    summaryAddressSeparator: ' · ',
    listAddressSeparator: ', ',
};

// The token is base64url(JSON of the whole point); decoding it lets the compact summary show the chosen
// point's details without matching it back to a (possibly regenerated) fetched point.
export function decodeToken(token) {
    try {
        let base64 = String(token).replace(/-/g, '+').replace(/_/g, '/');
        while (0 !== base64.length % 4) {
            base64 += '=';
        }
        const bytes = Uint8Array.from(atob(base64), function (character) {
            return character.charCodeAt(0);
        });

        return JSON.parse(new TextDecoder().decode(bytes));
    } catch (error) {
        return null;
    }
}

// A point's identity is provider + id — matching the server-side PickupPointIdentifier, where country is not
// part of the identity but travels in the identifier's metadata (it is what re-resolves a point, not what names
// it). It is NOT the full token: a provider may return slightly different details for the same point between
// calls (and the faker provider regenerates them), so a persisted selection must still match its list entry on
// reload by identity rather than byte-equality.
export function identity(token) {
    const point = decodeToken(token);

    return null === point ? null : [point.provider, point.id].join(' ');
}

export function addressLine(point, separator) {
    const locality = [point.zipCode, point.city].filter(Boolean).join(' ');

    return [point.address, locality].filter(Boolean).join(separator);
}

function shipmentIndex(name) {
    const match = /\[shipments]\[(\d+)]/.exec(name || '');

    return match ? match[1] : '0';
}

function methodRadioSelector(index) {
    return 'input[type="radio"][name*="[shipments][' + index + '][method]"]';
}

// Clones the first element of a server-rendered <template>, or returns null when it is absent. Callers treat
// null as "nothing to render" so the chooser degrades gracefully if the templates partial was not rendered.
function cloneTemplate(id) {
    const template = document.getElementById(id);
    if (template instanceof HTMLTemplateElement) {
        const node = template.content.firstElementChild;
        if (null !== node) {
            return node.cloneNode(true);
        }
    }

    return null;
}

// One fetch per visit, shared by every shipment's chooser. Reset by the boot so a re-entered checkout step
// re-fetches (the points depend on the cart's shipping address, which may have changed).
let pointsPromise = null;

function loadPoints(url) {
    if (null === pointsPromise) {
        pointsPromise = fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed: ' + response.status);
                }

                return response.json();
            });
    }

    return pointsPromise;
}

export class PickupPointChooser {
    constructor(field, options) {
        this.field = field;
        this.input = field.querySelector('[data-pickup-point-input]');
        this.index = shipmentIndex(this.input ? this.input.getAttribute('name') : '');
        this.options = Object.assign(
            {},
            DEFAULTS,
            { url: window.setonoSyliusPickupPointUrl },
            window.setonoSyliusPickupPointConfig || {},
            options || {},
        );
        this.mode = 'summary';
        // null = loading, false = failed, object = points keyed by shipping method code.
        this.data = null;
    }

    start() {
        if (null === this.input) {
            return;
        }

        const self = this;
        document.querySelectorAll(methodRadioSelector(this.index)).forEach(function (radio) {
            radio.addEventListener('change', function () {
                self.input.value = '';
                self.mode = 'summary';
                self.render();
            });
        });

        this.render();
    }

    fetchPoints() {
        return loadPoints(this.options.url);
    }

    setData(data) {
        this.data = data;

        const method = this.currentMethod();
        const methodCode = null !== method ? method.value : null;
        if (false === data) {
            this.dispatch('setono:pickup-points:error', { error: null });
        } else {
            const points = (null !== methodCode && data) ? (data[methodCode] || []) : [];
            this.dispatch('setono:pickup-points:loaded', { methodCode: methodCode, points: points });
        }

        this.render();
    }

    currentMethod() {
        return document.querySelector(methodRadioSelector(this.index) + ':checked');
    }

    pointsFor(method) {
        if (this.data && false !== this.data) {
            return this.data[method.value] || [];
        }

        return null;
    }

    removeSection() {
        document.querySelectorAll('[' + SECTION_ATTR + '="' + this.index + '"]').forEach(function (section) {
            section.remove();
        });
    }

    shouldAutoSelect(points) {
        // Index of the point to pre-select (providers return them ordered by distance), or -1 to skip.
        return this.options.autoSelectNearest && Array.isArray(points) && points.length > 0 ? 0 : -1;
    }

    formatAddress(point, separator) {
        return addressLine(point, separator);
    }

    render() {
        this.removeSection();

        const method = this.currentMethod();
        if (null === method || !method.hasAttribute('data-pickup-point-provider')) {
            return;
        }

        const card = method.closest('.card');
        if (null === card) {
            return;
        }

        const section = cloneTemplate('setono-pickup-point-section');
        if (null === section) {
            // The templates partial is not on the page — render nothing rather than fabricate markup.
            return;
        }
        section.setAttribute(SECTION_ATTR, this.index);

        const points = this.pointsFor(method);

        // Pre-select the nearest point so the shopper lands on the most likely choice in the compact summary
        // instead of an open list; they can still open the list to pick another.
        if ('' === this.input.value && Array.isArray(points)) {
            const autoIndex = this.shouldAutoSelect(points);
            if (autoIndex >= 0 && autoIndex < points.length) {
                this.selectPoint(points[autoIndex], 'auto', false);
            }
        }

        const summaryMode = 'summary' === this.mode && '' !== this.input.value;

        const required = section.querySelector('[data-role="required"]');
        if (null !== required) {
            required.hidden = !(!summaryMode && Array.isArray(points) && points.length > 0);
        }

        let content;
        if (summaryMode) {
            content = this.renderSummary();
        } else if (null === this.data) {
            content = this.renderMessage('loading');
        } else if (false === this.data) {
            content = this.renderMessage('error');
        } else if (!Array.isArray(points) || 0 === points.length) {
            content = this.renderMessage('empty');
        } else {
            content = this.renderList(points);
        }

        if (null !== content) {
            const body = section.querySelector('[data-role="body"]') || section;
            body.appendChild(content);
        }

        card.appendChild(section);
    }

    renderSummary() {
        const node = cloneTemplate('setono-pickup-point-summary');
        if (null === node) {
            return null;
        }

        const point = decodeToken(this.input.value) || {};
        const name = node.querySelector('[data-slot="name"]');
        if (null !== name) {
            name.textContent = point.name || '';
        }
        const address = node.querySelector('[data-slot="address"]');
        if (null !== address) {
            address.textContent = this.formatAddress(point, this.options.summaryAddressSeparator);
        }

        const change = node.querySelector('[data-action="change"]');
        if (null !== change) {
            const self = this;
            change.addEventListener('click', function () {
                self.mode = 'list';
                self.render();
            });
        }

        return node;
    }

    renderList(points) {
        const node = cloneTemplate('setono-pickup-point-list');
        if (null === node) {
            return null;
        }

        const current = this.input.value ? decodeToken(this.input.value) : null;
        const currentIdentity = this.input.value ? identity(this.input.value) : null;

        const header = node.querySelector('[data-role="header"]');
        if (null !== header) {
            if (null !== current) {
                header.hidden = false;
                const currentName = header.querySelector('[data-slot="current-name"]');
                if (null !== currentName) {
                    currentName.textContent = current.name || '';
                }
                const keep = header.querySelector('[data-action="keep"]');
                if (null !== keep) {
                    const self = this;
                    keep.addEventListener('click', function () {
                        self.mode = 'summary';
                        self.render();
                    });
                }
            } else {
                header.hidden = true;
            }
        }

        const list = node.querySelector('[data-role="list"]') || node;
        const self = this;
        points.forEach(function (point) {
            const row = self.renderRow(point, currentIdentity);
            if (null !== row) {
                list.appendChild(row);
            }
        });

        return node;
    }

    renderRow(point, currentIdentity) {
        const node = cloneTemplate('setono-pickup-point-row');
        if (null === node) {
            return null;
        }

        if (undefined === currentIdentity) {
            currentIdentity = this.input.value ? identity(this.input.value) : null;
        }
        const isSelected = null !== currentIdentity && identity(point.value) === currentIdentity;
        if (isSelected) {
            node.classList.add('bg-primary-subtle');
        }

        const radio = node.querySelector('[data-role="radio"]');
        if (null !== radio) {
            radio.name = 'setono-pickup-point-choice-' + this.index;
            radio.value = point.value;
            radio.checked = isSelected;
            const self = this;
            radio.addEventListener('change', function () {
                self.selectPoint(point, 'list');
            });
        }

        const name = node.querySelector('[data-slot="name"]');
        if (null !== name) {
            name.textContent = point.name || '';
        }
        const address = node.querySelector('[data-slot="address"]');
        if (null !== address) {
            address.textContent = this.formatAddress(point, this.options.listAddressSeparator);
        }

        const badge = node.querySelector('[data-role="badge"]');
        if (null !== badge) {
            badge.hidden = !isSelected;
        }

        return node;
    }

    renderMessage(state) {
        const node = cloneTemplate('setono-pickup-point-message');
        if (null === node) {
            return null;
        }

        node.querySelectorAll('[data-state]').forEach(function (span) {
            span.hidden = span.getAttribute('data-state') !== state;
        });

        return node;
    }

    // Writes the chosen point's token into the hidden input and switches to the summary, dispatching a
    // cancelable `selected` event first so a listener can veto it. Used by both list selection and auto-select.
    selectPoint(point, source, rerender) {
        const token = point.value;
        const event = this.dispatch(
            'setono:pickup-point:selected',
            { point: decodeToken(token), token: token, source: source },
            true,
        );
        if (event.defaultPrevented) {
            this.mode = 'list';
            if (false !== rerender) {
                this.render();
            }

            return;
        }

        this.input.value = token;
        this.mode = 'summary';
        if (false !== rerender) {
            this.render();
        }
    }

    dispatch(name, detail, cancelable) {
        const event = new CustomEvent(name, {
            bubbles: true,
            cancelable: Boolean(cancelable),
            detail: Object.assign({ chooser: this, field: this.field }, detail),
        });
        this.field.dispatchEvent(event);

        return event;
    }
}

function boot() {
    const url = window.setonoSyliusPickupPointUrl;
    if (!url) {
        return;
    }

    const Ctor = window.SetonoSyliusPickupPointChooser || PickupPointChooser;

    const choosers = [];
    document.querySelectorAll('[data-pickup-point-field]').forEach(function (field) {
        if (field.hasAttribute('data-pickup-point-ready')) {
            return;
        }
        field.setAttribute('data-pickup-point-ready', '');
        choosers.push(new Ctor(field, {}));
    });
    if (0 === choosers.length) {
        return;
    }

    // Fresh fetch for this visit (see loadPoints) — one request shared by all of this page's shipments.
    pointsPromise = null;

    choosers.forEach(function (chooser) {
        chooser.start();
        chooser.fetchPoints()
            .then(function (data) {
                chooser.setData(data);
            })
            .catch(function () {
                chooser.setData(false);
            });
    });
}

// Test-only: resets the per-visit fetch memo so specs can re-run the boot deterministically.
export function __resetBootForTests() {
    pointsPromise = null;
}

document.addEventListener('DOMContentLoaded', boot);
document.addEventListener('turbo:load', boot);

// The module may be evaluated after the DOM is already interactive.
if ('loading' !== document.readyState) {
    boot();
}
