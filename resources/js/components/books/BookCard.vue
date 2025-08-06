<script setup lang="ts">
import Image from '@/components/Image.vue'
import StarRatingDisplay from '@/components/StarRatingDisplay.vue'
import { Link } from '@inertiajs/vue3'
import type { Book } from '@/types/book'
import { computed, PropType } from 'vue'
import { useBook } from '@/composables/useBook'
import { useColours } from '@/composables/useColours'
import { useContrast } from '@/composables/useContrast'
import { useAddCurrentUrl } from '@/composables/useAddCurrentUrl'

const props = defineProps({
    book: {
        type: Object as PropType<Book>,
        required: true
    },
    hover: {
        type: Boolean,
        default: true
    }
})

const { changeColourOpacity } = useColours()
const { userRating } = useBook(props.book)

const url = computed(() => {
    const url = props.book.links?.show ?? null

    return useAddCurrentUrl(url)
})
</script>

<template>
    <article
        :id="`book-card-${book.id}`"
        class="flex gap-4 book-card">
        <div
            class="flex w-full flex-col overflow-hidden rounded-md shadow-sm group"
            :style="{
                backgroundColor: book.colour,
            }"
            :class="[
                useContrast(book.colour, 'text-zinc-900', 'text-white'),
            ]"
        >
            <Link
                :href="url"
                prefetch>
                <div class="relative w-full overflow-hidden aspect-book group">
                    <span
                        v-if="book.binding"
                        class="absolute opacity-0 capitalize group-hover:opacity-100 delay-100 transition-all top-2 right-2 text-xs bg-white/75 text-zinc-900 px-1.5 py-px rounded-full">
                        {{ book.binding }}
                    </span>
                    <Image
                        v-if="book.cover"
                        :src="book.cover"
                        :height="315"
                        :width="200"
                        class="h-full w-full object-cover" />
                    <div
                        v-if="hover"
                        class="absolute bottom-0 left-0 flex w-full items-end p-4 opacity-0 transition-all duration-300 h-full group-hover:opacity-100"
                        :style="{backgroundImage: `linear-gradient(to top, ${changeColourOpacity(book.colour, 1)}, ${changeColourOpacity(book.colour, 0.75)}, rgba(0, 0, 0, 0))`}">
                        <div class="flex w-full translate-y-4 flex-col gap-1.5 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                            <h2 class="font-serif font-semibold text-base md:text-lg line-clamp-4 leading-5">
                                {{ book.title }}
                            </h2>
                            <p
                                v-if="book.authors"
                                class="text-xs line-clamp-2">
                                {{ book.authors.map(a => a.name).join(', ') }}
                            </p>
                            <StarRatingDisplay
                                v-if="userRating"
                                class="mt-1"
                                text-class="text-inherit"
                                :rating="userRating.value"
                                :star-width="15" />
                        </div>
                    </div>
                </div>
            </Link>
        </div>
    </article>
</template>
