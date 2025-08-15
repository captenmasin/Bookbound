<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

defineProps({
    logoSize: {
        type: String,
        default: 'size-9'
    },
    textSize: {
        type: String,
        default: 'text-lg'
    },
    logoBorderColor: {
        type: String,
        default: 'border-primary/20'
    }
})
</script>

<template>
    <div>
        <div
            class="aspect-square items-center justify-center"
            :class="logoSize">
            <AppLogoIcon
                :class="logoBorderColor"
                class="rounded-lg border fill-current text-primary size-full dark:text-white" />
        </div>
        <div class="relative ml-2 grid flex-1 flex-col gap-0 text-left text-sm text-primary">
            <span
                class="truncate font-serif font-semibold leading-tight tracking-tight"
                :class="textSize">
                {{ page.props.app.name }}
            </span>
        </div>
    </div>
</template>
