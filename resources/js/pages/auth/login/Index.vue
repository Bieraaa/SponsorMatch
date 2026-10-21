<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import TextLink from '@/components/TextLink.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Eye, EyeOff } from '@lucide/vue';

/**
 * Blok A — Halaman Login (Figma frame "Login", 1440x1024).
 *
 * Hasil analisis piksel Figma export:
 * - Layout 2 kolom: kiri ilustrasi 600px (putih, aset gambar_login.png),
 *   kanan panel navy #161E2F full-height (x600-1440 = 840px).
 * - Form di kolom x664-1382 (lebar ~712px, padding panel ~64px).
 * - H1 "Selamat Datang" putih + "Kembali" MERAH #B51A2B, ~40px semibold.
 * - Input: isi PUTIH, border #541A2E 2px, radius 12px, tinggi 69px,
 *   ikon kiri (#242F49) jarak ~24px dari tepi, teks #242F49 18-20px.
 * - Checkbox "Ingat Saya" putih 24x24; "Lupa Password?" abu terang KANAN.
 * - Tombol "Masuk" merah full-width, tinggi 65px, radius 12px, teks putih.
 * - Pemisah "Atau" (garis + teks), lalu 2 tombol putih:
 *   "Lanjutkan dengan Google" & "Lanjutkan dengan Apple" (radius 12px).
 * - Footer: "Belum punya akun?" putih + "Daftar Sekarang" merah.
 * - Aset: gambar_login.png, icon_email.png, icon_password.png,
 *   icon_google.png, icon_apple.png (semua di /assets).
 */

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const showPassword = ref(false);
</script>

<template>
    <Head title="Masuk" />

    <div class="flex min-h-screen bg-white">
        <!-- Ilustrasi kiri (600x1024, sesuai Figma) — hanya tampil di layar besar -->
        <div class="hidden bg-white lg:block lg:w-[600px] lg:shrink-0">
            <img
                src="/assets/gambar_login.png"
                alt="SponsorMatch — Kolaborasi untuk Event Berdamapak"
                class="h-full w-full object-cover object-top"
            />
        </div>

        <!-- Panel kanan: navy, form di tengah, lebar konten 712px -->
        <div class="flex flex-1 items-center justify-center bg-sm-navy px-6 py-16 lg:px-16">
            <div class="w-full max-w-[712px]">
                <div
                    v-if="status"
                    class="mb-6 rounded-xl border-2 border-sm-red-dark bg-sm-card px-5 py-4 text-center text-sm font-medium text-white"
                >
                    {{ status }}
                </div>

                <!-- H1: "Selamat Datang" putih + "Kembali" merah (40px) -->
                <h1 class="text-[28px] font-semibold leading-tight text-white lg:text-[40px]">
                    Selamat Datang <span class="text-sm-red">Kembali</span>
                </h1>

                <!-- Subteks (18px, putih) -->
                <p class="mt-4 text-base leading-relaxed text-white lg:text-lg">
                    Masuk ke akun SponsorMatch untuk kembali melanjutkan perjalanan kolaborasi kamu
                </p>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="mt-12"
                >
                    <!-- Input Email (tinggi 69px, border #541A2E, radius 12px) -->
                    <div
                        class="flex h-16 items-center gap-4 rounded-xl border-2 border-sm-red-dark bg-white px-5 lg:h-[69px]"
                    >
                        <img src="/assets/icon_email.png" alt="" class="h-[30px] w-[38px] shrink-0" />
                        <input
                            id="email"
                            type="text"
                            name="email"
                            required
                            v-focus
                            :tabindex="1"
                            autocomplete="username"
                            placeholder="Email atau nomor telepon"
                            class="h-full w-full border-0 bg-transparent text-base text-sm-card outline-none placeholder:text-sm-card lg:text-lg"
                        />
                    </div>
                    <InputError :message="errors.email" class="mt-2" />

                    <!-- Input Password (sama, + toggle lihat password) -->
                    <div
                        class="mt-6 flex h-16 items-center gap-4 rounded-xl border-2 border-sm-red-dark bg-white px-5 lg:h-[69px]"
                    >
                        <img src="/assets/icon_password.png" alt="" class="h-9 w-9 shrink-0" />
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="Password"
                            class="h-full w-full border-0 bg-transparent text-base text-sm-card outline-none placeholder:text-sm-card lg:text-lg"
                        />
                        <button
                            type="button"
                            class="shrink-0 text-sm-card/70 hover:text-sm-card"
                            :tabindex="-1"
                            @click="showPassword = !showPassword"
                        >
                            <Eye v-if="!showPassword" class="h-6 w-6" />
                            <EyeOff v-else class="h-6 w-6" />
                        </button>
                    </div>
                    <InputError :message="errors.password" class="mt-2" />

                    <!-- Baris: checkbox "Ingat Saya" (kiri) + "Lupa Password?" (kanan) -->
                    <div class="mt-8 flex items-center justify-between">
                        <label for="remember" class="flex cursor-pointer items-center gap-3">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                :tabindex="3"
                                class="h-6 w-6 cursor-pointer appearance-none rounded-md border-2 border-sm-red-dark bg-white checked:border-sm-red checked:bg-sm-red"
                            />
                            <span class="text-base text-white lg:text-lg">Ingat Saya</span>
                        </label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-base text-[#E2E3E5] hover:text-sm-red lg:text-lg"
                            :tabindex="4"
                        >
                            Lupa Password?
                        </TextLink>
                    </div>

                    <!-- Tombol Masuk (merah, tinggi 65px, radius 12px) -->
                    <button
                        type="submit"
                        class="mt-9 flex h-16 w-full items-center justify-center gap-3 rounded-xl bg-sm-red text-xl font-semibold text-white transition-colors hover:bg-sm-red-dark disabled:opacity-70 lg:h-[65px]"
                        :tabindex="5"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" class="h-5 w-5" />
                        Masuk
                    </button>

                    <!-- Pemisah "Atau" -->
                    <div class="mt-10 flex items-center gap-4">
                        <span class="h-px flex-1 bg-white/25"></span>
                        <span class="text-base text-white lg:text-lg">Atau</span>
                        <span class="h-px flex-1 bg-white/25"></span>
                    </div>

                    <!-- Tombol sosial (tampilan sesuai Figma; backend login
                         Google/Apple belum diintegrasikan — klik tidak berbuat apa-apa) -->
                    <button
                        type="button"
                        class="mt-10 flex h-16 w-full items-center justify-center gap-6 rounded-xl border-2 border-sm-red-dark bg-white text-lg font-medium text-sm-card transition-colors hover:bg-gray-50 lg:h-[69px] lg:text-xl"
                    >
                        <img src="/assets/icon_google.png" alt="" class="h-[38px] w-[37px]" />
                        Lanjutkan dengan Google
                    </button>
                    <button
                        type="button"
                        class="mt-5 flex h-16 w-full items-center justify-center gap-6 rounded-xl border-2 border-sm-red-dark bg-white text-lg font-medium text-sm-card transition-colors hover:bg-gray-50 lg:h-[69px] lg:text-xl"
                    >
                        <img src="/assets/icon_apple.png" alt="" class="h-[39px] w-[33px]" />
                        Lanjutkan dengan Apple
                    </button>

                    <!-- Footer: daftar -->
                    <p class="mt-10 text-center text-base text-white lg:text-lg">
                        Belum punya akun?
                        <TextLink :href="register()" class="font-semibold text-sm-red hover:underline" :tabindex="6">
                            Daftar Sekarang
                        </TextLink>
                    </p>
                </Form>
            </div>
        </div>
    </div>
</template>
