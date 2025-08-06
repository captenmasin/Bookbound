<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
    src: string
    placeholder: string
    alt?: string,
    imageClass?: string
}>()

const loaded = ref(false)
</script>

<template>
    <div>
        <!-- Blurred placeholder -->
        <img
            :src="placeholder"
            alt=""
            aria-hidden="true"
            class="filter blur-md scale-105 transition-opacity duration-500"
            :class="[loaded ? 'opacity-0' : '', imageClass ]"
        >

        <!-- Full image -->
        <img
            :src="src"
            :alt="alt"
            loading="lazy"
            class="transition-opacity duration-700"
            :class="[loaded ? 'opacity-100' : 'opacity-0', imageClass]"
            @load="loaded = true"
        >
    </div>
</template>
