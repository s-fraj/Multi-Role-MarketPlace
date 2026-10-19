import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Work Sans', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                paper: '#F6F2E8',
                card: '#FFFEFB',
                ink: '#16262B',
                teal: {
                    DEFAULT: '#1E5C63',
                    deep: '#123840',
                },
                ochre: '#E2A33B',
                terracotta: '#C4552E',
                line: '#DDD5C3',
                muted: '#8A8578',
                graphite: '#3F4E52',
            },
        },
    },

    plugins: [forms],
};
