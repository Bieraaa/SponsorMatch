<script setup lang="ts">
import { useReveal } from '@/composables/useReveal';
import { Link } from '@inertiajs/vue3';

// Seksi "Langganan" (#langganan): paket Reguler vs Premium.
// Manfaat premium merujuk pada SRS bagian batasan produk (poin 3, 4, 8, 9).
const plans = [
    {
        name: 'Reguler',
        price: 'Rp0',
        period: '/bulan',
        note: 'Cocok untuk mulai mencari sponsor',
        checkIcon: 'vector_check_list_biru.svg',
        highlight: false,
        benefits: [
            'Unggah proposal dengan kuota ekstraksi AI terbatas',
            'Rekomendasi perusahaan yang terbatas',
            'Pencarian & filter dasar',
            'Kirim proposal serta terima/tolak penawaran',
            'Statistik dasar proposal & campaign',
        ],
    },
    {
        name: 'Premium',
        price: 'Rp99rb',
        period: '/bulan',
        note: 'Untuk EO & perusahaan yang serius',
        checkIcon: 'Vector_check_list_merah.svg',
        highlight: true,
        benefits: [
            'Ekstraksi proposal tanpa batas',
            'Rekomendasi AI dengan cakupan lebih luas',
            'Filter lanjutan & simpan daftar mitra potensial',
            'Negosiasi lanjutan dengan counter-offer',
            'Chat antar pengguna & notifikasi proaktif',
            'Insight siapa yang membuka proposal/campaign Anda',
        ],
    },
];

// Animasi reveal: aktif saat masuk viewport, reset saat keluar (animasi berulang).
const { el: sectionEl, revealed } = useReveal(0.25);
</script>

<template>
    <section
        id="langganan"
        ref="sectionEl"
        class="flex min-h-svh snap-start items-center scroll-mt-[68px] bg-sm-navy font-jakarta text-white"
    >
        <div class="mx-auto w-full max-w-[1440px] px-5 py-20 lg:px-10 lg:py-28">
            <div
                class="mx-auto max-w-2xl text-center transition-all duration-700 ease-out"
                :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <p class="font-poppins text-sm font-semibold uppercase tracking-widest text-sm-amber">
                    Langganan
                </p>
                <h2 class="mt-3 font-poppins text-4xl font-bold leading-tight lg:text-5xl">
                    Pilih Paket Sesuai <span class="text-sm-red">Kebutuhan</span> Anda
                </h2>
                <p class="mt-5 text-base leading-7 text-white/80">
                    Mulai gratis, kapan saja tingkatkan ke Premium untuk fitur lengkap dan cakupan rekomendasi yang
                    lebih luas.
                </p>
            </div>

            <div class="mx-auto mt-14 grid max-w-4xl gap-8 md:grid-cols-2">
                <div
                    v-for="(plan, index) in plans"
                    :key="plan.name"
                    class="relative flex flex-col rounded-2xl bg-sm-card p-8 ring-1 transition-all duration-500 ease-out"
                    :class="[
                        plan.highlight ? 'ring-sm-red' : 'ring-white/10',
                        revealed ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0',
                    ]"
                    :style="{ transitionDelay: `${200 + index * 120}ms` }"
                >
                    <span
                        v-if="plan.highlight"
                        class="absolute -top-3 left-8 rounded-full bg-sm-red px-3 py-1 font-poppins text-xs font-semibold text-white"
                    >
                        Paling Lengkap
                    </span>

                    <h3 class="font-poppins text-xl font-semibold">{{ plan.name }}</h3>
                    <p class="mt-1 text-sm text-white/60">{{ plan.note }}</p>

                    <p class="mt-6 font-poppins text-4xl font-bold">
                        {{ plan.price }}<span class="text-base font-medium text-white/60">{{ plan.period }}</span>
                    </p>

                    <ul class="mt-8 flex-1 space-y-4">
                        <li v-for="benefit in plan.benefits" :key="benefit" class="flex items-start gap-3">
                            <img :src="`/assets/${plan.checkIcon}`" alt="" class="mt-1 h-4 w-5 shrink-0" />
                            <span class="text-sm leading-6 text-white/85">{{ benefit }}</span>
                        </li>
                    </ul>

                    <Link
                        href="/register"
                        class="mt-10 inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 font-poppins text-base font-medium transition-colors"
                        :class="
                            plan.highlight
                                ? 'bg-sm-red text-white hover:bg-sm-red-dark'
                                : 'bg-white text-sm-navy hover:bg-white/90'
                        "
                    >
                        {{ plan.highlight ? 'Langganan Sekarang' : 'Mulai Gratis' }}
                        <img src="/assets/vector_next_kanan_putih.svg" alt="" class="h-3.5 w-3.5" :class="plan.highlight ? '' : 'hidden'" />
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
