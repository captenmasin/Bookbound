<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import Image from '@/components/Image.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import TagCloud from '@/components/TagCloud.vue'
import BookCard from '@/components/books/BookCard.vue'
import ShareButton from '@/components/ShareButton.vue'
import RatingForm from '@/components/books/RatingForm.vue'
import BookActions from '@/components/books/BookActions.vue'
import NotesSection from '@/components/books/NotesSection.vue'
import StarRatingDisplay from '@/components/StarRatingDisplay.vue'
import ReviewsSection from '@/components/books/ReviewsSection.vue'
import UpdateBookCover from '@/components/books/UpdateBookCover.vue'
import { Review } from '@/types/review'
import type { Book } from '@/types/book'
import { type PropType, ref, watch } from 'vue'
import { Deferred, router } from '@inertiajs/vue3'
import { usePlural } from '@/composables/usePlural'
import { useMarkdown } from '@/composables/useMarkdown'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { useUserSettings } from '@/composables/useUserSettings'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'

const props = defineProps({
    book: { type: Object as PropType<Book>, required: true },
    averageRating: { type: String, default: '0' },
    related: { type: Array as PropType<Book[]> },
    reviews: {
        type: Array as PropType<Review[]>,
        default: () => []
    }
})

const { updateSingleSetting, getSingleSetting } = useUserSettings()
const { authed, authedUser } = useAuthedUser()

const data = [
    {
        title: 'Type',
        value: props.book.binding || 'N/A'
    },
    {
        title: 'Pages',
        value: props.book.page_count || 'N/A'
    },
    {
        title: 'Publisher',
        value: props.book.publisher?.name || 'N/A'
    },
    {
        title: 'Published',
        value: props.book.published_date || 'N/A'
    }
]

const displayTypes = [
    { value: 'notes', label: 'Notes' },
    { value: 'reviews', label: 'Reviews' }
]
const displayType = ref(getSingleSetting('single_book.default_section', 'reviews'))

const refreshKey = ref(1)

const detailsOpen = ref(false)

watch(displayType, (newType) => {
    if (authed.value) {
        updateSingleSetting('single_book.default_section', newType)
    }
})

function refreshRating () {
    router.reload({
        only: ['book', 'averageRating'],
        onSuccess: () => {
            refreshKey.value += 1
        }
    })
}

defineOptions({
    layout: AppLayout
})
</script>

<template>
    <div class="md:mt-4">
        <div class="flex flex-col md:flex-row gap-4 md:gap-10">
            <div class="flex w-full order-1 md:w-32 lg:w-44 xl:w-56 flex-col">
                <div class="flex w-full flex-col md:sticky top-4">
                    <div class="flex gap-4">
                        <div class="w-24 sm:w-32 md:w-full">
                            <UpdateBookCover
                                v-if="authedUser?.subscription.allow_custom_covers"
                                :book>
                                <div class="aspect-book overflow-hidden rounded-md">
                                    <Image
                                        width="250"
                                        class="size-full object-cover"
                                        :src="book.cover" />
                                </div>
                            </UpdateBookCover>
                            <div
                                v-else
                                class="aspect-book overflow-hidden rounded-md">
                                <Image
                                    width="250"
                                    class="size-full object-cover"
                                    :src="book.cover" />
                            </div>
                        </div>
                        <div class="flex flex-1 justify-between w-full md:hidden">
                            <div class="flex flex-col gap-1 flex-1">
                                <h2 class="font-serif text-lg/5.5 text-pretty font-semibold">
                                    {{ book.title }}
                                </h2>
                                <p
                                    v-if="book.authors && book.authors.length > 0"
                                    class="text-xs text-muted-foreground">
                                    By {{ book.authors.map((a) => a.name).join(', ') }}
                                </p>

                                <div
                                    v-if="book.ratings_count"
                                    class="flex flex-wrap items-center gap-1">
                                    <StarRatingDisplay
                                        :star-width="12"
                                        :rating="parseFloat(averageRating)" />
                                    <div class="mt-px text-xs text-muted-foreground">
                                        {{ averageRating }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex">
                                <ShareButton
                                    class="rounded-full"
                                    :url="book.links.show"
                                    :title="book.title"
                                    :text="book.description" />
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="book.in_library"
                        class="mt-4 hidden md:flex flex-col items-center">
                        <h3 class="text-xs font-semibold text-muted-foreground">
                            Your rating
                        </h3>
                        <RatingForm
                            :key="refreshKey"
                            :only="['rating', 'book', 'averageRating']"
                            class="mt-1"
                            :book="book"
                            @deleted="refreshRating"
                            @added="refreshRating"
                            @updated="refreshRating" />
                    </div>
                    <ShareButton
                        class="mt-4 mx-auto hidden md:flex"
                        :url="book.links.show"
                        :title="book.title"
                        :text="book.description"
                        modal-title="Share this book"
                    >
                        Share book
                    </ShareButton>
                </div>
            </div>
            <div class="flex w-full order-3 md:order-2 md:flex-1 flex-col">
                <div class="hidden md:flex flex-col">
                    <h2 class="font-serif text-3xl font-semibold text-pretty">
                        {{ book.title }}
                    </h2>
                    <p
                        v-if="book.authors && book.authors.length > 0"
                        class="mt-2 text-sm text-muted-foreground">
                        By {{ book.authors.map((a) => a.name).join(', ') }}
                    </p>
                    <div
                        v-if="book.ratings_count"
                        class="mt-1 flex items-center gap-2">
                        <StarRatingDisplay :rating="parseFloat(averageRating)" />
                        <div class="mt-px text-xs text-muted-foreground">
                            {{ averageRating }} &mdash; {{ book.ratings_count }} {{ usePlural('rating', book.ratings_count) }}
                        </div>
                    </div>
                </div>

                <div
                    class="prose md:mt-4 dark:prose-invert prose-sm md:prose-base max-w-none font-serif"
                    v-html="useMarkdown(book.description)" />

                <div class="mt-8 border-t border-secondary pt-8">
                    <div class="flex items-center w-full justify-between">
                        <div class="flex mb-4 w-full md:w-auto">
                            <Tabs
                                v-model="displayType"
                                class="flex w-full flex-1 book-display-type"
                                :default-value="displayType">
                                <TabsList class="w-full">
                                    <TabsTrigger
                                        v-for="item in displayTypes"
                                        :key="item.value"
                                        :value="item.value"
                                        class="px-0 md:px-4">
                                        {{ item.label }}
                                    </TabsTrigger>
                                </TabsList>
                            </Tabs>
                        </div>
                    </div>

                    <div>
                        <NotesSection
                            v-if="displayType === 'notes'"
                            class="notes-section"
                            :book="book" />
                        <ReviewsSection
                            v-if="displayType === 'reviews'"
                            class="reviews-section"
                            :book="book"
                            :reviews="reviews" />
                    </div>
                </div>
            </div>
            <div class="flex w-full order-2 md:order-3 md:w-48 xl:w-64 flex-col">
                <div>
                    <BookActions
                        :book="book"
                        @removed="refreshRating"
                        @added="refreshRating"
                        @updated="refreshRating" />
                </div>

                <div
                    v-if="book.in_library"
                    class="mt-2 flex md:hidden flex-col">
                    <h3 class="text-xs font-semibold text-muted-foreground">
                        Your rating
                    </h3>
                    <RatingForm
                        :key="refreshKey"
                        :only="['rating', 'book', 'averageRating']"
                        class="mt-1"
                        star-size="size-5"
                        :book="book"
                        @deleted="refreshRating"
                        @added="refreshRating"
                        @updated="refreshRating" />
                </div>

                <div class="bg-secondary md:bg-transparent rounded-md mt-4">
                    <button
                        class="text-left flex w-full py-2 px-4 md:p-0 items-center justify-between"
                        @click="detailsOpen = !detailsOpen">
                        <p class="md:text-lg font-semibold">
                            Details
                        </p>
                        <Icon
                            name="ChevronDown"
                            :class="detailsOpen ? 'rotate-180' : ''"
                            class="transition-transform md:hidden duration-200" />
                    </button>
                    <div
                        :class="detailsOpen ? 'h-[calc-size(auto,size)]' : 'h-0'"
                        class="flex-col md:flex md:bg-transparent transition-[height] duration-300 overflow-hidden md:h-auto md:text-foreground bg-secondary text-secondary-foreground rounded-b-md">
                        <dl class="py-2 md:py-0 px-4 md:px-0">
                            <div
                                v-for="item in data"
                                :key="item.title"
                                class="py-2 flex flex-col xl:flex-row xl:items-center justify-between">
                                <dt class="text-sm/6 font-medium">
                                    {{ item.title }}
                                </dt>
                                <dd class="xl:text-right text-sm/6 text-muted-foreground sm:col-span-2 sm:mt-0">
                                    {{ item.value }}
                                </dd>
                            </div>
                        </dl>

                        <div
                            v-if="book.tags && book.tags.length > 0"
                            class="md:mt-2 pt-2 pb-4 md:py-0 px-4 md:px-0">
                            <p class="text-sm/6 font-medium">
                                Tags
                            </p>
                            <TagCloud :tags="book.tags" />
                        </div>

                        <Deferred data="related">
                            <template #fallback />

                            <div
                                v-if="related && related.length > 0"
                                class="mt-4 hidden md:block">
                                <p class="text-sm/6 font-medium">
                                    Related
                                </p>
                                <div class="-mx-1 flex flex-wrap">
                                    <div
                                        v-for="relatedBook in related"
                                        :key="relatedBook.identifier"
                                        class="w-1/2 p-1">
                                        <BookCard
                                            :hover="false"
                                            :book="relatedBook" />
                                    </div>
                                </div>
                            </div>
                        </Deferred>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
