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
        class="group book-card-horizontal flex w-full flex-col gap-2 md:items-center"
        :class="narrow ? '' : 'md:flex-row md:gap-8'"
    >
        <div class="flex w-full gap-4">
            <component
                :is="linkTag"
                :href="url"
                :target="target"
                prefetch>
                <div class="relative aspect-book w-20 shrink-0 overflow-hidden shadow-sm md:w-40">
                    <!--                    <span-->
                    <!--                        v-if="book.type"-->
                    <!--                        class="absolute top-1 right-1 rounded-full bg-white/75 px-1.5 py-px text-[10px] text-zinc-900 opacity-0 transition-all group-hover:opacity-100"-->
                    <!--                    >-->
                    <!--                        {{ book.type }}-->
                    <!--                    </span>-->
                    <img
                        :src="book.cover ?? DefaultCover"
                        :alt="`Book cover image for ${book.title}`"
                        class="size-full bg-gray-200 object-cover">
                    <div class="bg-white/50 backdrop-blur text-black absolute top-2 right-2 px-2 text-center text-xs py-1">
                        {{ book.type }}
                    </div>
                </div>
            </component>
            <div class="flex w-full min-w-0 flex-col max-w-xl">
                <div class="flex">
                    <component
                        :is="linkTag"
                        :href="url"
                        :target="target"
                        prefetch>
                        <h3
                            :class="isLink ? 'hover:text-primary dark:hover:text-primary/80' : ''"
                            class="line-clamp-1 font-serif text-lg text-pretty transition-colors md:line-clamp-2 md:text-xl/7"
                        >
                            {{ book.title }}
                        </h3>
                    </component>
                </div>
                <p
                    v-if="book.authors && book.authors.length > 0"
                    class="-mt-0.5 line-clamp-1 text-xs text-muted-foreground/65 md:mt-0.5 md:text-sm">
                    By {{ book.authors?.map((a) => a.name).join(', ') }}
                </p>
                <p
                    v-if="book.description"
                    :class="userRating ? 'line-clamp-1' : 'line-clamp-2'"
                    class="mt-0.5 text-sm text-muted-foreground md:mt-1 md:line-clamp-3"
                >
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
            class="w-full shrink-0 pl-24 md:ml-auto md:w-40 md:max-w-64 md:max-w-none md:pl-0"
        >
            <BookActions
                :book="book" />
        </div>
    </div>
</template>
