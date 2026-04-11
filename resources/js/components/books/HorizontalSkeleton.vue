<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton'

defineProps({
    withActions: {
        type: Boolean,
        default: true
    }
})
</script>

<template>
    <div class="flex w-full flex-col gap-2 py-2 md:flex-row md:items-center md:gap-4">
        <div class="flex gap-4">
            <Skeleton class="w-20 shrink-0 overflow-hidden aspect-book md:w-40" />
            <div class="flex w-full flex-col gap-1">
                <Skeleton class="h-5 w-full md:w-48" />
                <Skeleton class="h-3 w-36 md:mt-1" />
                <Skeleton class="mt-1 h-3 w-full md:mt-2 md:w-80" />
                <Skeleton class="h-3 w-full md:w-56" />
            </div>
        </div>
        <div
            v-if="withActions"
            class="-mt-11 flex w-full shrink-0 pl-24 max-w-64 md:ml-auto md:w-40 md:max-w-none md:pl-0">
            <Skeleton class="h-8 w-full" />
        </div>
    </div>
</template>
