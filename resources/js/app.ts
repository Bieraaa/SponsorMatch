import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            // Halaman publik & halaman auth utama mengatur layoutnya sendiri
            // (full-screen, tidak memakai kerangka sidebar/kartu starter kit):
            //   - landing/Index      : snap-scroll satu halaman penuh
            //   - auth/login/Index   : split kiri-ilustrasi + kanan-panel navy
            //   - auth/register/*    : full-screen navy (role pick + form per peran)
            case name === 'Welcome':
            case name === 'landing/Index':
            case name === 'auth/login/Index':
            case name === 'auth/register/Index':
            case name === 'auth/register/eo/Index':
            case name === 'auth/register/perusahaan/Index':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
