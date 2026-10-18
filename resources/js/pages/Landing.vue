<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Hero from '@/components/landing/Hero.vue';
import Navbar from '@/components/landing/Navbar.vue';
</script>

<template>
    <Head title="SponsorMatch - Temukan Sponsor yang Tepat" />

    <div class="min-h-screen bg-sm-navy font-jakarta text-white">
        <Navbar />
        <main>
            <Hero />
            <!-- Seksi berikutnya ditambahkan satu per satu:
                <WhyUs /> <Pricing /> <Faq /> -->
        </main>
        <!-- <Footer /> -->
    </div>
</template>