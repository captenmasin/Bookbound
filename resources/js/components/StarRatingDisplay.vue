<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import { computed } from 'vue'

const props = withDefaults(
    defineProps<{
        rating: number;
        starWidth?: number;
        textClass?: string;
        strokeClass?: string;
    }>(),
    {
        starWidth: 20,
        textClass: 'text-primary/20',
        strokeClass: 'text-yellow-400'
    }
)

const gap = computed(() => props.starWidth / 10)
const totalWidth = computed(() => 5 * props.starWidth + 4 * gap.value)

const starsDisplayCount = computed(() => Math.ceil(props.rating))
</script>

<template>
    <div
        :key="starWidth"
        class="relative flex">
        <!-- Background: empty stars -->
        <div
            :style="{ gap: `${gap}px`, width: `${totalWidth}px` }"
            :class="textClass"
            class="flex"
        >
            <Icon
                v-for="i in starsDisplayCount"
                :key="`bg-${i}`"
                name="star"
                :style="{ width: `${starWidth}px`, height: `${starWidth}px` }"
            />
        </div>

        <!-- Foreground: filled stars, clipped to rating percentage -->
        <div
            class="absolute top-0 left-0 flex overflow-hidden text-yellow-400"
            :style="{
                gap: `${gap}px`,
                width: `${(rating / 5) * totalWidth}px`,
            }"
        >
            <Icon
                v-for="i in 5"
                :key="`fg-${i}`"
                name="star"
                :style="{ width: `${starWidth}px`, height: `${starWidth}px` }"
                :class="strokeClass"
                class="shrink-0 fill-yellow-400"
            />
        </div>
    </div>
</template>
