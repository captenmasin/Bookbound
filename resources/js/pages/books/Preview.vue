<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

defineProps({
    identifier: {
        type: String,
        required: true
    }
})

setInterval(function () {
    router.reload()
}, 2000)

defineOptions({
    layout: AppLayout
})
</script>

<template>
    <div
        class="mt-12 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center text-sm text-muted-foreground border-primary/10 md:py-16">
        <Icon
            name="BookDown"
            class="animate-bounce size-8" />
        <h3 class="font-serif text-2xl font-semibold">
            Importing...
        </h3>
        <p>
            Please wait while we import the book. This may take a few minutes depending on the size of the book.
        </p>
    </div>
</template>
