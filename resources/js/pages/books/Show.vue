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
import { Card } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
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
const { authed } = useAuthedUser()

const data = [
    {
        title: 'Type',
        value: props.book.binding || 'N/A'
    },
    {
        title: 'Edition',
        value: props.book.edition || 'N/A'
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
const displayType = ref(
    getSingleSetting('single_book.default_section', 'reviews')
)

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
    <div class="md:mt-4 max-w-7xl mx-auto">
        <div class="grid grid-cols-12 gap-4 md:flex-row md:gap-16">
            <div class="flex w-full flex-col col-span-3">
                <UpdateBookCover :book="book">
                    <div class="overflow-hidden rounded-md aspect-book">
                        <Image
                            width="250"
                            class="object-cover size-full"
                            :src="book.cover"
                        />
                    </div>
                </UpdateBookCover>
                <div class="grid gap-1 mt-4">
                    <Label> Reading status </Label>
                    <BookActions
                        :book="book"
                        @removed="refreshRating"
                        @added="refreshRating"
                        @updated="refreshRating"
                    />
                </div>
            </div>

            <div class="col-span-9">
                <div class="hidden flex-col md:flex">
                    <div class="flex mb-1 items-center justify-between gap-4">
                        <p
                            v-if="book.primary_category"
                            class="font-sans text-xs font-normal tracking-wider text-primary uppercase"
                        >
                            {{ book.primary_category }}
                        </p>
                        <ShareButton
                            :url="book.links.show"
                            variant="secondary"
                            :title="book.title"
                            :text="book.description"
                            button-class="text-xs py-0"
                            modal-title="Share this book"
                        >
                            Share book
                        </ShareButton>
                    </div>
                    <h2 class="font-serif text-4xl font-semibold text-pretty">
                        {{ book.title }}
                    </h2>
                    <p
                        v-if="book.authors && book.authors.length > 0"
                        class="mt-2 text-lg font-serif italic text-muted-foreground"
                    >
                        By {{ book.authors.map((a) => a.name).join(", ") }}
                    </p>
                    <div
                        v-if="book.ratings_count"
                        class="mt-1 flex items-center gap-2"
                    >
                        <StarRatingDisplay
                            :rating="parseFloat(averageRating)"
                        />
                        <div class="mt-px text-xs text-muted-foreground">
                            {{ averageRating }} &mdash; {{ book.ratings_count }}
                            {{ usePlural("rating", book.ratings_count) }}
                        </div>
                    </div>
                </div>

                <Label
                    v-if="book.description"
                    class="md:mt-4 mb-1">
                    About
                </Label>
                <div
                    class="max-w-none font-serif prose prose-sm dark:prose-invert md:prose-base"
                    v-html="useMarkdown(book.description)"
                />

                <Card
                    class="grid grid-cols-1 divide-x gap-4 mt-4 py-6 md:grid-cols-12"
                >
                    <div
                        v-for="item in data"
                        :key="item.title"
                        :class="{
                            'col-span-4': item.title === 'Publisher',
                            'col-span-2': item.title !== 'Publisher',
                        }"
                        class="flex flex-col px-1"
                    >
                        <Label>
                            {{ item.title }}
                        </Label>
                        <p class="text-base font-serif mt-1 line-clamp-1">
                            {{ item.value }}
                        </p>
                    </div>
                </Card>

                <div class="grid mt-5 max-w-lg grid-cols-2 gap-12 items-center">
                    <div class="grid gap-1">
                        <Label> Your rating </Label>
                        <RatingForm
                            v-if="book.in_library"
                            :key="refreshKey"
                            :only="['rating', 'book', 'averageRating']"
                            :book="book"
                            @deleted="refreshRating"
                            @added="refreshRating"
                            @updated="refreshRating"
                        />
                        <div
                            v-else
                            class="text-sm text-muted-foreground">
                            Add book to library to review
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t pt-8 border-secondary">
                    <div class="flex w-full items-center justify-between">
                        <div class="mb-4 flex w-full md:w-auto">
                            <Tabs
                                v-model="displayType"
                                class="flex w-full flex-1 book-display-type"
                                :default-value="displayType"
                            >
                                <TabsList class="w-full">
                                    <TabsTrigger
                                        v-for="item in displayTypes"
                                        :key="item.value"
                                        :value="item.value"
                                        class="px-0 md:px-4"
                                    >
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
                            :book="book"
                        />
                        <ReviewsSection
                            v-if="displayType === 'reviews'"
                            class="reviews-section"
                            :book="book"
                            :reviews="reviews"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--            <div class="order-2 flex w-full flex-col md:order-3 md:w-48 xl:w-64">-->
    <!--                <div>-->
    <!--                    <BookActions-->
    <!--                        :book="book"-->
    <!--                        @removed="refreshRating"-->
    <!--                        @added="refreshRating"-->
    <!--                        @updated="refreshRating" />-->
    <!--                </div>-->

    <!--                <div-->
    <!--                    v-if="book.in_library"-->
    <!--                    class="mt-2 flex flex-col md:mt-4">-->
    <!--                    <h3 class="text-xs font-semibold text-muted-foreground md:text-sm">-->
    <!--                        Your rating-->
    <!--                    </h3>-->
    <!--                    <RatingForm-->
    <!--                        :key="refreshKey"-->
    <!--                        :only="['rating', 'book', 'averageRating']"-->
    <!--                        class="mt-1"-->
    <!--                        star-size="size-5"-->
    <!--                        :book="book"-->
    <!--                        @deleted="refreshRating"-->
    <!--                        @added="refreshRating"-->
    <!--                        @updated="refreshRating" />-->
    <!--                </div>-->

    <!--                <div class="mt-4 rounded-md bg-secondary md:bg-transparent">-->
    <!--                    <button-->
    <!--                        class="flex w-full items-center justify-between px-4 py-2 text-left md:p-0"-->
    <!--                        @click="detailsOpen = !detailsOpen">-->
    <!--                        <p class="font-semibold md:text-lg">-->
    <!--                            Details-->
    <!--                        </p>-->
    <!--                        <Icon-->
    <!--                            name="ChevronDown"-->
    <!--                            :class="detailsOpen ? 'rotate-180' : ''"-->
    <!--                            class="transition-transform duration-200 md:hidden" />-->
    <!--                    </button>-->
    <!--                    <div-->
    <!--                        :class="detailsOpen ? 'h-[calc-size(auto,size)]' : 'h-0'"-->
    <!--                        class="flex-col overflow-hidden rounded-b-md duration-300 transition-[height] bg-secondary text-secondary-foreground md:text-foreground md:flex md:h-auto md:bg-transparent">-->
    <!--            <dl class="px-4 py-2 md:px-0 md:py-0">-->
    <!--                <div-->
    <!--                    v-for="item in data"-->
    <!--                    :key="item.title"-->
    <!--                    class="flex flex-col justify-between py-2 xl:flex-row xl:items-center">-->
    <!--                    <dt class="font-medium text-sm/6">-->
    <!--                        {{ item.title }}-->
    <!--                    </dt>-->
    <!--                    <dd class="text-sm/6 text-muted-foreground sm:col-span-2 sm:mt-0 xl:text-right">-->
    <!--                        {{ item.value }}-->
    <!--                    </dd>-->
    <!--                </div>-->
    <!--            </dl>-->

    <!--                        <div-->
    <!--                            v-if="book.tags && book.tags.length > 0"-->
    <!--                            class="px-4 pt-2 pb-4 md:mt-2 md:px-0 md:py-0">-->
    <!--                            <p class="font-medium text-sm/6">-->
    <!--                                Tags-->
    <!--                            </p>-->
    <!--                            <TagCloud :tags="book.tags" />-->
    <!--                        </div>-->

    <!--                        <Deferred data="related">-->
    <!--                            <template #fallback />-->

    <!--                            <div-->
    <!--                                v-if="related && related.length > 0"-->
    <!--                                class="mt-4 hidden md:block">-->
    <!--                                <p class="font-medium text-sm/6">-->
    <!--                                    Related-->
    <!--                                </p>-->
    <!--                                <div class="-mx-1 flex flex-wrap">-->
    <!--                                    <div-->
    <!--                                        v-for="relatedBook in related"-->
    <!--                                        :key="relatedBook.identifier"-->
    <!--                                        class="w-1/2 p-1">-->
    <!--                                        <BookCard-->
    <!--                                            :hover="false"-->
    <!--                                            :book="relatedBook" />-->
    <!--                                    </div>-->
    <!--                                </div>-->
    <!--                            </div>-->
    <!--                        </Deferred>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
</template>
