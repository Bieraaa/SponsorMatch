<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { useReveal } from '@/composables/useReveal';

// Seksi FAQ (#faq). Pertanyaan disusun dari contoh FAQ pada SRS (bagian 2.6).
const faqs = [
    {
        q: 'Apa itu SponsorMatch?',
        a: 'SponsorMatch adalah platform berbasis web yang membantu event organizer menemukan dan mengelola sponsor. Sistem memanfaatkan pencocokan berbasis AI untuk merekomendasikan perusahaan yang relevan dengan kebutuhan acara Anda.',
    },
    {
        q: 'Bagaimana jika hasil ekstraksi AI kurang akurat?',
        a: 'Hasil ekstraksi AI hanyalah draf. Sebelum proposal diterbitkan, Anda dapat meninjau dan mengoreksi setiap data hasil ekstraksi secara manual, sehingga informasi yang tampil dijamin sesuai keinginan Anda.',
    },
    {
        q: 'Apakah SponsorMatch menjamin saya akan mendapat sponsor?',
        a: 'Tidak. Seluruh hasil pencocokan bersifat rekomendasi berdasarkan kecocokan karakteristik data yang tersedia—bukan prediksi keberhasilan. Keputusan akhir tetap berada di tangan kedua belah pihak.',
    },
    {
        q: 'Bagaimana cara mengajukan penawaran sponsorship?',
        a: 'Setelah proposal Anda dikirim ke sebuah perusahaan, perusahaan dapat mengajukan penawaran (jenis dukungan, nominal, dan benefit). Anda kemudian dapat merespons dengan menerima, menolak, atau mengajukan penawaran balik (counter-offer, khusus pengguna Premium).',
    },
    {
        q: 'Apa perbedaan pengguna reguler dan Premium?',
        a: 'Pengguna reguler mendapatkan kuota ekstraksi AI terbatas serta fitur dasar. Pengguna Premium mendapatkan ekstraksi tanpa batas, cakupan rekomendasi lebih luas, filter lanjutan, counter-offer, chat, notifikasi proaktif, dan insight interaksi terhadap proposal/campaign Anda.',
    },
    {
        q: 'Apa yang terjadi jika pembayaran langganan gagal?',
        a: 'Jika pembayaran gagal, status langganan Premium tidak akan aktif dan Anda tetap dapat menggunakan fitur reguler. Anda dapat mencoba pembayaran ulang kapan saja melalui menu Langganan.',
    },
];

// Animasi reveal: aktif saat masuk viewport, reset saat keluar (animasi berulang).
const { el: sectionEl, revealed } = useReveal(0.25);
</script>

<template>
    <section
        id="faq"
        ref="sectionEl"
        class="flex min-h-svh snap-start items-center scroll-mt-[68px] bg-sm-navy font-jakarta text-white"
    >
        <div class="mx-auto grid w-full max-w-[1440px] items-start gap-12 px-5 py-20 lg:grid-cols-[1fr_1.2fr] lg:px-10 lg:py-28">
            <!-- Teks + foto -->
            <div
                class="transition-all delay-150 duration-700 ease-out lg:sticky lg:top-28"
                :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <p class="font-poppins text-sm font-semibold uppercase tracking-widest text-sm-amber">FAQ</p>
                <h2 class="mt-3 font-poppins text-4xl font-bold leading-tight lg:text-5xl">
                    Pertanyaan yang <span class="text-sm-red">Sering Diajukan</span>
                </h2>
                <p class="mt-5 max-w-md text-base leading-7 text-white/80">
                    Belum menemukan jawaban yang dicari? Hubungi kami melalui email di bawah, tim kami siap membantu.
                </p>

                <div class="relative mt-10 w-full max-w-[331px]">
                    <img
                        src="/assets/vector_tanda_tanya.svg"
                        alt=""
                        class="absolute -top-8 -right-6 z-0 h-16 w-auto opacity-40"
                        aria-hidden="true"
                    />
                    <img
                        src="/assets/gambar_landing_page_desk_faq.png"
                        alt="Ilustrasi pertanyaan seputar SponsorMatch"
                        class="relative z-10 block w-full"
                    />
                </div>
            </div>

            <!-- Daftar pertanyaan -->
            <div class="space-y-4">
                <Collapsible
                    v-for="(faq, index) in faqs"
                    :key="faq.q"
                    class="rounded-xl bg-sm-card ring-1 ring-white/5 transition-all duration-500 ease-out"
                    :class="revealed ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
                    :style="{ transitionDelay: `${200 + index * 80}ms` }"
                >
                    <CollapsibleTrigger
                        class="flex w-full items-center justify-between gap-4 rounded-xl px-6 py-5 text-left font-poppins text-base font-medium transition-colors hover:text-sm-amber"
                    >
                        {{ faq.q }}
                        <span class="shrink-0 text-xl leading-none text-sm-amber" aria-hidden="true">+</span>
                    </CollapsibleTrigger>
                    <CollapsibleContent class="px-6 pb-5 text-sm leading-6 text-white/70">
                        {{ faq.a }}
                    </CollapsibleContent>
                </Collapsible>
            </div>
        </div>
    </section>
</template>
