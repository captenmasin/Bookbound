<script setup lang="ts">
import BookPile from '~/images/book-pile.webp'
import AppLogo from '@/components/AppLogo.vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import BookPileSmall from '~/images/book-pile-small.webp'
import ProgressiveImage from '@/components/ProgressiveImage.vue'
import { usePwa } from '@/composables/usePwa'
import { Link, usePage } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'

const page = usePage()
const name = page.props.name

defineProps<{
    title?: string;
    description?: string;
}>()

const { isPwa } = usePwa()

const points = [
    {
        title: '📚 Curate Your Personal Library',
        description: 'Track every book you own, love, or plan to read.'
    },
    {
        title: '⭐ Rate & Review Thoughtfully',
        description: 'Leave ratings and write personal notes, just like writing in the margins.'
    },
    {
        title: '📖 See What You’re Reading',
        description: 'Stay on top of your current reads at a glance.'
    }
]
</script>

<template>
    <div class="relative grid flex-col items-center justify-center px-8 bg-background h-dvh sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="relative hidden h-full flex-col p-10 text-white dark:border-r lg:flex">
            <div
                class="absolute inset-4 flex flex-col justify-end overflow-hidden rounded-xl bg-cover bg-center shadow-md bg-secondary text-secondary-foreground">
                <ProgressiveImage
                    :src="BookPile"
                    :placeholder="BookPileSmall"
                    alt="Book Pile"
                    image-class="absolute inset-0 h-full w-full object-cover"
                />
                <div class="absolute inset-0 z-10 flex items-end bg-gradient-to-t from-black/60 via-black/30 to-transparent p-8 text-white">
                    <ul class="flex flex-col gap-6">
                        <li
                            v-for="point in points"
                            :key="point.title">
                            <h3 class="text-lg font-semibold">
                                {{ point.title }}
                            </h3>
                            <p class="pl-6 text-sm text-white/80">
                                {{ point.description }}
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
            <Link
                :href="isPwa ? null : useRoute('home')"
                class="relative z-20 flex items-center font-serif text-2xl font-semibold tracking-tight text-white">
                <AppLogoIcon class="mr-2 rounded-lg fill-current size-8" />
                <div class="relative flex flex-col">
                    <span>{{ name }}</span>
                </div>
            </Link>
        </div>
        <div class="lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center py-4 space-y-6 sm:w-sm">
                <AppLogo
                    logo-size="size-12"
                    text-size="text-2xl"
                    class="mx-auto mb-5 flex flex-col items-center justify-center gap-1 text-primary lg:hidden" />
                <div class="flex flex-col text-center space-y-1">
                    <h1
                        v-if="title"
                        class="text-xl font-medium tracking-tight">
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
