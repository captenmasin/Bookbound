<script setup lang="ts">
import PlaceholderImage from '~/images/placeholder.svg'
import { ref, computed } from 'vue'
import { ImageProps } from '@/types/image'
import { useImageTransform } from '@/composables/useImageTransform'

const props = withDefaults(defineProps<ImageProps>(), {
    alt: '',
    srcsetSizes: () => [320, 640, 960],
    srcsetMaxWidth: 1280,
    scale: true,
    placeholder: PlaceholderImage,
    showPlaceholder: true
})

const isLoaded = ref(false)
function handleLoad () {
    isLoaded.value = true
}

const { cleanPath, baseOptions, buildSrcSet, getImageUrl } = useImageTransform()

const options = computed(() => baseOptions(props))
const srcset = computed(() => buildSrcSet(props))
const finalSrc = computed(() => {
    return options.value
        ? getImageUrl(props.src, options.value)
        : `/storage/${cleanPath(props.src)}`
})
</script>

<template>
    <img
        :srcset="srcset"
        :src="finalSrc"
        :width="width"
        :sizes="enableSrcset && srcset?.length ? `(max-width: ${width || srcsetMaxWidth}px) 100vw, ${width || srcsetMaxWidth}px` : null"
        :height="height"
        loading="lazy"
        class="transition-opacity"
        :class="isLoaded ? '' : 'NO-animate-pulse'"
        :style="isLoaded ? null : {
            backgroundImage: showPlaceholder && placeholder && !isLoaded ? `url(${placeholder})` : '',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundColor: background ? `#${background}` : '#f3f4f6'
        }"
        :alt="alt"
        @load="handleLoad"
    >
</template>
