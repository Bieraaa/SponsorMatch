<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Building2, CalendarDays, FileText, MapPin } from '@lucide/vue';
import { computed, ref } from 'vue';
import { show } from '@/routes/sponsor/proposals';

type Item = {
    trackingId: number;
    proposalId: number;
    title: string;
    kategori: string | null;
    eoName: string | null;
    location: string | null;
    budgetMin: number;
    budgetMax: number;
    stage: string;
    stageLabel: string;
    sentAt: string | null;
    offerCount: number;
};

type Counts = {
    all: number;
    new: number;
    negotiating: number;
    deal: number;
    rejected: number;
};

const props = defineProps<{
    items: Item[];
    counts: Counts;
}>();

type Filter = 'all' | 'new' | 'negotiating' | 'deal' | 'rejected';

const activeFilter = ref<Filter>('all');

const tabs: { key: Filter; label: string }[] = [
    { key: 'all', label: 'Semua' },
    { key: 'new', label: 'Baru' },
    { key: 'negotiating', label: 'Negosiasi' },
    { key: 'deal', label: 'Disetujui' },
    { key: 'rejected', label: 'Ditolak' },
];

const filtered = computed(() => {
    if (activeFilter.value === 'all') {
        return props.items;
    }

    if (activeFilter.value === 'deal') {
        return props.items.filter((item) => ['deal', 'waiting_report', 'done'].includes(item.stage));
    }

    if (activeFilter.value === 'new') {
        return props.items.filter((item) => ['sent', 'viewed'].includes(item.stage));
    }

    if (activeFilter.value === 'negotiating') {
        return props.items.filter((item) => item.stage === 'negotiating');
    }

    return props.items.filter((item) => item.stage === 'rejected');
});

const stageStyle: Record<string, string> = {
    sent: 'bg-sky-50 text-sky-700 border-sky-200',
    viewed: 'bg-blue-50 text-blue-700 border-blue-200',
    negotiating: 'bg-amber-50 text-amber-700 border-amber-200',
    deal: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    waiting_report: 'bg-violet-50 text-violet-700 border-violet-200',
    done: 'bg-gray-100 text-gray-700 border-gray-200',
    rejected: 'bg-red-50 text-red-700 border-red-200',
};

function formatRupiah(value: number): string {
    return 'Rp' + value.toLocaleString('id-ID');
}

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Proposal Masuk - Sponsor" />

    <div class="min-h-full bg-sm-navy pb-10">
        <div class="mx-auto max-w-[1100px] px-4 py-6 xl:px-0">
            <header>
                <h1 class="text-2xl font-semibold text-white sm:text-[28px]">Proposal Masuk</h1>
                <p class="mt-1.5 text-sm text-white/70">
                    Daftar proposal sponsorship yang dikirim ke perusahaan kamu.
                </p>
            </header>

            <div class="mt-5 flex flex-wrap gap-2">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[12px] font-medium transition-colors"
                    :class="
                        activeFilter === tab.key
                            ? 'border-white bg-white text-sm-navy'
                            : 'border-white/25 text-white/75 hover:border-white/50 hover:text-white'
                    "
                    @click="activeFilter = tab.key"
                >
                    {{ tab.label }}
                    <span
                        class="rounded-full px-1.5 py-0.5 text-[10px]"
                        :class="activeFilter === tab.key ? 'bg-sm-navy/10 text-sm-navy' : 'bg-white/10 text-white/80'"
                    >
                        {{ props.counts[tab.key] }}
                    </span>
                </button>
            </div>

            <section class="mt-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div v-if="filtered.length === 0" class="px-6 py-10 text-center text-sm text-gray-500">
                    Tidak ada proposal pada kategori ini.
                </div>
                <ul v-else class="divide-y divide-gray-100">
                    <li v-for="item in filtered" :key="item.trackingId">
                        <Link
                            :href="show(item.trackingId).url"
                            class="flex flex-wrap items-center gap-4 px-5 py-4 transition-colors hover:bg-gray-50"
                        >
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-sm-navy text-white">
                                <FileText class="size-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[14px] font-medium text-sm-navy">{{ item.title }}</p>
                                <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <Building2 class="size-3.5" />{{ item.eoName ?? 'EO' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <MapPin class="size-3.5" />{{ item.location ?? '-' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <CalendarDays class="size-3.5" />Dikirim {{ formatDate(item.sentAt) }}
                                    </span>
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <p class="text-[12px] font-medium text-sm-navy">
                                    {{ formatRupiah(item.budgetMin) }} – {{ formatRupiah(item.budgetMax) }}
                                </p>
                                <span
                                    class="rounded-full border px-2 py-0.5 text-[10px] font-medium"
                                    :class="stageStyle[item.stage] ?? 'bg-gray-50 text-gray-600 border-gray-200'"
                                >
                                    {{ item.stageLabel }}
                                </span>
                                <p v-if="item.offerCount > 0" class="text-[10px] text-gray-400">
                                    {{ item.offerCount }} penawaran
                                </p>
                            </div>
                        </Link>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
