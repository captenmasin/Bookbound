<script setup lang="ts">
import useEmitter from '@/composables/useEmitter'
import { Button } from '@/components/ui/button'

function openDialog () {
    useEmitter.emit('openJoinProDialog')
}
</script>

<template>
    <Button
        as-child
        @click="openDialog">
        <slot />
    </Button>
</template>
