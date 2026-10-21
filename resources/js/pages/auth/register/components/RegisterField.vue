<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

/**
 * Satu baris field form pendaftaran (label + input + pesan error).
 *
 * Dipakai oleh form EO dan form Perusahaan supaya gaya visualnya identik
 * dan tidak ada kode yang terduplikasi. Semua field di form pendaftaran
 * memakai gaya input GELAP yang sama (lihat INPUT_CLASS di parent).
 *
 * Kenapa `password` dipisah ke komponen PasswordInput? Karena field password
 * butuh tombol "lihat/sembunyikan". Jadi komponen ini khusus input teks biasa.
 */
defineProps<{
    /** id + name input; dipakai juga oleh <Label for> */
    id: string;
    label: string;
    type?: string;
    placeholder?: string;
    autocomplete?: string;
    required?: boolean;
    autofocus?: boolean;
    /** Class tambahan (mis. tinggi input) dari parent */
    inputClass?: string;
    /** Pesan error dari Inertia Form */
    error?: string;
    /** Teks bantuan kecil di bawah input (opsional) */
    hint?: string;
}>();
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id" class="text-base text-white">{{ label }}</Label>
        <Input
            :id="id"
            :name="id"
            :type="type ?? 'text'"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :required="required"
            :autofocus="autofocus"
            :class="inputClass"
        />
        <p v-if="hint" class="text-xs text-white/60">{{ hint }}</p>
        <InputError :message="error" class="text-[#FF8A96]" />
    </div>
</template>
