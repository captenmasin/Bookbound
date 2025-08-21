<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import { Book } from '@/types/book'
import { PropType, ref } from 'vue'
import { useRoute } from '@/composables/useRoute'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    book: {
        type: Object as PropType<Book>,
        required: true
    },
    initialRating: {
        type: Number,
        default: 0
    },
    starSize: {
        type: String,
        default: 'size-6'
    },
    only: {
        type: Array as PropType<string[]>,
        default: () => []
    }
})

const form = useForm({
    rating: props.book.user_rating ?? {
        id: null,
        value: 0
    }
})

const currentlyHovering = ref<number | null>(null)

const emit = defineEmits(['added', 'updated', 'deleted'])

function updateRating (rating: number) {
    if (!form.rating.id || rating < 1 || rating > 5) {
        return
    }

    if (form.rating.value === rating) {
        clearRating()
        return
    }

    form.rating.value = rating

    form.put(
        useRoute('ratings.update', {
            book: props.book?.path,
            rating: props.book.user_rating
        }),
        {
            only: props.only.length ? props.only : [],
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                emit('updated', form.rating.value)
            }
        }
    )
}

function storeRating (rating: number) {
    if (rating < 1 || rating > 5) {
        return
    }

    form.rating.value = rating

    form.post(useRoute('ratings.store', { book: props.book }), {
        only: props.only.length ? props.only : [],
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('added', form.rating.value)
        }
    })
}

function clearRating () {
    form.rating.value = 0
    router.delete(
        useRoute('ratings.destroy', {
            book: props.book?.path,
            rating: props.book.user_rating
        }),
        {
            only: props.only.length ? props.only : [],
            preserveScroll: true,
            preserveState: true,
            onSuccess: (params) => {
                emit('deleted')
            }
        }
    )
}

function submit (rating: number | null) {
    if (rating === null || rating < 1 || rating > 5) {
        return
    }

    if (props.book.user_rating?.id) {
        updateRating(rating)
    } else {
        storeRating(rating)
    }
}
</script>

<template>
    <div class="flex items-center">
        <div class="flex">
            <button
                v-for="star in 5"
                :key="star"
                type="button"
                class="pr-0.5 rating-button"
                :class="`rating-button-${star}`"
                :aria-label="`Rate ${star} star`"
                @mouseover="currentlyHovering = star"
                @mouseleave="currentlyHovering = null"
                @focus="currentlyHovering = star"
                @blur="currentlyHovering = null"
                @click="submit(star)">
                <span class="cursor-pointer">
                    <Icon
                        name="Star"
                        :class="[
                            starSize,
                            !currentlyHovering && star <= form.rating.value ? 'fill-yellow-400 text-yellow-400' : 'text-primary/20 hover:fill-yellow-400 hover:text-yellow-400',
                            star <= currentlyHovering ? 'fill-yellow-400 text-yellow-400' : ''
                        ]"
                        class="stroke-[1.5px]"
                    />
                </span>
            </button>
        </div>
    </div>
</template>
