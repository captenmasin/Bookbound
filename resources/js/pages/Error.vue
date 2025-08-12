<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'
import { Button } from '@/components/ui/button/index.js'

const props = defineProps({
    status: Number
})

const title = computed(() => {
    return {
        503: 'We’re doing some maintenance',
        500: 'Something went wrong',
        404: 'Page not found',
        403: 'Page not found'
    }[props.status]
})

const message = computed(() => {
    return {
        503: 'BookBound is down for a bit of re-shelving. Please check back soon.\n',
        500: 'Our library shelves are a bit messy right now. We’re working to tidy things up.\n',
        404: 'The page you\'re looking for doesn\'t exist or has been shelved.\n',
        403: 'The page you\'re looking for doesn\'t exist or has been shelved.\n'
    }[props.status]
})
</script>

<template>
    <AppLayout>
        <div class="md:min-h-[80vh] py-20 md:py-0 flex items-center justify-center px-6">
            <div class="max-w-md mx-auto h-full flex flex-col justify-center text-center">
                <div class="text-8xl font-bold font-serif text-primary">
                    {{ status }}
                </div>
                <h1 class="text-2xl font-semibold font-serif text-foreground">
                    {{ title }}
                </h1>
                <p class="mt-2 text-base text-foreground/50 text-pretty">
                    {{ message }}
                </p>

                <div class="mt-6">
                    <Button
                        as-child>
                        <Link :href="useRoute('dashboard')">
                            <span v-if="[404, 403].includes(status)">
                                Go home
                            </span>
                            <span v-if="[500].includes(status)">
                                Try Again
                            </span>
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
