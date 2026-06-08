import { describe, it, expect, afterEach } from 'vitest';
import { PickupPointChooser } from '../../../../public/js/setono-pickup-point.js';
import { buildDom, makePoints, section, rows, resetGlobals } from './fixtures.js';

afterEach(() => resetGlobals());

describe('render', () => {
    it('auto-selects the nearest point and shows the summary', () => {
        const { field, input } = buildDom();
        const points = makePoints(3);
        new PickupPointChooser(field).setData({ faker: points });

        expect(input.value).toBe(points[0].value);
        const sec = section();
        expect(sec).not.toBeNull();
        expect(sec.querySelector('[data-action="change"]')).not.toBeNull();
        expect(sec.querySelector('[data-slot="name"]').textContent).toBe(points[0].name);
    });

    it('with autoSelectNearest=false leaves the input empty and shows the list + required marker', () => {
        const { field, input } = buildDom();
        new PickupPointChooser(field, { autoSelectNearest: false }).setData({ faker: makePoints(3) });

        expect(input.value).toBe('');
        expect(rows().length).toBe(3);
        expect(section().querySelector('[data-role="required"]').hidden).toBe(false);
    });

    it('renders no section for a method without a provider', () => {
        const { field } = buildDom({ provider: null });
        new PickupPointChooser(field).setData({ faker: makePoints(2) });

        expect(section()).toBeNull();
    });

    it('removes the prior section on re-render', () => {
        const { field } = buildDom();
        const chooser = new PickupPointChooser(field);
        chooser.setData({ faker: makePoints(2) });
        chooser.render();
        chooser.render();

        expect(document.querySelectorAll('[data-setono-pickup-section]').length).toBe(1);
    });

    it('renders nothing and does not crash when the templates are absent', () => {
        const { field } = buildDom({ templates: false });
        const chooser = new PickupPointChooser(field);

        expect(() => chooser.setData({ faker: makePoints(2) })).not.toThrow();
        expect(section()).toBeNull();
    });
});
