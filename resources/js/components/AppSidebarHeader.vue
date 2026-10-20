<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Bell, Coins, Search } from '@lucide/vue';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { SidebarTrigger } from '@/components/ui/sidebar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem } from '@/types';

/**
 * Blok A — Topbar aplikasi (Figma "Dashboard EO", export 19 Okt 2026).
 *
 * Hasil analisis piksel:
 * - Bar putih tinggi 67px dengan garis pemisah tipis.
 * - Search pill LEBAR warna navy #242F49 (radius penuh), placeholder
 *   "Cari sponsor, perusahaan atau event" teks putih.
 * - Lonceng notifikasi dengan badge angka merah ("3" pada desain).
 * - Chip navy berisi angka kredit ("250" pada desain) dengan ikon koin.
 * - Kanan: foto avatar + nama user + peran ("Event Organizer" untuk role
 *   eo, "Perusahaan" untuk role sponsor) — nama & peran diambil dari
 *   session, bukan hardcode.
 * Pencarian, notifikasi & kredit tampilan dulu (fitur blok berikutnya).
 */
const page = usePage();
const auth = computed(() => page.props.auth);
const roleName = computed(() =>
    auth.value.user?.role === 'sponsor' ? 'Perusahaan' : 'Event Organizer',
);

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4 transition-[width,height] ease-linear md:px-6"
    >
        <SidebarTrigger class="-ml-1 shrink-0 text-sm-card" />

        <Breadcrumbs
            v-if="breadcrumbs.length > 0"
            :breadcrumbs="breadcrumbs"
            class="shrink-0"
        />

        <!-- Search lebar navy (Figma: pill dari x~296 s.d. x~1121) -->
        <div class="relative hidden flex-1 lg:block">
            <Search
                class="pointer-events-none absolute left-5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/70"
            />
            <input
                type="search"
                placeholder="Cari sponsor, perusahaan atau event"
                class="h-10 w-full max-w-[820px] rounded-full bg-sm-card pl-12 pr-5 text-sm text-white placeholder:text-white/70 focus:outline-none"
            />
        </div>

        <div class="ml-auto flex shrink-0 items-center gap-3">
            <!-- Notifikasi + badge merah (tampilan dulu) -->
            <button
                type="button"
                class="relative flex h-10 w-10 items-center justify-center rounded-full text-sm-card hover:bg-gray-100"
                title="Notifikasi"
            >
                <Bell class="h-5 w-5" />
                <span
                    class="absolute right-1 top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-sm-red px-1 text-[11px] font-semibold text-white"
                >
                    3
                </span>
            </button>

            <!-- Chip kredit (tampilan dulu) -->
            <span
                class="hidden items-center gap-1.5 rounded-full bg-sm-card px-3.5 py-2 text-sm font-medium text-white md:flex"
            >
                <Coins class="h-4 w-4 text-sm-amber" />
                250
            </span>

            <!-- Avatar + nama + peran → dropdown menu user -->
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <button
                        type="button"
                        class="flex items-center gap-3 rounded-full pr-1 focus:outline-none"
                        data-test="user-menu-button"
                    >
                        <div class="hidden text-left leading-tight md:block">
                            <p class="text-sm font-semibold text-sm-card">
                                {{ auth.user?.name }}
                            </p>
                            <p class="text-xs text-sm-card/60">
                                {{ roleName }}
                            </p>
                        </div>
                        <Avatar class="size-10 overflow-hidden rounded-full">
                            <AvatarImage
                                v-if="auth.user?.avatar"
                                :src="auth.user.avatar"
                                :alt="auth.user.name"
                            />
                            <AvatarFallback
                                class="rounded-full bg-sm-card font-semibold text-white"
                            >
                                {{ getInitials(auth.user?.name) }}
                            </AvatarFallback>
                        </Avatar>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <UserMenuContent :user="auth.user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
