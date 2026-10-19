import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981', // Vibrant Crescent Emerald
                    600: '#059669',
                    700: '#047857',
                    800: '#0c3e2e', // Deep Primary Heritage Green
                    900: '#06241a', // Deep Primary Forest Canvas
                    950: '#03140e',
                },
                accent: {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    200: '#fde68a',
                    300: '#fcd34d',
                    400: '#fbbf24',
                    500: '#f59e0b', // Warm Amber Gold
                    600: '#d97706',
                    700: '#b45309',
                },
                urgent: {
                    50: '#fff1f2',
                    100: '#ffe4e6',
                    500: '#f43f5e',
                    600: '#e11d48', // Urgent Crimson
                    700: '#be123c',
                }
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                nastaliq: ['"Noto Nastaliq Urdu"', 'serif'],
            },
        },
    },

    plugins: [forms],
};
