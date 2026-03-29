<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div
        data-slot="card"
        :class="
            cn(
                'bg-card border border-card-outline text-card-foreground flex flex-col p-6',
                props.class,
            )
        "
    >
        <slot />
    </div>
</template>
