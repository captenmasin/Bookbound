<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <h3
        data-slot="card-title"
        :class="cn('font-serif text-xl font-bold text-primary', props.class)"
    >
        <slot />
    </h3>
</template>
