import { describe, it, expect } from 'vitest';
import { decodeToken, identity, addressLine } from '../../../../public/js/setono-pickup-point.js';
import { tokenFor } from './fixtures.js';

describe('decodeToken', () => {
    it('round-trips a base64url JSON token (incl. UTF-8)', () => {
        const token = tokenFor({ provider: 'gls', id: '42', name: 'Kiosk Ø', city: 'København' });
        expect(decodeToken(token)).toMatchObject({ provider: 'gls', id: '42', name: 'Kiosk Ø', city: 'København' });
    });

    it('returns null for malformed input', () => {
        expect(decodeToken('!!! not base64 !!!')).toBeNull();
        expect(decodeToken('')).toBeNull();
        expect(decodeToken(null)).toBeNull();
        expect(decodeToken(undefined)).toBeNull();
        expect(decodeToken(btoa('hello'))).toBeNull(); // valid base64, not JSON
    });
});

describe('identity', () => {
    it('is provider + id (not country or name)', () => {
        expect(identity(tokenFor({ provider: 'gls', id: '42', country: 'DK', name: 'A' }))).toBe('gls 42');
    });

    it('matches across faker drift — same provider+id, different name/country', () => {
        const a = identity(tokenFor({ provider: 'faker', id: '3', name: 'Old', country: 'DK' }));
        const b = identity(tokenFor({ provider: 'faker', id: '3', name: 'New', country: 'SE' }));
        expect(a).toBe(b);
        expect(a).toBe('faker 3');
    });

    it('returns null for a bad token', () => {
        expect(identity('!!!')).toBeNull();
    });
});

describe('addressLine', () => {
    it('joins the address and "zip city" with the separator', () => {
        expect(addressLine({ address: 'Main St 1', zipCode: '9000', city: 'Aalborg' }, ' · ')).toBe('Main St 1 · 9000 Aalborg');
    });

    it('omits empty parts', () => {
        expect(addressLine({ address: '', zipCode: '', city: 'Aalborg' }, ', ')).toBe('Aalborg');
        expect(addressLine({ address: 'Main St', zipCode: '', city: '' }, ', ')).toBe('Main St');
    });
});
