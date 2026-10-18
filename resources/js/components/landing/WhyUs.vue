<script setup lang="ts">
import { useReveal } from '@/composables/useReveal';

/**
 * ============================================================================
 * SEKSI "TENTANG" (#tentang) — "Kenapa harus SponsorMatch?"
 * ============================================================================
 * Struktur halaman:
 *   [section]        -> satu layar penuh (min-h-svh) + scroll-snap (snap-start)
 *     [kiri]         -> foto orang + rectangle gradien + tanda tanya (dekorasi)
 *     [kanan]        -> judul + paragraf + grid 4 kartu keunggulan
 */

// Daftar4 kartu keunggulan.
// Dipetakan (v-for) menjadi kartu di bawah, jadi menambah item = cukup tambah
// objek di array ini tanpa menyentuh HTML.
const features = [
    {
        title: 'AI Matching', // judul kartu (sejajar kanan logo)
        icon: 'vector_ai_landing_page_2.svg', // nama file di folder public/assets/
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

// Animasi reveal berbasis scroll.
// - `sectionEl` dipasang ke <section> sebagai ref pengamatan
// - `revealed` = true saat >= 25% seksi terlihat, dan KEMBALI false saat keluar
//   viewport → animasi berulang setiap kali user scroll masuk/keluar seksi ini.
// Dipakai lewat :class di bawah: revealed ? posisi-akhir : posisi-awal.
const { el: sectionEl, revealed } = useReveal(0.25);
</script>

<template>
    <!--
        <section> =1 seksi scroll-snap:
        - id="tentang"       -> target link navigasi (#tentang)
        - ref="sectionEl"    -> elemen yang diamati IntersectionObserver (useReveal)
        - min-h-svh          -> minimal setinggi layar, supaya memenuhi1 layar penuh
        - snap-start         -> scroll-snap: seksi ini "mengunci" di awal layar
        - scroll-mt-[68px]   -> kompensasi tinggi navbar sticky saat lompat anchor
        - bg-sm-navy         -> latar navy gelap (token warna di app.css)
    -->
    <section
        id="tentang"
        ref="sectionEl"
        class="flex min-h-svh snap-start items-center scroll-mt-[68px] bg-sm-navy font-jakarta text-white"
    >
        <!--
            Kontainer isi: max-w-[1440px] = lebar maks desain.
            flex-col lg:flex-row = di mobile bertumpuk vertikal, di layar lebar sejajar.
            Kolom gambar SHUT shrink-0 (lebarnya pas selebar gambar, tidak ikut melebar),
            kolom teks flex-1 (mengambil semua sisa ruang di sebelahnya).
        -->
        <div class="mx-auto flex w-full max-w-[1440px] flex-col items-center gap-12 px-5 py-20 lg:flex-row lg:gap-16 lg:px-10 lg:py-28">
            <!--
                KOLOM KIRI — Foto + dekorasi.
                position:relative wajib: agar anak-anaknya (rectangle, tanda tanya)
                bisa diposisikan absolute terhadap kotak ini.
                transition + :class = animasi naik+muncul saat seksi terlihat.
                shrink-0 = lebar kotak tidak menyusut (pas max-w-[398px]).
            -->
            <div
                class="relative w-full max-w-[398px] shrink-0 transition-all duration-700 ease-out"
                :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <!--
                    Rectangle gradien di BELAKANG foto:
                    - left/right -6% -> sedikit lebih lebar dari foto (menonjol di2 sisi)
                    - top-[3%] h-[74%] -> puncak tidak melewati dahi, menutupi ~3/4 foto
                    - rounded-tl/tr-[80px] -> sudut atas kiri-kanan membulat80px
                    - gradien 180deg: merah #B51A2B (0%) -> ungu #661C2D (33%) -> navy #161E2F (100%)
                    Anak pertama tanpa z → otomatis di bawah foto (z-10).
                -->
                <div
                    class="absolute left-[-6%] right-[-6%] top-[3%] h-[74%] rounded-tl-[80px] rounded-tr-[80px] bg-[linear-gradient(180deg,#B51A2B_0%,#661C2D_33%,#161E2F_100%)]"
                    aria-hidden="true"
                />

                <!--
                    Tanda tanya dekoratif: serong kanan atas kepala orang (orang bingung).
                    - z-20 -> di atas foto (foto z-10) dan rectangle
                    - h-14 -> tinggi56px (lebar ikut rasio SVG via w-auto)
                    - top-[-2%] -> sedikit keluar tepi atas foto (wajar untuk hiasan)
                    - alt="" + aria-hidden -> murni hiasan, diabaikan screen reader
                -->
                <img
                    src="/assets/vector_tanda_tanya.svg"
                    alt=""
                    aria-hidden="true"
                    class="absolute left-[58%] top-[-2%] z-20 h-14 w-auto"
                />

                <!--
                    Foto utama. z-10 supaya DI ATAS rectangle, tapi di bawah tanda tanya (z-20).
                    w-full h-auto → mengikuti lebar kolom (maks398px), tinggi otomatis.
                -->
                <img
                    src="/assets/gambar_landing_page_desk_2.png"
                    alt="Dua orang berjabat tangan setelah mencapai kesepakatan sponsorship"
                    class="relative z-10 block w-full"
                />
            </div>

            <!--
                KOLOM KANAN — Teks +4 kartu fitur.
                flex-1 min-w-0 → memakan sisa ruang, dan boleh menyusut tanpa
                mendorong layout (min-w-0 mencegah teks panjang "melompat" keluar).
                delay-150 → animasi ini mulai150ms setelah kolom foto (efek berurutan).
            -->
            <div
                class="w-full min-w-0 flex-1 transition-all delay-150 duration-700 ease-out"
                :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <!-- Eyebrow: teks kecil kapital berjarak lebar di atas judul -->
                <p class="font-poppins text-sm font-semibold uppercase tracking-widest text-sm-amber">
                    Tentang SponsorMatch
                </p>

                <!-- Judul seksi. "SponsorMatch" diwarnai merah (token sm-red) -->
                <h2 class="mt-3 font-poppins text-4xl font-bold leading-tight lg:text-5xl">
                    Kenapa harus <span class="text-sm-red">SponsorMatch</span>?
                </h2>

                <!--
                    Paragraf pengantar: TANPA max-w supaya ujung kanannya sejajar
                    dengan ujung grid kartu di bawahnya (lebar mengikuti kontainer).
                -->
                <p class="mt-5 text-base leading-7 text-white/80">
                    Mencari sponsor tidak harus dimulai dari menghubungi perusahaan satu per satu. SponsorMatch membantu mempertemukan event dengan perusahaan yang memiliki potensi kerja sama yang relevan.
                </p>

                <!--
                    Grid2 kolom kartu (sm:grid-cols-2 = mulai2 kolom di layar ≥640px).
                    gap-6 = jarak antar kartu24px.
                -->
                <div class="mt-10 grid gap-6 sm:grid-cols-2">
                    <!--
                        Satu kartu fitur, diulang sebanyak isi array `features`.
                        - :key → kunci unik Vue saat list berubah (performa)
                        - border-[0.5px] border-sm-amber → garis tepi amber #F59E0B setengah px
                        - bg-sm-card → latar kartu #242F49 (token di app.css)
                        - transitionDelay bertambah tiap kartu (200,300,400,500ms)
                        → kartu muncul berurutan satu per satu (efek domino)
                    -->
                    <div
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        class="rounded-xl border-[0.5px] border-sm-amber bg-sm-card p-6 transition-all duration-500 ease-out"
                        :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
                        :style="{ transitionDelay: `${200 + index * 100}ms` }"
                    >
                        <!-- Baris1: logo (di lingkaran putih) + judul sejajar kanannya -->
                        <div class="flex items-center gap-3">
                            <!--
                                Lingkaran putih48px sebagai "wadah" icon:
                                shrink-0 → tidak gepeng kalau judul panjang,
                                items-center justify-center → icon di tengah lingkaran.
                            -->
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white">
                                <!-- Icon hitam/navy, ukuran24px di dalam lingkaran48px -->
                                <img :src="`/assets/${feature.icon}`" alt="" class="h-6 w-6" />
                            </div>
                            <!-- Judul kartu: Poppins18px tebal, sejajar vertikal dengan logo -->
                            <h3 class="font-poppins text-lg font-semibold">{{ feature.title }}</h3>
                        </div>

                        <!-- Baris2: penjelasan di bawah baris logo+judul -->
                        <p class="mt-3 text-sm leading-6 text-white/70">{{ feature.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
