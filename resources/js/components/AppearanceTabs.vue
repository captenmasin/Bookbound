<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next'
import { useAppearance } from '@/composables/useAppearance'

withDefaults(defineProps<{
    iconOnly?: boolean;
}>(), {
    iconOnly: false
})

const { appearance, updateAppearance } = useAppearance()

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' }
] as const
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg p-1 bg-card border border-card-outline dark:bg-neutral-800">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            :class="[
                'flex items-center rounded-md py-1.5 transition-colors',
                iconOnly ? 'justify-center px-2.5' : 'px-3.5',
                appearance === value
                    ? 'bg-white text-foreground shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-primary hover:bg-white/40 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
            :aria-label="label"
            :title="label"
            @click="updateAppearance(value)"
        >
            <component
                :is="Icon"
                :class="iconOnly ? 'h-4 w-4' : '-ml-1 h-4 w-4'" />
            <span
                v-if="!iconOnly"
                class="text-sm ml-1.5"
            >
                {{ label }}
            </span>
        </button>
    </div>
</template>
