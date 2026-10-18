import { useIntersectionObserver } from '@vueuse/core';
import { ref } from 'vue';

/**
 * Animasi reveal sederhana berbasis scroll.
 *
 * Cara pakai:
 *   const { el, revealed } = useReveal(0.25);
 * lalu pasang `ref="el"` pada elemen target dan gunakan `revealed`
 * untuk mengganti kelas transisi (mis. opacity-0 / translate-y-8).
 *
 * `revealed` menjadi true saat >= threshold elemen terlihat di viewport
 * dan kembali false saat keluar, sehingga animasi berulang setiap kali.
 */
export function useReveal(threshold = 0.25) {
    const el = ref<HTMLElement | null>(null);
    const revealed = ref(false);

    useIntersectionObserver(
        el,
        ([entry]) => {
            revealed.value = !!entry?.isIntersecting;
        },
        { threshold },
    );

    return { el, revealed };
}
