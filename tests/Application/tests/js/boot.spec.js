import { describe, it, expect, afterEach, vi } from 'vitest';
import { PickupPointChooser } from '../../../../public/js/setono-pickup-point.js';
import { buildDom, makePoints, section, resetGlobals } from './fixtures.js';

afterEach(() => resetGlobals());

// The module binds boot() to DOMContentLoaded at import time; dispatching the event re-runs it deterministically.
function fireBoot() {
    document.dispatchEvent(new Event('DOMContentLoaded'));
}

function stubFetch(data) {
    vi.stubGlobal('fetch', vi.fn(() => Promise.resolve({ ok: true, json: () => Promise.resolve(data) })));
}

describe('boot', () => {
    it('boots: stamps the field ready, fetches once and renders', async () => {
        const { field } = buildDom();
        window.setonoSyliusPickupPointUrl = '/pickup-points';
        stubFetch({ faker: makePoints(2) });

        fireBoot();

        expect(field.hasAttribute('data-pickup-point-ready')).toBe(true);
        // start() renders a loading section synchronously; wait for the summary that appears after the fetch.
        await vi.waitFor(() => {
            const name = section() && section().querySelector('[data-slot="name"]');
            expect(name && name.textContent).toBe('Point #0');
        });
        expect(globalThis.fetch).toHaveBeenCalledTimes(1);
    });

    it('does nothing and does not throw when no endpoint URL is set', () => {
        const { field } = buildDom();

        expect(() => fireBoot()).not.toThrow();
        expect(field.hasAttribute('data-pickup-point-ready')).toBe(false);
        expect(section()).toBeNull();
    });

    it('uses a subclass registered on window.SetonoSyliusPickupPointChooser', async () => {
        buildDom();
        window.setonoSyliusPickupPointUrl = '/pickup-points';
        window.setonoSyliusPickupPointConfig = { autoSelectNearest: false }; // show the list so rows render
        window.SetonoSyliusPickupPointChooser = class extends PickupPointChooser {
            renderRow(point, currentIdentity) {
                const node = super.renderRow(point, currentIdentity);
                if (null !== node) {
                    node.setAttribute('data-custom', '');
                }

                return node;
            }
        };
        stubFetch({ faker: makePoints(2) });

        fireBoot();

        // Wait for the list (autoSelectNearest=false) rendered after the fetch resolves.
        await vi.waitFor(() => {
            expect(section()).not.toBeNull();
            expect(section().querySelectorAll('[data-role="list"] label[data-custom]').length).toBe(2);
        });
    });
});
