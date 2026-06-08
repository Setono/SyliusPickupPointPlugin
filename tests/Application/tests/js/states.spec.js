import { describe, it, expect, afterEach } from 'vitest';
import { PickupPointChooser } from '../../../../public/js/setono-pickup-point.js';
import { buildDom, section, resetGlobals } from './fixtures.js';

afterEach(() => resetGlobals());

function visibleStates() {
    return Array.from(section().querySelectorAll('[data-state]'))
        .filter((span) => !span.hidden)
        .map((span) => span.getAttribute('data-state'));
}

describe('states', () => {
    it('shows the loading message before data arrives', () => {
        const { field } = buildDom();
        new PickupPointChooser(field).render(); // data is null

        expect(visibleStates()).toEqual(['loading']);
    });

    it('shows the error message on fetch failure', () => {
        const { field } = buildDom();
        new PickupPointChooser(field).setData(false);

        expect(visibleStates()).toEqual(['error']);
    });

    it('shows the empty message when the method has no points', () => {
        const { field } = buildDom();
        new PickupPointChooser(field).setData({ faker: [] });

        expect(visibleStates()).toEqual(['empty']);
    });
});
