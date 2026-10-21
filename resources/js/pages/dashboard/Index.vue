<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Eye,
    FileText,
    Handshake,
    Heart,
    MessageCircle,
    Megaphone,
    Send,
    ThumbsDown,
    ThumbsUp,
    Upload,
} from '@lucide/vue';

/**
 * Blok A — Dashboard EO (Figma frame "Dashboard EO", export 19 Okt 2026).
 *
 * Semua ukuran/warna dari analisis piksel export 1440x814:
 * - Latar konten navy #161E2F.
 * - Hero sapaan: gradient merah #B51A2B → #630E1E (kiri→kanan), radius 24,
 *   "Halo, {nama}" putih ~40px + subteks putih 85%.
 * - 4 kartu statistik putih (Proposal diterima/ditolak/terkirim/dibalas).
 * - "Rekomendasi Sponsor": 3 kartu putih — nama, badge "% Cocok"
 *   (hijau/amber/merah), Rentang Dukungan, Keterangan, tombol
 *   "Lihat Sekarang".
 * - Kolom kanan: "Aksi Cepat" (panel #EDEDED, 4 tombol putih 2x2),
 *   kartu gradient "Upgrade ke Premium" + tombol amber "Lihat Paket",
 *   "Postingan Terbaru" (4 kartu ringkas + 3 counter).
 *
 * Data statistik/sponsor/postingan = dummy sesuai desain; diisi dari
 * database saat backend Blok B (proposal, postingan) sudah ada.
 * Role sponsor: placeholder sampai Figma "Dashboard Sponsor" tersedia.
 */
const page = usePage();
const user = computed(() => page.props.auth.user);
const isEo = computed(() => user.value?.role !== 'sponsor');
const firstName = computed(() => (user.value?.name ?? 'Pengguna').split(' ')[0]);

const stats = [
    { label: 'Proposal diterima', value: 5, icon: ThumbsUp, tint: 'text-green-600' },
    { label: 'Proposal ditolak', value: 2, icon: ThumbsDown, tint: 'text-sm-red' },
    { label: 'Proposal Terkirim', value: 12, icon: Send, tint: 'text-sm-card' },
    { label: 'Proposal dibalas', value: 3, icon: FileText, tint: 'text-sm-amber' },
];

const sponsors = [
    {
        name: 'PT Astra International',
        match: 92,
        range: 'Rp50.000.000 - Rp200.000.000',
        note: 'Tertarik melakukan dukungan terhadap event yang berfokus pada pengembangan teknologi otomotif, dan pengembangan SDM.',
    },
    {
        name: 'BCD',
        match: 60,
        range: 'Rp80.000.000 - Rp300.000.000',
        note: 'Tertarik melakukan dukungan terhadap program edukasi dan pengembangan komunitas',
    },
    {
        name: 'Telkom Indonesia',
        match: 20,
        range: 'Rp10.000.000 - Rp40.000.000',
        note: 'Tertarik melakukan dukungan terhadap program pengembangan telekomunikasi dan digitalisasi terhadap sebuah komunitas',
    },
];

const matchTint = (match: number) =>
    match >= 80
        ? 'bg-green-100 text-green-700'
        : match >= 50
          ? 'bg-amber-100 text-amber-700'
          : 'bg-red-100 text-sm-red';

const quickActions = [
    { label: 'Buat laporan', icon: FileText },
    { label: 'Upload Proposal Event', icon: Upload },
    { label: 'Buat Postingan', icon: Megaphone },
    { label: 'Kelola Penawaran', icon: Handshake },
];

const posts = [
    { title: 'Keseruan dibalik layar', event: 'ITK EXPO 2026', meta: 'Event · 3 hari lalu', views: 124, likes: 12, comments: 0 },
    { title: 'Kolaborasi Pendidikan demi masa depan', event: 'Beasiswa Anak Bangsa', meta: 'Perusahaan · 5 hari lalu', views: 200, likes: 150, comments: 45 },
    { title: 'Bank Sendiri dukung kerja sama Teknologi', event: 'FinTech Day', meta: 'Sponsor · 1 hari lalu', views: 250, likes: 150, comments: 80 },
    { title: 'Musik Kita Gas mencari sponsorship', event: 'Music Fest 2026', meta: 'Event · 1 hari lalu', views: 250, likes: 150, comments: 80 },
];
</script>

<template>
    <Head title="Dashboard" />

    <!-- Latar navy penuh (Figma: konten #161E2F) -->
    <div class="min-h-full bg-sm-navy pb-10">
        <!-- ===================== EO ===================== -->
        <div
            v-if="isEo"
            class="mx-auto grid max-w-[1180px] gap-6 px-4 py-6 xl:grid-cols-[1fr_294px] xl:px-0"
        >
            <!-- ---------- Kolom kiri ---------- -->
            <div class="min-w-0 space-y-8">
                <!-- Hero sapaan (gradient merah, radius 24) -->
                <div
                    class="rounded-3xl bg-gradient-to-r from-sm-red to-[#630E1E] px-8 py-9 text-white"
                >
                    <h1 class="text-3xl font-semibold lg:text-4xl">
                        Halo, {{ firstName }} 👋
                    </h1>
                    <p class="mt-3 max-w-xl text-base text-white/85 lg:text-lg">
                        Menemukan sponsor yang tepat. SponsorMatch siap
                        membantu menemukan mitra terbaik kamu.
                    </p>
                </div>

                <!-- 4 statistik proposal (kartu putih) -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="rounded-2xl bg-white p-5"
                    >
                        <p class="text-sm text-sm-card/70">{{ stat.label }}</p>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="text-4xl font-bold text-sm-navy">{{
                                stat.value
                            }}</span>
                            <component
                                :is="stat.icon"
                                class="h-6 w-6"
                                :class="stat.tint"
                            />
                        </div>
                    </div>
                </div>

                <!-- Rekomendasi Sponsor -->
                <section>
                    <h2 class="text-xl font-semibold text-white lg:text-2xl">
                        Rekomendasi Sponsor
                    </h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <article
                            v-for="sponsor in sponsors"
                            :key="sponsor.name"
                            class="flex flex-col rounded-2xl bg-white p-5"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sm-card text-sm font-semibold text-white"
                                >
                                    {{ sponsor.name.charAt(0) }}
                                </span>
                                <h3 class="font-semibold text-sm-navy">
                                    {{ sponsor.name }}
                                </h3>
                            </div>

                            <span
                                class="mt-4 w-fit rounded-full px-3 py-1 text-xs font-semibold"
                                :class="matchTint(sponsor.match)"
                            >
                                {{ sponsor.match }}% Cocok
                            </span>

                            <p class="mt-4 text-xs font-semibold text-sm-card">
                                Rentang Dukungan:
                            </p>
                            <p class="mt-1 text-sm text-sm-card/80">
                                {{ sponsor.range }}
                            </p>

                            <p class="mt-3 text-xs font-semibold text-sm-card">
                                Keterangan
                            </p>
                            <p class="mt-1 flex-1 text-sm leading-relaxed text-sm-card/80">
                                {{ sponsor.note }}
                            </p>

                            <button
                                type="button"
                                class="mt-5 w-full rounded-xl bg-sm-red py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sm-red-dark"
                            >
                                Lihat Sekarang
                            </button>
                        </article>
                    </div>
                </section>
            </div>

            <!-- ---------- Kolom kanan ---------- -->
            <div class="min-w-0 space-y-6">
                <!-- Aksi Cepat (panel abu #EDEDED, 4 tombol putih 2x2) -->
                <section>
                    <h3 class="text-sm font-semibold text-white">
                        Aksi Cepat
                    </h3>
                    <div
                        class="mt-3 grid grid-cols-2 gap-3 rounded-2xl bg-[#EDEDED] p-3"
                    >
                        <a
                            v-for="action in quickActions"
                            :key="action.label"
                            href="#"
                            class="flex flex-col items-start gap-2 rounded-xl bg-white p-4 text-left text-xs font-medium text-sm-card shadow-[0_1px_3px_rgba(22,30,47,0.08)] hover:shadow-[0_2px_6px_rgba(22,30,47,0.14)]"
                            :title="`${action.label} — segera hadir`"
                            @click.prevent
                        >
                            <component
                                :is="action.icon"
                                class="h-5 w-5 text-sm-red"
                            />
                            {{ action.label }}
                        </a>
                    </div>
                </section>

                <!-- Upgrade ke Premium (gradient merah vertikal + tombol amber) -->
                <section
                    class="rounded-2xl bg-gradient-to-b from-sm-red to-[#5D0F19] p-6 text-white"
                >
                    <h3 class="text-lg font-semibold">Upgrade ke Premium</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/85">
                        Dapatkan lebih banyak fitur yang memudahkanmu dalam
                        mengelola proposal
                    </p>
                    <a
                        href="/#harga"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-sm-amber py-2.5 text-sm font-semibold text-sm-card transition-opacity hover:opacity-90"
                    >
                        Lihat Paket
                    </a>
                </section>

                <!-- Postingan Terbaru -->
                <section class="rounded-2xl bg-white p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-sm-navy">
                            Postingan Terbaru
                        </h3>
                        <a
                            href="#"
                            class="text-sm font-medium text-sm-red hover:underline"
                            @click.prevent
                        >
                            Lihat postingan
                        </a>
                    </div>

                    <ul class="mt-4 divide-y divide-gray-100">
                        <li
                            v-for="post in posts"
                            :key="post.title"
                            class="py-3 first:pt-0 last:pb-0"
                        >
                            <p class="text-sm font-semibold text-sm-navy">
                                {{ post.title }}
                            </p>
                            <p class="mt-0.5 text-xs text-sm-card/70">
                                {{ post.event }}
                            </p>
                            <p class="mt-0.5 text-xs text-sm-card/50">
                                {{ post.meta }}
                            </p>
                            <div
                                class="mt-2 flex items-center gap-4 text-xs text-sm-card/70"
                            >
                                <span class="flex items-center gap-1">
                                    <Eye class="h-3.5 w-3.5" />{{ post.views }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Heart class="h-3.5 w-3.5" />{{ post.likes }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <MessageCircle class="h-3.5 w-3.5" />{{
                                        post.comments
                                    }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>
        </div>

        <!-- ===================== SPONSOR ===================== -->
        <div v-else class="mx-auto max-w-[900px] px-4 py-6">
            <div
                class="rounded-3xl bg-gradient-to-r from-sm-red to-[#630E1E] px-8 py-9 text-white"
            >
                <h1 class="text-3xl font-semibold lg:text-4xl">
                    Halo, {{ firstName }} 👋
                </h1>
                <p class="mt-3 text-base text-white/85 lg:text-lg">
                    Selamat datang di SponsorMatch. Temukan event yang paling
                    cocok untuk perusahaanmu.
                </p>
            </div>
            <div class="mt-6 rounded-2xl bg-white p-8 text-center">
                <h2 class="text-lg font-semibold text-sm-navy">
                    Dashboard Perusahaan sedang disiapkan 🚧
                </h2>
                <p class="mt-2 text-sm text-sm-card/70">
                    Fitur campaign, rekomendasi event, dan penawaran akan hadir
                    pada blok pengembangan berikutnya.
                </p>
            </div>
        </div>
    </div>
</template>
