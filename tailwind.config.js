// Mengimpor utilitas withMT dari Material Tailwind
const withMT = require("@material-tailwind/react/utils/withMT");
// Mengimpor tema default Tailwind dan plugin forms
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
// Membungkus ekspor konfigurasi dengan withMT
export default withMT({
  // Konfigurasi mode gelap sudah ada
  darkMode: 'class',

  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.jsx', // Path untuk file React/JSX kamu
    './node_modules/flowbite/**/*.js', // Path untuk Flowbite

    // Path untuk komponen Material Tailwind
    // Pastikan path ini benar sesuai dengan struktur node_modules Anda.
    // Jika Anda menginstal @material-tailwind/react di root proyek, path ini seharusnya sudah benar.
    "node_modules/@material-tailwind/react/components/**/*.{js,ts,jsx,tsx}",
    "node_modules/@material-tailwind/react/theme/components/**/*.{js,ts,jsx,tsx}",
  ],

  theme: {
    extend: {
      // Kustomisasi warna yang sudah ada
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
      // Kustomisasi font family yang sudah ada
      fontFamily: {
        'body': [
          'Inter',
          'ui-sans-serif',
          'system-ui',
          '-apple-system',
          // 'system-ui', // duplikat, bisa dihapus
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
          // 'system-ui', // duplikat, bisa dihapus
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
    require('flowbite/plugin'), // Plugin Flowbite yang sudah ada
    // Material Tailwind tidak memerlukan plugin khusus di sini jika sudah dibungkus dengan withMT
  ],
});
