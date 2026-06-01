/**
 * Pickup-point chooser (no framework, no Stimulus).
 *
 * The shipping page renders without any provider calls. Once it is on screen this script fetches — once
 * per visit, asynchronously — every pickup-capable method's points for the current cart from a single
 * JSON endpoint (window.setonoSyliusPickupPointUrl). While the request is in flight the field shows a
 * "loading" message for the selected pickup method, so a slow or down provider degrades gracefully
 * instead of blocking checkout. When the response arrives the radios are built client-side; selecting one
 * writes its opaque token into the hidden input, which the server decodes on submit (no re-fetch).
 *
 * Sylius' shop navigates with Turbo (the checkout steps are AJAX body swaps), so initialisation is bound
 * to both DOMContentLoaded (first/full load) and turbo:load (every navigation); a per-field flag keeps it
 * from running twice on the same DOM.
 */
(function () {
    'use strict';

    if (window.__setonoSyliusPickupPointBooted) {
        return;
    }
    window.__setonoSyliusPickupPointBooted = true;

    function shipmentIndex(name) {
        const match = /\[shipments]\[(\d+)]/.exec(name || '');

        return match ? match[1] : '0';
    }

    function methodRadioSelector(index) {
        return 'input[type="radio"][name*="[shipments][' + index + '][method]"]';
    }

    function showMessage(field, attribute) {
        const choices = field.querySelector('[data-pickup-point-choices]');
        const message = document.createElement('div');
        message.className = 'setono-sylius-pickup-point-field-message text-muted';
        message.textContent = field.getAttribute(attribute) || '';
        choices.innerHTML = '';
        choices.appendChild(message);
    }

    function buildChoice(point, groupName, index, position, checked) {
        const id = 'setono-pickup-point-' + index + '-' + position;

        const wrapper = document.createElement('div');
        wrapper.className = 'form-check setono-sylius-pickup-point-field-choice';

        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.className = 'form-check-input';
        radio.name = groupName;
        radio.id = id;
        radio.value = point.value;
        radio.checked = checked;

        const label = document.createElement('label');
        label.className = 'form-check-label';
        label.htmlFor = id;

        const parts = [point.name, point.address, [point.zipCode, point.city].filter(Boolean).join(' ')];
        label.appendChild(document.createTextNode(parts.filter(Boolean).join(', ')));

        if (point.latitude && point.longitude) {
            label.appendChild(document.createTextNode(' '));
            const map = document.createElement('a');
            map.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(point.latitude + ',' + point.longitude);
            map.target = '_blank';
            map.rel = 'noopener';
            map.textContent = '🗺';
            label.appendChild(map);
        }

        wrapper.appendChild(radio);
        wrapper.appendChild(label);

        return wrapper;
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

        function render(field) {
            const input = field.querySelector('[data-pickup-point-input]');
            const choices = field.querySelector('[data-pickup-point-choices]');
            if (null === input || null === choices) {
                return;
            }

            const index = shipmentIndex(input.getAttribute('name'));
            const method = document.querySelector(methodRadioSelector(index) + ':checked');

            if (null === method || !method.hasAttribute('data-pickup-point-provider')) {
                field.hidden = true;
                choices.innerHTML = '';

                return;
            }

            field.hidden = false;

            if (null === state.data) {
                showMessage(field, 'data-loading-text');

                return;
            }

            if (false === state.data) {
                showMessage(field, 'data-error-text');

                return;
            }

            const points = state.data[method.value] || [];
            if (0 === points.length) {
                showMessage(field, 'data-empty-text');

                return;
            }

            choices.innerHTML = '';
            const groupName = 'setono-pickup-point-choice-' + index;
            points.forEach(function (point, position) {
                choices.appendChild(buildChoice(point, groupName, index, position, point.value === input.value));
            });
        }

        function setup(field) {
            const input = field.querySelector('[data-pickup-point-input]');
            const choices = field.querySelector('[data-pickup-point-choices]');
            if (null === input || null === choices) {
                return;
            }

            const index = shipmentIndex(input.getAttribute('name'));

            // Selecting a point writes its token into the hidden field that is actually submitted.
            choices.addEventListener('change', function (event) {
                if (event.target instanceof HTMLInputElement && 'radio' === event.target.type) {
                    input.value = event.target.value;
                }
            });

            // Changing the shipping method clears the previous choice and re-renders for the new method.
            document.querySelectorAll(methodRadioSelector(index)).forEach(function (radio) {
                radio.addEventListener('change', function () {
                    input.value = '';
                    render(field);
                });
            });

            render(field);
        }

        fields.forEach(setup);

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
                fields.forEach(render);
            });
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('turbo:load', init);

    // The script itself may be (re-)evaluated after the DOM is already interactive.
    if ('loading' !== document.readyState) {
        init();
    }
})();
