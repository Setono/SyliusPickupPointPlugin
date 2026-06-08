import { describe, it, expect, afterEach } from 'vitest';
import { PickupPointChooser } from '../../../../public/js/setono-pickup-point.js';
import { buildDom, makePoints, tokenFor, section, rows, resetGlobals } from './fixtures.js';

afterEach(() => resetGlobals());

function openList() {
    section().querySelector('[data-action="change"]').click();
}

describe('list', () => {
    it('marks the persisted point as Selected by identity on reload (faker drift)', () => {
        const { field, input } = buildDom();
        const points = makePoints(3); // provider faker, ids 0,1,2
        // A persisted token with the same provider+id as points[1] but a drifted name.
        input.value = tokenFor({ provider: 'faker', id: '1', name: 'Old name', country: 'DK', metadata: [] });
        new PickupPointChooser(field).setData({ faker: points }); // summary (input already set)
        openList();

        const allRows = rows();
        expect(allRows[1].querySelector('[data-role="badge"]').hidden).toBe(false);
        expect(allRows[1].classList.contains('bg-primary-subtle')).toBe(true);
        expect(allRows[1].querySelector('[data-role="radio"]').checked).toBe(true);
        expect(allRows[0].querySelector('[data-role="badge"]').hidden).toBe(true);
        expect(section().querySelector('[data-slot="current-name"]').textContent).toBe('Old name');
    });

    it('selecting a row writes its token and returns to the summary', () => {
        const { field, input } = buildDom();
        const points = makePoints(3);
        new PickupPointChooser(field, { autoSelectNearest: false }).setData({ faker: points }); // list

        const radio = rows()[2].querySelector('[data-role="radio"]');
        radio.checked = true;
        radio.dispatchEvent(new Event('change', { bubbles: true }));

        expect(input.value).toBe(points[2].value);
        expect(section().querySelector('[data-action="change"]')).not.toBeNull(); // back to summary
    });

    it('"Keep this" returns to the summary without changing the input', () => {
        const { field, input } = buildDom();
        new PickupPointChooser(field).setData({ faker: makePoints(3) }); // auto-selected summary
        const token = input.value;
        openList();
        section().querySelector('[data-action="keep"]').click();

        expect(input.value).toBe(token);
        expect(section().querySelector('[data-action="change"]')).not.toBeNull();
    });
});
