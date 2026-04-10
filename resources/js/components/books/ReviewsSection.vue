<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import SingleReview from '@/components/SingleReview.vue'
import ReviewForm from '@/components/books/ReviewForm.vue'
import { Book } from '@/types/book'
import { Review } from '@/types/review'
import { Card } from '@/components/ui/card'
import { computed, PropType, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { useAuthedUser } from '@/composables/useAuthedUser'

const props = defineProps({
    book: {
        type: Object as PropType<Book>,
        required: true
    },
    reviews: {
        type: Array as PropType<Review[]>,
        default: () => []
    }
})

const showAllReviews = ref(false)
const displayLimit = ref(3)
const { authed } = useAuthedUser()

const displayReviews = computed(() => {
    if (!props.reviews) {
        return []
    }
    if (showAllReviews.value) {
        return props.reviews
    }

    return props.reviews.slice(0, displayLimit.value)
})
</script>

<template>
    <div>
        <div v-if="!authed">
            <div
                class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center text-sm text-muted-foreground border-primary/10">
                <Icon
                    name="Star"
                    class="size-8" />
                <h3 class="font-serif text-2xl font-semibold">
                    Add a review
                </h3>
                <p>
                    Log in to add a review for this book.
                </p>
            </div>
        </div>

        <div class="flex flex-col">
            <ReviewForm
                v-if="authed"
                :book="book"
                :existing-review="book.user_review" />

            <div class="flex flex-col gap-4">
                <Card
                    v-for="review in displayReviews"
                    :key="review.uuid">
                    <SingleReview
                        :review="review"
                        :book="book" />
                </Card>
            </div>

            <div
                v-if="reviews && reviews?.length > displayLimit"
                class="my-3 flex items-center md:my-6"
            >
                <Separator class="flex flex-1 bg-muted" />
                <div class="flex px-4">
                    <Button
                        variant="link"
                        size="sm"
                        class="text-secondary-foreground"
                        @click="showAllReviews = !showAllReviews">
                        {{ showAllReviews ? 'Show less' : 'Show all' }}
                    </Button>
                </div>
                <Separator class="flex flex-1 bg-muted" />
            </div>
        </div>
    </div>
</template>
