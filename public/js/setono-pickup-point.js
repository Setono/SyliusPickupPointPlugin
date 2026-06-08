/**
 * Pickup-point chooser (no framework, no Stimulus).
 *
 * The shipping page renders without any provider calls. Once it is on screen this script fetches — once
 * per visit, asynchronously — every pickup-capable method's points for the current cart from a single
 * JSON endpoint (window.setonoSyliusPickupPointUrl); a slow or down provider therefore degrades
 * gracefully instead of blocking checkout.
 *
 * The chooser is built client-side and inserted into the selected pickup-capable shipping method's card.
 * It has two states: a compact summary of the chosen point (with a "Change" button) and an expandable
 * list of points (with a "Selected" badge on the current one and a "Keep this" shortcut). Selecting a
 * point writes its opaque token into the hidden input, which the server decodes on submit (no re-fetch).
 *
 * Sylius' shop navigates with Turbo (checkout steps are AJAX body swaps), so init is bound to both
 * DOMContentLoaded and turbo:load; a per-field flag keeps it from running twice on the same DOM.
 */
(function () {
    'use strict';

    if (window.__setonoSyliusPickupPointBooted) {
        return;
    }
    window.__setonoSyliusPickupPointBooted = true;

    const SVG_NS = 'http://www.w3.org/2000/svg';
    const SECTION_ATTR = 'data-setono-pickup-section';

    function shipmentIndex(name) {
        const match = /\[shipments]\[(\d+)]/.exec(name || '');

        return match ? match[1] : '0';
    }

    function methodRadioSelector(index) {
        return 'input[type="radio"][name*="[shipments][' + index + '][method]"]';
    }

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (undefined !== text && null !== text) {
            node.textContent = text;
        }

        return node;
    }

    // The token is base64url(JSON of the whole point); decoding it lets the compact summary show the
    // chosen point's details without matching it back to a (possibly regenerated) fetched point.
    function decodeToken(token) {
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

    function addressLine(point, separator) {
        const locality = [point.zipCode, point.city].filter(Boolean).join(' ');

        return [point.address, locality].filter(Boolean).join(separator);
    }

    // A point's identity is provider + id + country, not its full token: a provider may return slightly
    // different details for the same point between calls (and the faker provider regenerates them), so a
    // persisted selection must still match its list entry on reload by identity rather than byte-equality.
    function identity(token) {
        const point = decodeToken(token);

        return null === point ? null : [point.provider, point.id, point.country].join(' ');
    }

    function checkIcon() {
        const svg = document.createElementNS(SVG_NS, 'svg');
        svg.setAttribute('viewBox', '0 0 16 16');
        svg.setAttribute('width', '16');
        svg.setAttribute('height', '16');
        svg.setAttribute('fill', 'currentColor');

        const path = document.createElementNS(SVG_NS, 'path');
        path.setAttribute('d', 'M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z');
        svg.appendChild(path);

        return svg;
    }

    function message(field, attribute) {
        return el('div', 'setono-sylius-pickup-point-field-message text-body-secondary small py-2', field.getAttribute(attribute));
    }

    function summary(field, input, rerender) {
        const point = decodeToken(input.value) || {};

        const box = el('div', 'd-flex align-items-center gap-3 bg-body border border-primary rounded-3 p-3');

        const check = el('span', 'setono-pickup-point__check rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center');
        check.appendChild(checkIcon());
        box.appendChild(check);

        const info = el('div', 'flex-grow-1');
        info.appendChild(el('div', 'fw-bold', point.name || ''));
        info.appendChild(el('div', 'text-body-secondary small', addressLine(point, ' · ')));
        box.appendChild(info);

        const change = el('button', 'btn btn-link text-primary fw-semibold text-decoration-none p-0', field.getAttribute('data-change-text'));
        change.type = 'button';
        change.addEventListener('click', function () {
            field._mode = 'list';
            rerender();
        });
        box.appendChild(change);

        return box;
    }

    function list(field, input, points, rerender) {
        const wrapper = el('div');

        const current = input.value ? decodeToken(input.value) : null;
        const currentIdentity = input.value ? identity(input.value) : null;
        if (null !== current) {
            const head = el('div', 'd-flex justify-content-between align-items-center gap-2 mb-2');

            const label = el('div', 'small text-body-secondary');
            label.appendChild(document.createTextNode((field.getAttribute('data-currently-text') || '') + ' '));
            label.appendChild(el('span', 'fw-bold text-body', current.name || ''));
            head.appendChild(label);

            const keep = el('button', 'btn btn-sm btn-outline-primary flex-shrink-0', field.getAttribute('data-keep-text'));
            keep.type = 'button';
            keep.addEventListener('click', function () {
                field._mode = 'summary';
                rerender();
            });
            head.appendChild(keep);

            wrapper.appendChild(head);
        }

        const group = el('div', 'setono-pickup-point__list list-group');
        const groupName = 'setono-pickup-point-choice-' + shipmentIndex(input.getAttribute('name'));

        points.forEach(function (point) {
            const isSelected = null !== currentIdentity && identity(point.value) === currentIdentity;
            const item = el('label', 'list-group-item d-flex align-items-center gap-3' + (isSelected ? ' bg-primary-subtle' : ''));

            const radio = el('input', 'form-check-input flex-shrink-0 m-0');
            radio.type = 'radio';
            radio.name = groupName;
            radio.value = point.value;
            radio.checked = isSelected;
            radio.addEventListener('change', function () {
                input.value = radio.value;
                field._mode = 'summary';
                rerender();
            });
            item.appendChild(radio);

            const info = el('div', 'flex-grow-1');
            info.appendChild(el('div', 'fw-bold', point.name || ''));
            info.appendChild(el('div', 'text-body-secondary small', addressLine(point, ', ')));
            item.appendChild(info);

            if (isSelected) {
                item.appendChild(el('span', 'badge bg-primary text-white flex-shrink-0', field.getAttribute('data-selected-text')));
            }

            group.appendChild(item);
        });

        wrapper.appendChild(group);

        return wrapper;
    }

    function removeSection(index) {
        document.querySelectorAll('[' + SECTION_ATTR + '="' + index + '"]').forEach(function (section) {
            section.remove();
        });
    }

    function render(field, state) {
        const input = field.querySelector('[data-pickup-point-input]');
        if (null === input) {
            return;
        }

        const index = shipmentIndex(input.getAttribute('name'));
        removeSection(index);

        const method = document.querySelector(methodRadioSelector(index) + ':checked');
        if (null === method || !method.hasAttribute('data-pickup-point-provider')) {
            return;
        }

        const card = method.closest('.card');
        if (null === card) {
            return;
        }

        const section = el('div', 'setono-pickup-point px-3 pb-3');
        section.setAttribute(SECTION_ATTR, index);
        section.appendChild(el('hr', 'setono-pickup-point__divider my-3'));

        const points = (state.data && false !== state.data) ? (state.data[method.value] || []) : null;

        // Pre-select the nearest point (providers return them ordered by distance from the address) so
        // the shopper lands on the most likely choice in the compact summary instead of an open list;
        // they can still open the list to pick another.
        if (Array.isArray(points) && points.length > 0 && '' === input.value) {
            input.value = points[0].value;
            field._mode = 'summary';
        }

        const summaryMode = 'summary' === field._mode && '' !== input.value;

        const heading = el('div', 'fw-bold mb-2');
        heading.appendChild(document.createTextNode(field.getAttribute('data-label-text') || ''));
        if (!summaryMode && Array.isArray(points) && points.length > 0) {
            heading.appendChild(document.createTextNode(' '));
            heading.appendChild(el('span', 'text-danger', '*'));
        }
        section.appendChild(heading);

        const rerender = function () {
            render(field, state);
        };

        if (summaryMode) {
            section.appendChild(summary(field, input, rerender));
        } else if (null === state.data) {
            section.appendChild(message(field, 'data-loading-text'));
        } else if (false === state.data) {
            section.appendChild(message(field, 'data-error-text'));
        } else if (!Array.isArray(points) || 0 === points.length) {
            section.appendChild(message(field, 'data-empty-text'));
        } else {
            section.appendChild(list(field, input, points, rerender));
        }

        card.appendChild(section);
    }

    function setup(field, state) {
        const input = field.querySelector('[data-pickup-point-input]');
        if (null === input) {
            return;
        }

        field._mode = 'summary';

        document.querySelectorAll(methodRadioSelector(shipmentIndex(input.getAttribute('name')))).forEach(function (radio) {
            radio.addEventListener('change', function () {
                input.value = '';
                field._mode = 'summary';
                render(field, state);
            });
        });

        render(field, state);
    }

    function init() {
        const url = window.setonoSyliusPickupPointUrl;
        if (!url) {
            return;
        }

        const fields = [];
        document.querySelectorAll('[data-pickup-point-field]').forEach(function (field) {
            if (!field.hasAttribute('data-pickup-point-ready')) {
                field.setAttribute('data-pickup-point-ready', '');
                fields.push(field);
            }
        });
        if (0 === fields.length) {
            return;
        }

        // null = still loading, false = failed, object = points keyed by shipping method code.
        const state = { data: null };

        fields.forEach(function (field) {
            setup(field, state);
        });

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed: ' + response.status);
                }

                return response.json();
            })
            .then(function (data) {
                state.data = data;
            })
            .catch(function () {
                state.data = false;
            })
            .then(function () {
                fields.forEach(function (field) {
                    render(field, state);
                });
            });
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('turbo:load', init);

    // The script itself may be (re-)evaluated after the DOM is already interactive.
    if ('loading' !== document.readyState) {
        init();
    }
})();
