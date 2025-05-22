import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
// daisyui dan flowbite/plugin di-require di bawah, jadi tidak perlu diimpor di sini.

/** @type {import('tailwindcss').Config} */
export default {
    // Diambil dari konfigurasi baru Anda
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
        './node_modules/flowbite/**/*.js', // Pastikan ini ada untuk Flowbite
    ],

    theme: {
        extend: {
            // Diambil dari konfigurasi baru Anda
            colors: {
                primary: {
                    "50": "#eff6ff",
                    "100": "#dbeafe",
                    "200": "#bfdbfe",
                    "300": "#93c5fd",
                    "400": "#60a5fa",
                    "500": "#3b82f6",
                    "600": "#2563eb",
                    "700": "#1d4ed8",
                    "800": "#1e40af",
                    "900": "#1e3a8a",
                    "950": "#172554"
                }
            },
            // fontFamily dari konfigurasi baru Anda akan menggantikan/menambahkan
            // yang ada di 'extend' konfigurasi lama Anda.
            // Konfigurasi 'sans: ['Figtree', ...]' sebelumnya akan tergantikan oleh 'sans' dari sini.
            fontFamily: {
                'body': [
                    'Inter',
                    'ui-sans-serif',
                    'system-ui',
                    '-apple-system',
                    'system-ui', // duplikat 'system-ui', bisa dihapus salah satu jika mau
                    'Segoe UI',
                    'Roboto',
                    'Helvetica Neue',
                    'Arial',
                    'Noto Sans',
                    'sans-serif',
                    'Apple Color Emoji',
                    'Segoe UI Emoji',
                    'Segoe UI Symbol',
                    'Noto Color Emoji'
                ],
                'sans': [
                    'Inter',
                    'ui-sans-serif',
                    'system-ui',
                    '-apple-system',
                    'system-ui', // duplikat 'system-ui', bisa dihapus salah satu jika mau
                    'Segoe UI',
                    'Roboto',
                    'Helvetica Neue',
                    'Arial',
                    'Noto Sans',
                    'sans-serif',
                    'Apple Color Emoji',
                    'Segoe UI Emoji',
                    'Segoe UI Symbol',
                    'Noto Color Emoji'
                ]
            },
        },
    },

    plugins: [
        forms,
        require('flowbite/plugin'), // Plugin Flowbite
    ],

};