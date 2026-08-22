import { initializeTheme } from '@/hooks/use-appearance';
import { createInertiaApp } from '@inertiajs/react';
import { TooltipProvider } from '@radix-ui/react-tooltip';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { type ComponentType } from 'react';
import { Toaster } from 'sonner';
import AppLayout from './layouts/app-layout';
import AuthLayout from './layouts/auth-layout';
import SettingsLayout from './layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    resolve: (name) => {
        console.log('Inertia page:', name);

        const appPages = import.meta.glob<{ default: ComponentType }>(
            './pages/**/*.tsx',
        );

        const modulePages = import.meta.glob<{ default: ComponentType }>(
            '../../Modules/*/resources/js/pages/**/*.tsx',
        );

        console.log('Module pages:', Object.keys(modulePages));

        const parts = name.split('/');
        const modulePage = `../../Modules/${parts[0]}/resources/js/pages/${parts.slice(1).join('/')}.tsx`;

        console.log('Looking for:', modulePage);

        if (modulePages[modulePage]) {
            console.log('Found module page');
            return resolvePageComponent(modulePage, modulePages);
        }

        console.log('Using application page');

        return resolvePageComponent(`./pages/${name}.tsx`, appPages);
    },
    // setup({ el, App, props }) {
    //     createRoot(el).render(<App {...props} />);
    // },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
