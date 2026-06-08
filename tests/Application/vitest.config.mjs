// ESM config (.mjs) on purpose: tests/Application/package.json is NOT "type": "module" because Encore's
// webpack.config.js is CommonJS. Vitest still imports the ESM source under test via Vite.
import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment: 'jsdom',
        include: ['tests/js/**/*.spec.js'],
        restoreMocks: true,
        unstubGlobals: true,
    },
    // The module under test lives at the repo root (public/js), above this config's root (tests/Application),
    // so allow Vite to read files up there.
    server: {
        fs: {
            allow: ['../..'],
        },
    },
});
