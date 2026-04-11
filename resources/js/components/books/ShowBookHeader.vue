<script setup lang="ts">
import ShareButton from '@/components/ShareButton.vue'
import RatingForm from '@/components/books/RatingForm.vue'
import StarRatingDisplay from '@/components/StarRatingDisplay.vue'
import { Link } from '@inertiajs/vue3'
import { computed, PropType } from 'vue'
import type { Book } from '@/types/book'
import { Label } from '@/components/ui/label'
import { useRoute } from '@/composables/useRoute'
import { usePlural } from '@/composables/usePlural'

const props = defineProps({
    book: { type: Object as PropType<Book>, required: true },
    averageRating: { type: String, default: '0' },
    small: { type: Boolean, default: false }
})

const emit = defineEmits(['refresh'])

function refresh () {
    emit('refresh')
}

const primaryCategory = computed(() => {
    return props.book.primary_category ?? props.book.tags?.[0]?.name ?? null
})
</script>

<template>
    <div class="flex-col flex">
        <div class="flex mb-1 items-center justify-between gap-4">
            <Link
                v-if="primaryCategory"
                :href="
                    useRoute('books.search', { q: `tag: ${primaryCategory} ` })
                "
                :class="small ? 'text-sm' : 'text-xs'"
                class="font-sans font-normal tracking-wider text-primary uppercase"
            >
                {{ primaryCategory }}
            </Link>
            <div v-else />
            <ShareButton
                :url="book.links.show"
                variant="secondary"
                :title="book.title"
                :text="book.description"
                button-class="text-xs py-0"
                modal-title="Share this book"
            >
                <template v-if="!small">
                    Share book
                </template>
            </ShareButton>
        </div>
        <h2
            :class="small ? 'text-lg' : 'text-4xl'"
            class="font-serif font-semibold text-pretty"
        >
            {{ book.title }}
        </h2>
        <p
            v-if="book.authors && book.authors.length > 0"
            :class="small ? 'text-sm' : 'text-lg'"
            class="mt-1 font-serif italic text-muted-foreground"
        >
            By {{ book.authors.map((a) => a.name).join(", ") }}
        </p>
        <div class="mt-2.5 flex items-center divide-x gap-4">
            <div
                v-if="!small || book.in_library"
                class="grid gap-1 pr-4 py-2.5"
            >
                <Label> Your rating </Label>
                <RatingForm
                    v-if="book.in_library"
                    :only="['rating', 'book', 'averageRating']"
                    :book="book"
                    star-size="size-5"
                    @deleted="refresh"
                    @added="refresh"
                    @updated="refresh"
                />
                <div
                    v-else
                    class="text-sm text-muted-foreground">
                    Add book to library to review
                </div>
            </div>

            <div
                v-if="book.ratings_count && !small"
                class="grid gap-1 py-1">
                <div class="flex items-center gap-1">
                    <Label> Avg rating </Label>
                    <div class="text-xs text-muted-foreground">
                        {{ averageRating }} &mdash;
                        {{ book.ratings_count }}
                        {{ usePlural("rating", book.ratings_count) }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <StarRatingDisplay :rating="parseFloat(averageRating)" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
