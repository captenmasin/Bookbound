<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { TabsList, type TabsListProps } from 'reka-ui'

const props = defineProps<TabsListProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = reactiveOmit(props, 'class')
</script>

<template>
    <TabsList
        data-slot="tabs-list"
        v-bind="delegatedProps"
        :class="cn(
            'bg-secondary text-secondary-foreground inline-flex h-9 w-fit items-center justify-center rounded-lg px-1 py-[3px]',
            props.class,
        )"
    >
        <slot />
    </TabsList>
</template>
