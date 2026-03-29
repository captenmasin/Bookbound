<script setup lang="ts">
import useEmitter from '@/composables/useEmitter'
import { Primitive, type PrimitiveProps } from 'reka-ui'

withDefaults(defineProps<PrimitiveProps>(), {
    as: 'button',
    asChild: true
})

function openDialog () {
    useEmitter.emit('openJoinProDialog')
}
</script>

<template>
    <Primitive
        :as="as"
        :as-child="asChild"
        @click="openDialog">
        <slot />
    </Primitive>
</template>
