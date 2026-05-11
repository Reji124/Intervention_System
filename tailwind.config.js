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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            screens: {
                '320': '320px',
                '480': '480px',
                // Keeping default Tailwind breakpoints
                // sm: '640px' => already exists
                // md: '768px' => already exists (tablet)
                // lg: '1024px' => already exists (laptop)
                // xl: '1280px' => already exists (desktop)
            },
        },
    },

    plugins: [forms],
};
