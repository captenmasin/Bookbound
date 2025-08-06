<script setup lang="ts">
import { ref } from 'vue'
import { useImageTransform } from '@/composables/useImageTransform'

defineProps<{
    src: string
    placeholder: string
    alt?: string,
    imageClass?: string
}>()

const { cleanPath, baseOptions, buildSrcSet, getImageUrl } = useImageTransform()

const loaded = ref(false)
</script>

<template>
    <div>
        <!-- Blurred placeholder -->
        <img
            :src="getImageUrl(placeholder)"
            alt=""
            aria-hidden="true"
            class="filter blur-md scale-105 transition-opacity duration-500"
            :class="[loaded ? 'opacity-0' : '', imageClass ]"
        >

        <!-- Full image -->
        <img
            :src="getImageUrl(src)"
            :alt="alt"
            loading="lazy"
            class="transition-opacity duration-700"
            :class="[loaded ? 'opacity-100' : 'opacity-0', imageClass]"
            @load="loaded = true"
        >
    </div>
</template>
