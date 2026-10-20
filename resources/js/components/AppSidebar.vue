<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    CreditCard,
    FileText,
    Handshake,
    Headphones,
    History,
    LayoutGrid,
    Megaphone,
    Settings,
    Sparkles,
} from '@lucide/vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { edit } from '@/routes/profile';
import type { NavItem } from '@/types';

/**
 * Blok A — Sidebar aplikasi (Figma "Dashboard EO", export 19 Okt 2026).
 *
 * Hasil analisis piksel:
 * - Sidebar PUTIH selebar 240px, teks/ikon navy #242F49.
 * - 9 menu urut: Dashboard, Rekomendasi Sponsor, Proposal Saya, Penawaran,
 *   Postingan, Riwayat Kolaborasi, Subscription, Pengaturan, Bantuan.
 * - Item menu berbentuk kartu putih dengan bayangan lembut.
 * - Logo "SponsorMatch" di atas (crop dari export Figma →
 *   /assets/logo_sponsormatch_sidebar.png).
 * - Bagian bawah: avatar + nama + email (NavUser).
 *
 * Menu selain Dashboard & Pengaturan adalah fitur blok berikutnya,
 * jadi href '#' (NavMain merender <a> nonaktif untuk '#' — tidak ada
 * rute di Laravel untuk itu).
 */
const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'Rekomendasi Sponsor', href: '#', icon: Sparkles },
    { title: 'Proposal Saya', href: '#', icon: FileText },
    { title: 'Penawaran', href: '#', icon: Handshake },
    { title: 'Postingan', href: '#', icon: Megaphone },
    { title: 'Riwayat Kolaborasi', href: '#', icon: History },
    { title: 'Subscription', href: '#', icon: CreditCard },
    { title: 'Pengaturan', href: edit(), icon: Settings },
    { title: 'Bantuan', href: '#', icon: Headphones },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="bg-white">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <img
                                src="/assets/logo_sponsormatch_sidebar.png"
                                alt="SponsorMatch"
                                class="h-9 w-auto"
                            />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
