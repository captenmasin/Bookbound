<script setup lang="ts">
import { useAttrs } from 'vue'
import { cn } from '@/lib/utils'

withDefaults(defineProps<{
    // eslint-disable-next-line no-undef
    tag?: keyof HTMLElementTagNameMap
}>(), {
    tag: 'h2'
})

const attrs = useAttrs()
</script>

<template>
    <component
        :is="tag"
        v-bind="attrs"
        :class="cn('font-serif items-center text-3xl md:text-3xl font-semibold', attrs.class ?? '')">
        <slot />
    </component>
</template>
