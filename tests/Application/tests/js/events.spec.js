import { describe, it, expect, afterEach, vi } from 'vitest';
import { PickupPointChooser } from '../../../../public/js/setono-pickup-point.js';
import { buildDom, makePoints, rows, resetGlobals } from './fixtures.js';

afterEach(() => resetGlobals());

describe('events', () => {
    it('dispatches a bubbling selected event (source=list) on row click', () => {
        const { field } = buildDom();
        const points = makePoints(3);
        new PickupPointChooser(field, { autoSelectNearest: false }).setData({ faker: points });

        const handler = vi.fn();
        document.addEventListener('setono:pickup-point:selected', handler);
        const radio = rows()[1].querySelector('[data-role="radio"]');
        radio.checked = true;
        radio.dispatchEvent(new Event('change', { bubbles: true }));

        expect(handler).toHaveBeenCalledOnce();
        const detail = handler.mock.calls[0][0].detail;
        expect(detail.source).toBe('list');
        expect(detail.token).toBe(points[1].value);
        expect(detail.point.id).toBe('1');
    });

    it('dispatches a selected event (source=auto) during auto-select', () => {
        const { field } = buildDom();
        const handler = vi.fn();
        document.addEventListener('setono:pickup-point:selected', handler);
        new PickupPointChooser(field).setData({ faker: makePoints(2) });

        expect(handler).toHaveBeenCalledOnce();
        expect(handler.mock.calls[0][0].detail.source).toBe('auto');
    });

    it('lets a listener veto the selection with preventDefault()', () => {
        const { field, input } = buildDom();
        document.addEventListener('setono:pickup-point:selected', (event) => event.preventDefault());
        new PickupPointChooser(field).setData({ faker: makePoints(2) }); // auto-select vetoed

        expect(input.value).toBe('');
    });

    it('dispatches loaded (with methodCode + points) and error', () => {
        const { field } = buildDom();
        const loaded = vi.fn();
        const error = vi.fn();
        document.addEventListener('setono:pickup-points:loaded', loaded);
        document.addEventListener('setono:pickup-points:error', error);

        new PickupPointChooser(field).setData({ faker: makePoints(2) });
        expect(loaded).toHaveBeenCalledOnce();
        expect(loaded.mock.calls[0][0].detail.methodCode).toBe('faker');
        expect(loaded.mock.calls[0][0].detail.points.length).toBe(2);

        new PickupPointChooser(field).setData(false);
        expect(error).toHaveBeenCalledOnce();
    });
});
