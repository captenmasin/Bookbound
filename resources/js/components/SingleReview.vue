<script setup lang="ts">
import UserAvatar from '@/components/UserAvatar.vue'
import StarRatingDisplay from '@/components/StarRatingDisplay.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import { cn } from '@/lib/utils'
import { Book } from '@/types/book'
import { Review } from '@/types/review'
import { computed, PropType } from 'vue'
import { router } from '@inertiajs/vue3'
import { useDateFormat } from '@vueuse/core'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { useMarkdown } from '@/composables/useMarkdown'
import { useAuthedUser } from '@/composables/useAuthedUser'

const props = defineProps({
    book: {
        type: Object as PropType<Book>,
        required: true
    },
    review: {
        type: Object as PropType<Review>,
        required: true
    },
    class: {
        type: String,
        default: ''
    }
})

const { authedUser } = useAuthedUser()

function formatDate (date: string | Date) {
    return useDateFormat(date, 'Mo MMMM h:ma')
}

const emit = defineEmits(['deleted'])

function deleteReview () {
    router.delete(useRoute('reviews.destroy', { book: props.book, review: props.review }), {
        preserveScroll: true,
        only: ['reviews', 'book'],
        onSuccess: () => {
            emit('deleted')
        }
    })
}

const isUserReview = computed(() => {
    return props.review.user && props.review.user.id === authedUser.value?.id
})
</script>

<template>
    <div :class="cn('group py-6', props.class)">
        <div class="flex items-center justify-between">
            <h3
                v-if="review.title"
                class="text-xl font-semibold">
                {{ review.title }}
            </h3>

            <div
                v-if="isUserReview"
                class="flex transition-all group-hover:opacity-100 md:opacity-0">
                <ConfirmationModal
                    @confirmed="deleteReview()">
                    <template #title>
                        Are you sure you want to delete this review?
                    </template>
                    <template #description>
                        This action cannot be undone.
                    </template>
                    <template #trigger>
                        <Button
                            :id="`delete-review-` + review.id"
                            variant="link"
                            class="h-auto py-0 text-xs text-destructive">
                            Delete
                        </Button>
                    </template>
                </ConfirmationModal>
            </div>
        </div>

        <!--        <RatingForm-->
        <!--            v-if="review.rating?.value && isUserReview"-->
        <!--            class="mt-px mb-2"-->
        <!--            star-size="size-5"-->
        <!--            :only="['ratings', 'rating', 'book', 'averageRating']"-->
        <!--            :book="book" />-->

        <StarRatingDisplay
            v-if="review.rating?.value"
            :rating="review.rating.value"
            class="mt-px mb-2" />
        <div
            class="max-w-none prose prose-sm dark:prose-invert"
            v-html="useMarkdown(review.content)" />

        <div
            v-if="review.user"
            class="mt-4 flex items-center gap-2.5">
            <div>
                <UserAvatar
                    :user="review.user"
                    :size="28"
                    class="size-10" />
            </div>
            <div class="flex flex-col gap-px">
                <Label>
                    {{ review.user?.name || 'Anonymous' }}
                </Label>
                <div class="text-xs font-semibold text-muted-foreground">
                    {{ formatDate(review.created_at) }}
                </div>
            </div>
        </div>
    </div>
</template>
