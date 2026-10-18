import '../css/app.css';
import { createElement } from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    resolve: async (name) => {
        const page = pages[`./Pages/${name}.jsx`];
        if (!page) throw new Error(`Página não encontrada: ${name}`);
        return page();
    },
    setup({ el, App, props }) {
        createRoot(el).render(createElement(App, props));
    },
});
