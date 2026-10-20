<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

/**
 * Halaman pendaftaran SponsorMatch — BLOK A (auth + role).
 *
 * Alur 2 langkah (sesuai Figma frame "Role pick"):
 *   Step 1 — ROLE PICK : pengguna memilih peran (Event Organizer / Perusahaan).
 *   Step 2 — FORM      : mengisi nama/email/password; role dikirim sebagai
 *                        <input type="hidden" name="role"> ke POST /register.
 *
 * Di backend, App\Actions\Fortify\CreateNewUser memvalidasi role wajib hanya
 * 'eo' | 'sponsor' lalu menyimpannya ke kolom users.role (lihat migration
 * 2026_10_18_000001_add_role_to_users_table.php).
 *
 * Halaman ini dirender TANPA AuthLayout (lihat app.ts → case 'auth/Register'
 * return null) karena desainnya full-screen navy #161E2F, bukan kartu terpusat
 * milik starter kit.
 *
 * Semua ukuran/warna di bawah diukur langsung dari export Figma "Role pick.png"
 * (1440×1024): kartu 500×550, border 5px, radius 24, tombol 445×58 r14, dll.
 */

type Role = 'eo' | 'sponsor';

const roles = [
    {
        value: 'eo' as Role,
        title: 'Event Organizer',
        subtitle: 'Untuk Komunitas & Penyelenggara Event',
        desc: 'Temukan dan kelola sponsor yang tepat untuk acara anda dengan bantuan AI. Dapatkan rekomendasi yang sesuai dengan kebutuhan acara anda.',
        features: [
            'Unggah proposal dan ekstraksi dengan AI',
            'Dapatkan rekomendasi yang sesuai',
            'Kelola dan tracking proposal',
            'Akses laporan dan statistik event',
        ],
        icon: '/assets/ikon_role_eo.png',
    },
    {
        value: 'sponsor' as Role,
        title: 'Perusahaan',
        subtitle: 'Untuk Brand & Corporate',
        desc: 'Temukan event yang sesuai dengan campaign anda dan bangun kolaborasi jangka panjang dengan komunitas dan penyelenggara event',
        features: [
            'Akses proposal event yang relevan',
            'Kelola Penawaran & Kerja Sama',
            'Pantau Pipeline dan Riwayat Kolaborasi',
            'Kelola Campaign',
        ],
        icon: '/assets/ikon_role_perusahaan.png',
    },
];

const step = ref<1 | 2>(1);
const role = ref<Role>('eo');
const current = computed(() => roles.find((r) => r.value === role.value)!);

/** Klik kartu = pilih peran; go = true langsung lanjut ke form (klik tombol). */
function pick(r: Role, go = false) {
    role.value = r;
    if (go) {
        step.value = 2;
    }
}

/** Style input gelap yang dipakai semua field di step 2. */
const inputClass =
    'h-[54px] rounded-[14px] border-[#4A5670] bg-sm-navy px-4 text-base text-white placeholder:text-white/45 focus-visible:border-sm-red focus-visible:ring-0';
</script>

<template>
    <Head title="Daftar" />

    <div class="min-h-screen bg-sm-navy text-white">
        <div class="mx-auto w-full max-w-[1102px] px-4 pb-14">
            <!-- ===================== STEP 1 — ROLE PICK ===================== -->
            <template v-if="step === 1">
                <div class="pt-[124px]">
                    <!--
                        Heading tengah 40px bold. "Sponsor" merah di dalam kata
                        SponsorMatch (dua warna dalam satu kata = pola brand).
                    -->
                    <h1 class="text-center text-[30px] font-bold leading-tight sm:text-[40px]">
                        Mulai Perjalanan di <span class="text-sm-red">Sponsor</span><span>Match</span>
                    </h1>
                    <!-- Sub-heading 2 baris; max-w memaksa wrap sebelum "SponsorMatch" seperti desain -->
                    <p class="mx-auto mt-[14px] max-w-[625px] text-center text-base leading-[30px]">
                        Pilih peran sesuai dengan kebutuhan anda untuk memperoleh pengalaman terbaik di SponsorMatch
                    </p>
                </div>

                <!-- Dua kartu peran — 500×550, border 5px, radius 24 (diukur dari Figma) -->
                <div class="mt-[64px] flex flex-col items-center gap-8 lg:flex-row lg:justify-center lg:gap-[70px]">
                    <div
                        v-for="r in roles"
                        :key="r.value"
                        role="button"
                        tabindex="0"
                        class="h-[550px] w-full max-w-[500px] shrink-0 cursor-pointer rounded-[24px] border-[5px] transition-colors duration-200"
                        :class="
                            role === r.value
                                ? 'border-sm-red bg-[linear-gradient(180deg,#541A2E_0%,#52192C_6%,#360B12_100%)]'
                                : 'border-[#384358] bg-[linear-gradient(180deg,#384358_0%,#232D46_8%,#192134_100%)] hover:border-[#4A5670]'
                        "
                        @click="pick(r.value)"
                        @keydown.enter.prevent="pick(r.value, true)"
                    >
                        <div class="flex h-full flex-col px-[22px] pt-[20px] pb-[34px]">
                            <!-- Baris judul: ikon peran (crop dari Figma, PNG transparan) + judul/subjudul -->
                            <div class="flex items-center gap-[13px]">
                                <img :src="r.icon" alt="" class="h-16 w-16 shrink-0 object-contain" />
                                <div class="min-w-0">
                                    <h3 class="text-[28px] font-bold leading-tight">{{ r.title }}</h3>
                                    <p class="mt-1.5 text-base">{{ r.subtitle }}</p>
                                </div>
                            </div>

                            <!-- Deskripsi peran 16px leading 30px -->
                            <p class="mt-[10px] text-base leading-[30px]">{{ r.desc }}</p>

                            <!--
                                Daftar fitur: lingkaran peach Ø40 + centang knockout
                                #161E2F — pola identik dengan manfaat di Pricing.vue.
                            -->
                            <ul class="mt-[10px] space-y-2">
                                <li v-for="f in r.features" :key="f" class="flex items-center gap-[13px]">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-sm-peach">
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="h-[18px] w-[18px]"
                                            fill="none"
                                            stroke="#161E2F"
                                            stroke-width="3"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="text-base">{{ f }}</span>
                                </li>
                            </ul>

                            <!-- Tombol full-width 58px r14 merah = pilih role + lanjut ke form -->
                            <button
                                type="button"
                                class="mt-auto h-[58px] w-full cursor-pointer rounded-[14px] bg-sm-red font-semibold text-white transition-colors hover:bg-[#C41F31]"
                                @click.stop="pick(r.value, true)"
                            >
                                Daftar Sekarang
                            </button>
                        </div>
                    </div>
                </div>

                <!-- "Kembali" ke landing page (link teks + panah kiri 18px, kiri-sejajar kartu) -->
                <Link
                    href="/"
                    class="mt-[52px] inline-flex items-center gap-[18px] text-base text-white transition-colors hover:text-sm-peach"
                >
                    <ArrowLeft class="h-[18px] w-[18px]" aria-hidden="true" />
                    Kembali
                </Link>
            </template>

            <!-- ===================== STEP 2 — FORM DAFTAR ===================== -->
            <template v-else>
                <button
                    type="button"
                    class="mt-[40px] inline-flex cursor-pointer items-center gap-[18px] text-base text-white transition-colors hover:text-sm-peach"
                    @click="step = 1"
                >
                    <ArrowLeft class="h-[18px] w-[18px]" aria-hidden="true" />
                    Kembali
                </button>

                <div
                    class="mx-auto mt-8 w-full max-w-[480px] rounded-[24px] border-[5px] border-[#384358] bg-[linear-gradient(180deg,#384358_0%,#232D46_8%,#192134_100%)] p-[34px]"
                >
                    <h2 class="text-center text-[28px] font-bold">Lengkapi Data Anda</h2>
                    <p class="mt-2 text-center text-base leading-[30px] text-white/80">
                        Isi data diri berikut untuk menyelesaikan pendaftaran di SponsorMatch
                    </p>

                    <!-- Ringkasan peran terpilih — tombol "Ubah" kembali ke role pick -->
                    <div class="mt-6 flex items-center gap-3 rounded-[14px] border border-[#4A5670] bg-sm-navy px-4 py-3">
                        <img :src="current.icon" alt="" class="h-8 w-8 shrink-0 object-contain" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ current.title }}</p>
                            <p class="truncate text-xs text-white/70">{{ current.subtitle }}</p>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 cursor-pointer text-sm text-sm-peach underline-offset-2 hover:underline"
                            @click="step = 1"
                        >
                            Ubah
                        </button>
                    </div>

                    <!--
                        Form Inertia mengikuti pola starter kit: v-bind="store.form()"
                        → action/method dari Ziggy (POST /register). Fortify memanggil
                        App\Actions\Fortify\CreateNewUser yang menerima field role ini.
                    -->
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password', 'password_confirmation']"
                        v-slot="{ errors, processing }"
                        class="mt-6 flex flex-col gap-5"
                    >
                        <!-- Field tersembunyi: peran yang dipilih di step 1 -->
                        <input type="hidden" name="role" :value="role" />

                        <div class="grid gap-2">
                            <Label for="name" class="text-base text-white">Nama Lengkap</Label>
                            <Input
                                id="name"
                                name="name"
                                type="text"
                                required
                                autofocus
                                placeholder="Masukkan nama lengkap"
                                :class="inputClass"
                            />
                            <InputError :message="errors.name" class="text-[#FF8A96]" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" class="text-base text-white">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                required
                                placeholder="nama@email.com"
                                :class="inputClass"
                            />
                            <InputError :message="errors.email" class="text-[#FF8A96]" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password" class="text-base text-white">Password</Label>
                            <PasswordInput id="password" name="password" required :class="inputClass" />
                            <InputError :message="errors.password" class="text-[#FF8A96]" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation" class="text-base text-white">Konfirmasi Password</Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                :class="inputClass"
                            />
                            <InputError :message="errors.password_confirmation" class="text-[#FF8A96]" />
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="mt-1 h-[58px] w-full cursor-pointer rounded-[14px] bg-sm-red font-semibold text-white transition-colors hover:bg-[#C41F31] disabled:opacity-60"
                        >
                            <span v-if="processing" class="flex items-center justify-center gap-2">
                                <Spinner class="h-4 w-4" />
                                Mendaftar...
                            </span>
                            <span v-else>Daftar Sekarang</span>
                        </button>
                    </Form>

                    <p class="mt-6 text-center text-sm text-white/80">
                        Sudah punya akun?
                        <Link :href="login()" class="text-sm-peach underline-offset-2 hover:underline">Masuk</Link>
                    </p>
                </div>
            </template>
        </div>
    </div>
</template>
