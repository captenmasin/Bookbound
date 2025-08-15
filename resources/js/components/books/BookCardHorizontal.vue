<script setup lang="ts">
import DefaultCover from '~/images/default-cover.svg'
import BookActions from '@/components/books/BookActions.vue'
import StarRatingDisplay from '@/components/StarRatingDisplay.vue'
import { Link } from '@inertiajs/vue3'
import { computed, PropType } from 'vue'
import { useBook } from '@/composables/useBook'
import { Book, BookApiResult } from '@/types/book'
import { useAddCurrentUrl } from '@/composables/useAddCurrentUrl'

const props = defineProps({
    book: {
        type: Object as PropType<BookApiResult | Book>,
        required: true
    },
    narrow: {
        type: Boolean,
        default: false
    },
    includeActions: {
        type: Boolean,
        default: true
    },
    target: {
        type: String as PropType<'_blank' | '_self'>,
        default: '_self'
    },
    linkSrc: {
        type: [String, null] as PropType<string | null>,
        default: null
    }
})

const isLink = computed(() => {
    return props.book.links?.show !== undefined && props.book.links?.show !== null
})

const linkTag = computed(() => {
    if (props.target === '_blank') {
        return 'a'
    }

    if (props.book.links?.show) {
        return Link
    }

    return 'span'
})

const url = computed(() => {
    const url = props.book.links?.show ?? null

    return useAddCurrentUrl(url)
})

const { userRating } = useBook(props.book)
</script>

<template>
    <div
        :id="`book-card-${book.id}`"
        class="flex w-full flex-col gap-2 group book-card-horizontal md:items-center"
        :class="narrow ? '' : 'md:flex-row md:gap-8'">
        <div class="flex w-full gap-4">
            <component
                :is="linkTag"
                :href="url"
                :target="target"
                prefetch>
                <div class="relative w-20 shrink-0 overflow-hidden rounded-sm shadow-sm aspect-book md:w-22">
                    <span
                        v-if="book.binding"
                        class="absolute top-1 right-1 rounded-full bg-white/75 py-px text-zinc-900 opacity-0 transition-all text-[10px] px-1.5 group-hover:opacity-100">
                        {{ book.binding }}
                    </span>
                    <img
                        :src="book.cover ?? DefaultCover"
                        :alt="`Book cover image for ${book.title}`"
                        class="bg-gray-200 object-cover size-full">
                </div>
            </component>
            <div class="flex w-full min-w-0 flex-col">
                <div class="flex">
                    <component
                        :is="linkTag"
                        :href="url"
                        :target="target"
                        prefetch>
                        <h3
                            :class="isLink ? 'hover:text-primary dark:hover:text-primary/80' : ''"
                            class="font-serif text-lg transition-colors line-clamp-1 text-pretty md:line-clamp-2 md:text-lg/6"
                        >
                            {{ book.title }}
                        </h3>
                    </component>
                </div>
                <p
                    v-if="book.authors && book.authors.length > 0"
                    class="text-xs -mt-0.5 line-clamp-1 text-muted-foreground/65 md:mt-0.5 md:text-sm">
                    By {{ book.authors?.map((a) => a.name).join(', ') }}
                </p>
                <p
                    v-if="book.description"
                    :class="userRating ? 'line-clamp-1' : 'line-clamp-2'"
                    class="text-xs mt-0.5 text-muted-foreground md:line-clamp-2 md:mt-1">
                    {{ book.description_clean }}
                </p>
                <StarRatingDisplay
                    v-if="userRating"
                    class="mt-1"
                    :rating="userRating.value"
                    :star-width="15" />
            </div>
        </div>
        <div
            v-if="includeActions"
            :class="userRating ? '-mt-11' : '-mt-11'"
            class="w-full shrink-0 pl-24 md:max-w-64 md:ml-auto md:w-40 md:max-w-none md:pl-0">
            <BookActions :book="book" />
        </div>
    </div>
</template>
