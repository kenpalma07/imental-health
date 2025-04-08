import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import obfuscatorPlugin from "vite-plugin-javascript-obfuscator";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        obfuscatorPlugin({
            //include: ["src/path/to/file.js", "path/anyjs/**/*.js", /foo.js$/],
            exclude: [/node_modules/],
            apply: "build",
            debugger: true,
            options: {
              // your javascript-obfuscator options
              debugProtection: false,
              disableConsoleOutput: true,
              // ...  [See more options](https://github.com/javascript-obfuscator/javascript-obfuscator)
            },
        }),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
});
