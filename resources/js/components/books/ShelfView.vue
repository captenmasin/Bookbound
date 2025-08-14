<script setup lang="ts">
import Image from '@/components/Image.vue'
import { PropType } from 'vue'
import { Book } from '@/types/book'
import { Link } from '@inertiajs/vue3'
import { useColours } from '@/composables/useColours'
import { useContrast } from '@/composables/useContrast'
import { useUserSettings } from '@/composables/useUserSettings'
import { useAddCurrentUrl } from '@/composables/useAddCurrentUrl'

defineProps({
    books: {
        type: Array as PropType<Book[]>,
        required: true
    }
})

const { changeColourOpacity } = useColours()
const { getSingleSetting } = useUserSettings()

const maxHeight = 36
const minHeight = 28

function stringToSeed (str: string): number {
    let hash = 0
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash)
    }
    return hash
}

const tiltedCache: Record<string, boolean> = {}

function seededRandom (seed: number | string): number {
    if (typeof seed === 'number') {
        seed = seed.toString()
    }

    const numericSeed = stringToSeed(seed)
    const x = Math.sin(numericSeed) * 10000
    return x - Math.floor(x)
}

function getHeight (identifier: string): number {
    const rand = seededRandom(identifier)
    return Math.floor(rand * (maxHeight - minHeight + 1)) + minHeight
}

function isTilted (identifier: string, index: number): boolean {
    if (getSingleSetting('library.tilt_books') === false) {
        return false
    }

    if (index === 0) {
        tiltedCache[identifier] = false
        return false
    }

    if (tiltedCache[identifier] !== undefined) {
        return tiltedCache[identifier]
    }

    const rand = seededRandom(identifier)

    if (
        (index > 0 && isTilted(identifier, index - 1)) ||
        (index > 1 && isTilted(identifier, index - 2))
    ) {
        tiltedCache[identifier] = false
        return false
    }

    tiltedCache[identifier] = rand < 0.35
    return tiltedCache[identifier]
}

function getUrl (book: Book): string {
    const url = book.links?.show ?? null

    return useAddCurrentUrl(url)
}
</script>
<template>
    <ul class="flex flex-wrap items-end">
        <li
            v-for="(book, index) in books"
            :key="book.identifier"
            class="mb-8 border-b-2 border-amber-900 dark:border-neutral-700 book-card-shelf-item group pb-[2px]">
            <Link
                :href="getUrl(book)"
                prefetch
                :data-tilted="isTilted(book.identifier, index)"
                :style="{
                    backgroundColor: book.colour,
                    '--book-height': getHeight(book.identifier),
                    '--book-width': 7
                }"
                :class="{
                    'ml-[calc(var(--spacing)*(var(--book-width))/2)] mr-4 -translate-y-[3px] -rotate-14 group-hover:rotate-0 group-hover:translate-y-[2px]': isTilted(book.identifier, index)
                }"
                class="w-[calc(var(--spacing)*(var(--book-width)))] rounded-t-xs border border-background overflow-hidden group-hover:scale-125 group-hover:z-20 transition-all relative origin-center h-[calc(var(--spacing)*(var(--book-height)))] flex p-2">
                <div
                    class="absolute top-0 left-0 h-full w-full px-4 z-2">
                    <div class="absolute bottom-0 left-0 w-[calc(var(--spacing)*(var(--book-width)))] h-20 z-2 overflow-hidden">
                        <div
                            class="absolute top-0 left-0 w-[calc(var(--spacing)*(var(--book-width)))] h-full z-2 overflow-hidden"
                            :style="{backgroundImage: `linear-gradient(to bottom, ${changeColourOpacity(book.colour, 1)}, rgba(0, 0, 0, 0))`}"
                        />
                        <Image
                            :src="book.cover"
                            :alt="book.title"
                            :width="45"
                            :height="90"
                            class="h-full w-full object-cover opacity-65" />
                    </div>
                    <div class="pl-2.5 pr-1.5 rotate-[0.25turn] translate-x-[calc(var(--spacing)*(var(--book-width)))] items-center flex w-[calc(var(--spacing)*(var(--book-height)))] h-[calc(var(--spacing)*(var(--book-width)))] z-10 absolute left-0 origin-top-left">
                        <h3
                            :class="useContrast(book.colour, 'text-zinc-900/100', 'text-white/100')"
                            class="w-full font-serif font-semibold line-clamp-1 text-[0.6rem]">
                            {{ book.title }}
                        </h3>
                    </div>
                </div>
            </Link>
        </li>
    </ul>
</template>
