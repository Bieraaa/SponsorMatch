<script setup lang="ts">
import { useReveal } from '@/composables/useReveal';

// Seksi "Tentang" (#tentang): 4 keunggulan utama SponsorMatch.
const features = [
    {
        title: 'AI Matching',
        icon: 'vector_ai_landing_page_2.svg',
        desc: 'Temukan mitra sponsor yang relevan berkat pencocokan berbasis AI yang menganalisis kebutuhan acara dan campaign perusahaan.',
    },
    {
        title: 'Tepat Sasaran',
        icon: 'vector_tepat_sasaran.svg',
        desc: 'Rekomendasi disusun dari karakteristik acara, target audiens, dan budget—bukan sekadar tebak-tebakan.',
    },
    {
        title: 'Satu Platform',
        icon: 'Vector_satu_platform.svg',
        desc: 'Cari sponsor, kirim proposal, negosiasi, hingga laporan pasca-acara—semuanya dalam satu tempat.',
    },
    {
        title: 'Efisien',
        icon: 'vetor_efisien.svg',
        desc: 'Hemat waktu dan tenaga tanpa perlu menghubungi perusahaan satu per satu secara manual.',
    },
];

// Animasi reveal: aktif saat seksi masuk viewport, reset saat keluar—jadi animasi berulang tiap scroll.
const { el: sectionEl, revealed } = useReveal(0.25);
</script>

<template>
    <section
        id="tentang"
        ref="sectionEl"
        class="flex min-h-svh snap-start items-center scroll-mt-[68px] bg-sm-navy font-jakarta text-white"
    >
        <div class="mx-auto grid w-full max-w-[1440px] items-center gap-12 px-5 py-20 lg:grid-cols-[0.9fr_1.1fr] lg:px-10 lg:py-28">
            <!-- Foto + glow -->
            <div
                class="relative mx-auto w-full max-w-[398px] transition-all duration-700 ease-out"
                :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <div
                    class="absolute left-[10%] top-[10%] -z-0 aspect-square w-[85%] rounded-full bg-[radial-gradient(circle_at_50%_30%,#ffa586_0%,#d9667a_55%,#541a2e_100%)] opacity-90"
                    aria-hidden="true"
                />
                <img
                    src="/assets/gambar_landing_page_desk_2.png"
                    alt="Dua orang berjabat tangan setelah mencapai kesepakatan sponsorship"
                    class="relative z-10 block w-full"
                />
            </div>

            <!-- Teks + daftar fitur -->
            <div
                class="transition-all delay-150 duration-700 ease-out"
                :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <p class="font-poppins text-sm font-semibold uppercase tracking-widest text-sm-amber">
                    Tentang SponsorMatch
                </p>
                <h2 class="mt-3 font-poppins text-4xl font-bold leading-tight lg:text-5xl">
                    Kenapa Cari Sponsor <span class="text-sm-red">Masih Ribet</span>?
                </h2>
                <p class="mt-5 max-w-xl text-base leading-7 text-white/80">
                    SponsorMatch hadir untuk mempertemukan event organizer dengan perusahaan yang benar-benar relevan—
                    tanpa perlu bolak-balik kirim proposal secara acak.
                </p>

                <div class="mt-10 grid gap-6 sm:grid-cols-2">
                    <div
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        class="rounded-xl bg-sm-card p-6 ring-1 ring-white/5 transition-all duration-500 ease-out"
                        :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
                        :style="{ transitionDelay: `${200 + index * 100}ms` }"
                    >
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-sm-navy">
                            <img :src="`/assets/${feature.icon}`" alt="" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-4 font-poppins text-lg font-semibold">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-white/70">{{ feature.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
