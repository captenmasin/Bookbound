<script setup lang="ts">
import BookPile from '~/images/book-pile.webp'
import AppLogo from '@/components/AppLogo.vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import BookPileSmall from '~/images/book-pile-small.webp'
import ProgressiveImage from '@/components/ProgressiveImage.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'

const page = usePage()
const name = page.props.name

defineProps<{
    title?: string;
    description?: string;
}>()

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
    <div class="relative grid flex-col items-center bg-background justify-center px-8 h-dvh sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="relative hidden h-full flex-col p-10 text-white dark:border-r lg:flex">
            <div
                class="absolute inset-4 flex flex-col justify-end bg-cover overflow-hidden shadow-md bg-center bg-secondary text-secondary-foreground rounded-xl">
                <ProgressiveImage
                    :src="BookPile"
                    :placeholder="BookPileSmall"
                    alt="Book Pile"
                    image-class="absolute inset-0 w-full h-full object-cover"
                />
                <div class="z-10 bg-gradient-to-t from-black/60 via-black/30 text-white to-transparent absolute inset-0 flex items-end p-8">
                    <ul class="flex flex-col gap-6">
                        <li
                            v-for="point in points"
                            :key="point.title">
                            <h3 class="text-lg font-semibold">
                                {{ point.title }}
                            </h3>
                            <p class="text-sm text-white/80 pl-6">
                                {{ point.description }}
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
            <Link
                :href="useRoute('user.books.index')"
                class="relative z-20 flex items-center text-2xl text-white font-serif font-semibold tracking-tight">
                <AppLogoIcon class="mr-2 rounded-lg fill-current size-8" />
                <div class="flex flex-col relative">
                    <span>{{ name }}</span>
                </div>
            </Link>
        </div>
        <div class="lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-sm py-4">
                <AppLogo
                    logo-size="size-12"
                    text-size="text-2xl"
                    class="text-primary items-center gap-1 justify-center flex flex-col mx-auto lg:hidden mb-5" />
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
