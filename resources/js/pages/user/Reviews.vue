<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import SingleReview from '@/components/SingleReview.vue'
import CustomPagination from '@/components/CustomPagination.vue'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import { PropType } from 'vue'
import { Review } from '@/types/review'
import { Paginated } from '@/types/pagination'

defineOptions({ layout: AppLayout })

const props = defineProps({
    reviews: {
        type: Object as PropType<Paginated<Review>>,
        default: () => ({ data: [], links: {}, meta: {} })
    }
})
</script>

<template>
    <div>
        <PageTitle class="mb-4">
            Your Reviews
        </PageTitle>

        <div
            v-if="reviews.meta.total === 0 || reviews.data.length === 0"
            class="my-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center text-sm border-primary/10 text-muted-foreground md:py-12"
        >
            <Icon
                name="NotebookPen"
                class="size-8" />
            <h3 class="font-serif text-2xl font-semibold">
                Nothing to see here
            </h3>
            <p v-if="reviews.meta.total === 0">
                You haven't reviewed any books yet
            </p>
            <p v-else>
                There's no reviews on this page
            </p>
        </div>

        <ul
            v-else
            class="rounded-xl bg-white shadow divide-y divide-muted dark:divide-zinc-950 dark:bg-zinc-900">
            <li
                v-for="review in props.reviews.data"
                :key="review.uuid"
                class="flex flex-col items-start gap-4 p-4 group md:flex-row md:p-6"
            >
                <BookCardHorizontal
                    :book="review.book"
                    :include-actions="false"
                    class="md:w-1/3"
                />
                <SingleReview
                    :book="review.book"
                    :review="review"
                    class="flex-1 py-0"
                />
            </li>
        </ul>
        <CustomPagination
            v-if="reviews.meta.total > reviews.meta.per_page"
            class="my-4"
            :meta="props.reviews.meta" />
    </div>
</template>
