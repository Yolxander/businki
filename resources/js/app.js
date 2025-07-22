import './bootstrap';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import React from 'react';
import { Toaster } from 'sonner';

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            React.createElement(React.Fragment, {},
                React.createElement(App, props),
                React.createElement(Toaster, {
                    position: "top-right",
                    theme: "dark",
                    richColors: true,
                    closeButton: true,
                    duration: 4000,
                    style: {
                        zIndex: 9999,
                        position: 'fixed'
                    },
                    toastOptions: {
                        style: {
                            background: '#171717',
                            border: '1px solid #282828',
                            color: '#fafafa',
                            fontFamily: 'Orbitron, monospace'
                        },
                        success: {
                            style: {
                                background: '#171717',
                                border: '1px solid #d1ff75',
                                color: '#fafafa'
                            }
                        },
                        error: {
                            style: {
                                background: '#171717',
                                border: '1px solid #ff6467',
                                color: '#fafafa'
                            }
                        },
                        warning: {
                            style: {
                                background: '#171717',
                                border: '1px solid #fbbf24',
                                color: '#fafafa'
                            }
                        },
                        info: {
                            style: {
                                background: '#171717',
                                border: '1px solid #60a5fa',
                                color: '#fafafa'
                            }
                        }
                    }
                })
            )
        );
    },
});
