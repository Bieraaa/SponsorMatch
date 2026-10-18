```vue
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const chips = [
    {
        title: 'AI Matching',
        note: 'Efisien · Relevan',
        icon: 'vector_ai.svg',
        class: 'left-[40%] top-[3%]',
    },
    {
        title: 'Event',
        note: 'Festival · Seminar · Expo',
        icon: 'vector_event.svg',
        class: 'left-[-2%] top-[28%]',
    },
    {
        title: 'Perusahaan',
        note: 'Brand · Partnership',
        icon: 'vector_perusahaan.svg',
        class: 'right-[-2%] top-[42%]',
    },
];
</script>

<template>
    <section
        id="beranda"
        class="relative isolate flex min-h-svh snap-start items-center overflow-hidden"
    >
        <div
            class="mx-auto grid w-full max-w-[1800px] grid-cols-1 items-center gap-12 px-5 pb-12 pt-24 sm:px-8 md:gap-14 lg:grid-cols-[0.85fr_1.15fr] lg:gap-8 lg:px-10 lg:py-20 xl:gap-12 xl:px-14 2xl:px-16"
        >
            <!-- KONTEN TEKS -->
            <div
                class="relative z-20 flex flex-col items-start lg:py-8"
            >
                <h1
                    class="font-poppins text-5xl font-bold leading-[1.05] tracking-tight sm:text-6xl md:text-7xl lg:text-[clamp(3.5rem,5.2vw,5.5rem)] xl:text-[88px]"
                >
                    <span class="text-sm-red">Sponsor</span>Match
                </h1>

                <p
                    class="mt-6 max-w-xl font-poppins text-2xl font-semibold leading-snug sm:text-3xl lg:mt-8 lg:text-[clamp(1.75rem,2.4vw,2rem)]"
                >
                    Sudah Punya Acara, Tapi Masih Kesulitan Cari
                    <span class="text-sm-amber">Sponsor</span>?
                </p>

                <p
                    class="mt-5 max-w-lg font-poppins text-sm leading-7 text-white/80 sm:text-base lg:mt-6 lg:text-lg lg:leading-8"
                >
                    SponsorMatch membantu Anda menemukan perusahaan yang
                    relevan melalui pencocokan berbasis AI, sehingga peluang
                    kerja sama lebih tepat sasaran.
                </p>

                <Link
                    href="/register"
                    class="mt-8 inline-flex items-center gap-3 rounded-xl bg-sm-red px-6 py-3.5 font-poppins text-base font-semibold text-white shadow-lg shadow-sm-red/20 transition duration-300 hover:-translate-y-1 hover:bg-sm-red-dark sm:text-lg"
                >
                    Mulai Sekarang
                    <img
                        src="/assets/vector_next_kanan_putih.svg"
                        alt=""
                        class="h-5 w-5 shrink-0 transition-transform duration-300 group-hover:translate-x-1"
                    />
                </Link>
            </div>

            <!-- VISUAL HERO -->
            <div
                class="relative mx-auto w-full min-w-0 max-w-[10000px] self-center lg:ml-auto lg:mr-0"
            >
                <!-- Glow di belakang ilustrasi (mengikuti rasio gambar 867x849) -->
                <div
                    class="pointer-events-none absolute left-[6%] top-[4%] z-0 aspect-[867/849] w-[88%] rounded-full bg-[radial-gradient(circle_at_45%_35%,#FFA586_0%,#242F49_95%)]"
                    aria-hidden="true"
                />

                <!-- Ilustrasi utama -->
                <img
                    src="/assets/gambar_landing_page_desk_1.png"
                    alt="Event organizer dan perwakilan perusahaan berdiskusi"
                    class="relative z-10 block h-auto w-full origin-center object-contain lg:scale-105 xl:scale-110"
                />

                <!-- Chip informasi -->
                <div
                    v-for="chip in chips"
                    :key="chip.title"
                    :class="chip.class"
                    class="absolute z-20 hidden items-center gap-2 rounded-lg border border-white/70 bg-white/95 px-2.5 py-1.5 shadow-[0_8px_30px_rgba(0,0,0,0.16)] backdrop-blur-sm md:flex lg:gap-2.5 lg:px-3 lg:py-2"
                >
                    <span
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sm-peach lg:h-8 lg:w-8"
                    >
                        <img
                            :src="`/assets/${chip.icon}`"
                            alt=""
                            class="h-4 w-4 lg:h-5 lg:w-5"
                        />
                    </span>

                    <div class="min-w-0 font-poppins leading-tight">
                        <p
                            class="whitespace-nowrap text-[10px] font-semibold text-sm-navy lg:text-[11px]"
                        >
                            {{ chip.title }}
                        </p>
                        <p
                            class="mt-0.5 whitespace-nowrap text-[8px] text-sm-navy/65 lg:text-[9px]"
                        >
                            {{ chip.note }}
                        </p>
                    </div>

                    <span
                        class="ml-0.5 text-sm leading-none text-sm-navy"
                        aria-hidden="true"
                    >
                        &rarr;
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
```