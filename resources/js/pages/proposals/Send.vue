<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Building2, Send } from '@lucide/vue';
import { ref, computed } from 'vue';
import ProposalController from '@/actions/App/Http/Controllers/ProposalController';
import { index as proposalsIndex } from '@/routes/proposals';

type Sponsor = {
    id: number;
    name: string;
    industry: string | null;
    email: string;
    alreadySent: boolean;
};

type ProposalInfo = {
    id: number;
    nomor: string;
    title: string;
    kategori: string | null;
};

const props = defineProps<{
    proposal: ProposalInfo;
    sponsors: Sponsor[];
}>();

const selected = ref<number[]>([]);
const search = ref('');

const filtered = computed(() => {
    const q = search.value.toLowerCase().trim();

    if (q === '') {
        return props.sponsors;
    }

    return props.sponsors.filter((sponsor) => {
        const name = sponsor.name.toLowerCase();
        const industry = (sponsor.industry ?? '').toLowerCase();

        return name.includes(q) || industry.includes(q);
    });
});

function toggle(sponsor: Sponsor): void {
    if (sponsor.alreadySent) {
        return;
    }

    const index = selected.value.indexOf(sponsor.id);

    if (index >= 0) {
        selected.value.splice(index, 1);
        return;
    }

    selected.value.push(sponsor.id);
}

function selectAll(): void {
    const ids = filtered.value
        .filter((sponsor) => !sponsor.alreadySent)
        .map((sponsor) => sponsor.id);

    selected.value = [...new Set([...selected.value, ...ids])];
}

function clearAll(): void {
    selected.value = [];
}

const canSubmit = computed(() => selected.value.length > 0);
</script>

<template>
    <Head :title="`Kirim Proposal — ${proposal.title}`" />

    <div class="min-h-full bg-sm-navy pb-10">
        <div class="mx-auto max-w-[880px] px-4 py-6 xl:px-0">
            <Link
                :href="proposalsIndex().url"
                class="inline-flex items-center gap-1.5 text-sm text-white/70 transition-colors hover:text-white"
            >
                <ArrowLeft class="size-4" />
                Kembali ke Proposal Saya
            </Link>

            <header class="mt-4">
                <p class="text-xs font-medium uppercase tracking-wide text-white/60">Proposal #{{ proposal.nomor }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-white sm:text-[28px]">{{ proposal.title }}</h1>
                <p class="mt-1 text-sm text-white/70">
                    {{ proposal.kategori ?? '-' }}
                </p>
            </header>

            <section class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 class="text-[15px] font-semibold text-sm-navy">Pilih Sponsor</h2>
                        <p class="mt-1 text-xs text-gray-500">
                            Kirim proposal ini ke sponsor terpilih. Sponsor yang belum pernah dikirimi bisa dipilih.
                        </p>
                    </div>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari nama sponsor atau industri..."
                        class="h-9 w-full rounded-lg border border-gray-200 px-3 text-[13px] text-sm-navy placeholder:text-gray-400 sm:w-72"
                    />
                </header>

                <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-5 py-2.5">
                    <button
                        type="button"
                        class="rounded-lg px-2 py-1 text-[12px] font-medium text-sm-navy transition-colors hover:bg-gray-50"
                        @click="selectAll"
                    >
                        Pilih semua
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-2 py-1 text-[12px] font-medium text-gray-500 transition-colors hover:bg-gray-50"
                        @click="clearAll"
                    >
                        Kosongkan
                    </button>
                    <span class="ml-auto text-[11px] text-gray-400">{{ selected.length }} sponsor dipilih</span>
                </div>

                <ul class="divide-y divide-gray-100">
                    <li
                        v-for="sponsor in filtered"
                        :key="sponsor.id"
                        class="flex items-center gap-3 px-5 py-3"
                        :class="sponsor.alreadySent ? 'opacity-60' : 'cursor-pointer'"
                        @click="toggle(sponsor)"
                    >
                        <input
                            type="checkbox"
                            :checked="selected.includes(sponsor.id) || sponsor.alreadySent"
                            :disabled="sponsor.alreadySent"
                            class="size-4 rounded border-gray-300 text-sm-navy focus:ring-sm-navy"
                            @click.stop
                            @change="toggle(sponsor)"
                        />
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-sm-card text-xs font-semibold text-white"
                        >
                            {{ sponsor.name.slice(0, 2).toUpperCase() }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[13px] font-medium text-sm-navy">{{ sponsor.name }}</p>
                            <p class="text-[11px] text-gray-500">{{ sponsor.industry ?? sponsor.email }}</p>
                        </div>
                        <span
                            v-if="sponsor.alreadySent"
                            class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700"
                        >
                            Sudah dikirim
                        </span>
                    </li>
                    <li v-if="filtered.length === 0" class="px-5 py-6 text-center text-[12px] text-gray-400">
                        Tidak ada sponsor yang cocok dengan pencarian.
                    </li>
                </ul>

                <footer class="flex justify-end gap-2 border-t border-gray-100 px-5 py-3">
                    <Link
                        :href="proposalsIndex().url"
                        class="h-9 rounded-lg border border-gray-200 px-3 text-[13px] font-medium text-gray-600 transition-colors hover:bg-gray-50"
                    >
                        Batal
                    </Link>
                    <form
                        method="post"
                        :action="ProposalController.send(proposal.id).url"
                        @submit="
                            (e) => {
                                if (!canSubmit) {
                                    e.preventDefault();
                                }
                            }
                        "
                    >
                        <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                        <template v-for="id in selected" :key="id">
                            <input type="hidden" name="sponsor_ids[]" :value="id" />
                        </template>
                        <button
                            type="submit"
                            :disabled="!canSubmit"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-sm-red px-4 text-[13px] font-medium text-white transition-colors hover:bg-sm-red-dark disabled:opacity-60"
                        >
                            <Send class="size-3.5" />
                            Kirim ke {{ selected.length }} Sponsor
                        </button>
                    </form>
                </footer>
            </section>
        </div>
    </div>
</template>
