<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { OTPInputEmits, OTPInputProps } from "vue-input-otp"
import { reactiveOmit } from "@vueuse/core"
import { useForwardPropsEmits } from "reka-ui"
import { OTPInput } from "vue-input-otp"
import { computed } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<OTPInputProps & { class?: HTMLAttributes["class"] }>()

const emits = defineEmits<OTPInputEmits>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardPropsEmits(delegatedProps, emits)

/**
 * Pola default: HANYA angka yang boleh diketik.
 *
 * Kenapa default ini penting:
 * `vue-input-otp` mengubah `pattern` jadi RegExp lalu memakainya untuk
 * memblokir input yang tidak cocok. Kalau polanya `[0-9]*`, regex-nya jadi
 * "nol atau lebih digit" — yang cocok dengan APA SAJA termasuk huruf.
 * Akibatnya user bisa mengetik "abc" ke kotak OTP.
 *
 * `^[0-9]*$` anchoring-nya jelas: seluruh isi input harus angka.
 * Kalau sebuah halaman butuh kode yang mengandung huruf, cukup kirim
 * `pattern` sendiri — nilai itu yang akan dipakai.
 */
const effectivePattern = computed(() => props.pattern ?? "^[0-9]*$")
</script>

<template>
  <OTPInput
    v-slot="slotProps"
    v-bind="forwarded"
    :pattern="effectivePattern"
    :container-class="cn('flex items-center gap-2 has-disabled:opacity-50', props.class)"
    data-slot="input-otp"
    class="disabled:cursor-not-allowed"
  >
    <slot v-bind="slotProps" />
  </OTPInput>
</template>
