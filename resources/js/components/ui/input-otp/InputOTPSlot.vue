<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { useForwardProps } from "reka-ui"
import { computed } from "vue"
import { useVueOTPContext } from "vue-input-otp"
import { cn } from "@/lib/utils"

const props = defineProps<{ index: number, class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardProps(delegatedProps)

const context = useVueOTPContext()

const slot = computed(() => context?.value.slots[props.index])
</script>

<template>
  <div
    v-bind="forwarded"
    data-slot="input-otp-slot"
    :data-active="slot?.isActive"
    :class="cn('data-[active=true]:border-ring data-[active=true]:ring-ring/50 data-[active=true]:aria-invalid:ring-destructive/20 dark:data-[active=true]:aria-invalid:ring-destructive/40 aria-invalid:border-destructive data-[active=true]:aria-invalid:border-destructive dark:bg-input/30 border-input relative flex h-9 w-9 items-center justify-center border-y border-r text-sm shadow-xs transition-all outline-none first:rounded-l-md first:border-l last:rounded-r-md data-[active=true]:z-10 data-[active=true]:ring-[3px]', props.class)"
  >
    <!--
      Garis penanda posisi input.

      Saat kotak masih kosong, muncul garis tipis di tengah kotak sebagai
      petunjuk "digit kamu ketik di sini". Garis ini hilang begitu kotak
      terisi digit.

      `bg-current` dipakai supaya warnanya otomatis mengikuti warna teks
      kotak (mis. navy), dan `pointer-events-none` supaya tidak mengganggu
      klik/fokus pada input.
    -->
    <span
      v-if="!slot?.char"
      data-slot="input-otp-slot-placeholder"
      aria-hidden="true"
      class="pointer-events-none absolute left-1/2 top-1/2 h-px w-[42%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-current opacity-30"
    />

    {{ slot?.char }}
    <div v-if="slot?.hasFakeCaret" class="pointer-events-none absolute inset-0 flex items-center justify-center">
      <div class="animate-caret-blink bg-current h-4 w-px duration-1000" />
    </div>
  </div>
</template>
