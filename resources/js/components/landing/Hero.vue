<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

// Chip melayang di sekitar foto hero. Posisi (persen) disesuaikan dengan Figma.
const chips = [
    { title: 'AI Matching', note: 'Efisien · Relevan', icon: 'vector_ai.svg', class: 'left-[42%] top-[4%]' },
    { title: 'Event', note: 'Festival · Seminar · Expo', icon: 'vector_event.svg', class: 'left-[6%] top-[14%]' },
    { title: 'Perusahaan', note: 'Brand · Partnership', icon: 'vector_perusahaan.svg', class: 'right-[-2%] top-[24%]' },
];
</script>

<template>
    <section id="beranda" class="relative overflow-hidden">
        <div class="mx-auto grid max-w-[1440px] items-center gap-10 px-5 pb-0 pt-16 lg:grid-cols-[1fr_1.1fr] lg:px-10 lg:pt-24">
            <!-- Teks -->
            <div class="pb-16 lg:pb-32">
                <h1 class="font-poppins text-6xl font-bold leading-none tracking-tight lg:text-[84px]">
                    <span class="text-sm-red">Sponsor</span>Match
                </h1>

                <p class="mt-8 max-w-md font-poppins text-3xl leading-snug lg:text-[32px]">
                    Sudah Punya Acara, Tapi Masih Kesulitan Cari <span class="text-sm-amber">Sponsor</span>?
                </p>

                <p class="mt-8 max-w-md font-poppins text-sm leading-7 text-white/90">
                    SponsorMatch membantu Anda menemukan perusahaan yang relevan melalui pencocokan berbasis AI,
                    sehingga peluang kerja sama lebih tepat sasaran.
                </p>

                <Link
                    href="/register"
                    class="mt-10 inline-flex items-center gap-2 rounded-lg bg-sm-red px-6 py-3 font-poppins text-lg font-medium transition-colors hover:bg-sm-red-dark"
                >
                    Mulai Sekarang
                    <img src="/images/landing/vector_next_kanan_putih.svg" alt="" class="h-4 w-4" />
                </Link>
            </div>

            <!-- Foto + glow + chip -->
            <div class="relative mx-auto w-full max-w-[867px] self-end">
                <!-- Glow peach (pengganti sementara node Figma 174:106) -->
                <div
                    class="absolute left-[18%] top-[8%] -z-0 aspect-square w-[78%] rounded-full bg-[radial-gradient(circle_at_50%_30%,#ffa586_0%,#d9667a_55%,#541a2e_100%)] opacity-90"
                    aria-hidden="true"
                />
                <img
                    src="/images/landing/gambar_landing_page_desk_1.png"
                    alt="Event organizer dan perwakilan perusahaan berdiskusi"
                    class="relative z-10 block w-full"
                />

                <div
                    v-for="chip in chips"
                    :key="chip.title"
                    :class="chip.class"
                    class="absolute z-20 hidden items-center gap-2 rounded-md bg-white px-2.5 py-1.5 text-sm-navy shadow-lg md:flex"
                >
                    <img :src="`/images/landing/${chip.icon}`" alt="" class="h-6 w-6" />
                    <div class="font-poppins leading-tight">
                        <p class="text-[11px] font-semibold">{{ chip.title }}</p>
                        <p class="text-[8px] text-sm-navy/70">{{ chip.note }}</p>
                    </div>
                    <span class="ml-2 text-sm leading-none" aria-hidden="true">&rarr;</span>
                </div>
            </div>
        </div>
    </section>
</template>