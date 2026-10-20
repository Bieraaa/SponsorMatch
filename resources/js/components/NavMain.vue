<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();

/*
 * Blok A — Menu sidebar (Figma "Dashboard EO"):
 * tiap item = kartu putih rounded-xl dengan bayangan lembut
 * (shadow 0 1px 3px navy 10%), teks-sm-card 14px medium.
 * Item aktif (rute saat ini): teks MERAH + font-semibold + bayangan
 * merah tipis, supaya posisi halaman terlihat jelas.
 * Item href '#' (fitur blok berikutnya) dirender sebagai <a> nonaktif.
 */
const itemBase =
    'flex w-full items-center gap-3 rounded-xl bg-white px-4 py-3 text-sm font-medium text-sm-card';
const itemIdle =
    'shadow-[0_1px_3px_rgba(22,30,47,0.10)] opacity-90 hover:shadow-[0_2px_6px_rgba(22,30,47,0.16)] hover:opacity-100';
const itemActive =
    'font-semibold text-sm-red shadow-[0_2px_8px_rgba(181,26,43,0.20)]';
</script>

<template>
    <SidebarGroup class="gap-1.5 px-3 py-1">
        <SidebarMenu class="gap-1.5">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <a
                    v-if="item.href === '#'"
                    href="#"
                    :class="[itemBase, itemIdle, 'cursor-default select-none']"
                    :title="`${item.title} — segera hadir`"
                    @click.prevent
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                    <span>{{ item.title }}</span>
                </a>
                <Link
                    v-else
                    :href="item.href"
                    :class="[
                        itemBase,
                        isCurrentUrl(item.href) ? itemActive : itemIdle,
                    ]"
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                    <span>{{ item.title }}</span>
                </Link>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
