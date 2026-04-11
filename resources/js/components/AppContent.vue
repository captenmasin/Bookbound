<script setup lang="ts">
import { computed } from 'vue'
import { SidebarInset } from '@/components/ui/sidebar'
import AppProBanner from '@/components/AppProBanner.vue'

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>()
const className = computed(() => props.class)
</script>

<template>
    <SidebarInset
        v-if="props.variant === 'sidebar'"
        :class="className">
        <slot />
    </SidebarInset>
    <AppProBanner />
    <main
        v-if="props.variant !== 'sidebar'"
        class="mx-auto flex h-full w-full max-w-screen-2xl flex-1 flex-col gap-4 rounded-xl px-4 md:px-12 pwa:max-w-none"
        :class="className"
    >
        <slot />
    </main>
</template>
