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
            // --- INICIO DE NUESTRA PERSONALIZACIÓN (ACTUALIZADA) ---
            colors: {
                // Color Primario (Azul Oscuro FIIS)
                'primary': {
                    '50': '#eef2ff',
                    '100': '#e0e7ff',
                    '200': '#c7d2fe',
                    '300': '#a5b4fc',
                    '400': '#818cf8',
                    '500': '#6366f1',
                    '600': '#4f46e5',
                    '700': '#4338ca', // Azul principal para botones, etc.
                    '800': '#3730a3',
                    '900': '#312e81',
                    '950': '#1e1b4b',
                },
                // Color Secundario (Dorado/Amarillo FIIS)
                'secondary': {
                    '50': '#fefce8',
                    '100': '#fef9c3',
                    '200': '#fef08a',
                    '300': '#fde047',
                    '400': '#facc15', // Acento principal
                    '500': '#eab308',
                    '600': '#ca8a04', // Acento más oscuro
                    '700': '#a16207',
                    '800': '#854d0e',
                    '900': '#713f12',
                    '950': '#422006',
                },
            },
            // --- FIN DE NUESTRA PERSONALIZACIÓN (ACTUALIZADA) ---
        },
    },

    plugins: [forms],
};