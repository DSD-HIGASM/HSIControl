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
            colors: {
                brand: {
                    // Color Institucional (Cian)
                    cyan: {
                        light: '#33bece',
                        DEFAULT: '#00aec3',
                        dark: '#008ba0',
                    },
                    // Color Secundario (Azul)
                    blue: {
                        light: '#5b8bb5',
                        DEFAULT: '#417099',
                        dark: '#315473',
                    },
                    // Acento Principal (Rosa/Magenta)
                    pink: {
                        light: '#ee4c91',
                        DEFAULT: '#e81f76',
                        dark: '#b9195e',
                    },
                    // Variantes para gradientes y fondos neutros
                    'soft-100': '#cae7ea',
                    'soft-200': '#a3d8e7',
                    'soft-300': '#74c9e3',
                    'gray-custom': '#838383',
                }
            },
            fontFamily: {
                // Tipografía Principal
                sans: ['"Encode Sans"', ...defaultTheme.fontFamily.sans],
                // Tipografía Secundaria
                secondary: ['Roboto', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};
