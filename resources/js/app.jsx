import './bootstrap';
import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Toaster } from '@/Components/ui/toaster';
import { MaterialTailwindControllerProvider } from './Context/MaterialTailwindContext'; // Path ke context provider Anda

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(
      <MaterialTailwindControllerProvider> {/* Bungkus di sini */}
        <App {...props} />
        <Toaster duration={3000} />
      </MaterialTailwindControllerProvider>
    );
  },
  progress: {
    color: '#4B5563',
  },
});