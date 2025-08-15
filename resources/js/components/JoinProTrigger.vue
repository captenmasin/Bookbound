<script setup lang="ts">
import useEmitter from '@/composables/useEmitter'

function openDialog () {
    useEmitter.emit('openJoinProDialog')
}
</script>

<template>
    <button @click="openDialog">
        <slot />
    </button>
</template>
