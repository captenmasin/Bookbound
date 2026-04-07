<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { Label, type LabelProps } from 'reka-ui'

const props = defineProps<LabelProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = reactiveOmit(props, 'class')
</script>

<template>
    <Label
        data-slot="label"
        v-bind="delegatedProps"
        :class="
            cn(
                'flex items-center gap-2 text-sm leading-none font-medium select-none group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:opacity-50',
                `flex justify-between font-sans text-xs font-semibold tracking-wide text-secondary-foreground uppercase`,
                props.class,
            )
        "
    >
        <slot />
    </Label>
</template>
