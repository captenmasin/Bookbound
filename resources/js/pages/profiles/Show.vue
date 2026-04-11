<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import SingleReview from '@/components/SingleReview.vue'
import SingleActivity from '@/components/SingleActivity.vue'
import CustomPagination from '@/components/CustomPagination.vue'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import type { PropType } from 'vue'
import type { Review } from '@/types/review'
import type { PublicUser } from '@/types/user'
import type { Activity } from '@/types/activity'
import type { Paginated } from '@/types/pagination'
import { useContrast } from '@/composables/useContrast'

defineOptions({ layout: AppLayout })

const props = defineProps({
    user: {
        type: Object as PropType<PublicUser>,
        required: true
    },
    reviews: {
        type: Object as PropType<Paginated<Review>>,
        required: true
    },
    activities: {
        type: Object as PropType<Paginated<Activity>>,
        required: true
    },
    is_owner: {
        type: Boolean,
        default: false
    }
})

const headerTextClass = computed(() =>
    useContrast(props.user.colour, 'text-zinc-900', 'text-white')
)
const headerMutedTextClass = computed(() =>
    useContrast(props.user.colour, 'text-zinc-800/75', 'text-white/80')
)
const headerBadgeClass = computed(() =>
    useContrast(
        props.user.colour,
        'bg-white/75 text-zinc-900 border-black/10',
        'bg-white/15 text-white border-white/20'
    )
)
</script>

<template>
    <div class="space-y-10">
        <section
            class="overflow-hidden rounded-3xl border p-6 shadow-sm md:p-8"
            :class="
                cn(
                    headerTextClass,
                    useContrast(
                        user.colour,
                        'border-black/10',
                        'border-white/15',
                    ),
                )
            "
            :style="{ backgroundColor: user.colour }"
        >
            <div
                class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <UserAvatar
                        :user="user"
                        :size="84"
                        class="size-20 shadow-sm md:size-24"
                        :class="
                            useContrast(
                                user.colour,
                                'ring-4 ring-white/80',
                                'ring-4 ring-black/10',
                            )
                        "
                    />
                    <div class="space-y-1">
                        <PageTitle class="text-4xl md:text-5xl">
                            {{ user.name }}
                        </PageTitle>
                        <p
                            class="text-base font-medium"
                            :class="headerMutedTextClass"
                        >
                            @{{ user.username }}
                        </p>
                        <div
                            class="flex flex-wrap gap-2 pt-2 text-sm"
                            :class="headerMutedTextClass"
                        >
                            <span
                                class="rounded-full border px-3 py-1 shadow-sm"
                                :class="headerBadgeClass"
                            >
                                {{ user.books_count }}
                                {{
                                    user.books_count === 1 ? "book" : "books"
                                }}
                                in library
                            </span>
                            <span
                                class="rounded-full border px-3 py-1 shadow-sm"
                                :class="headerBadgeClass"
                            >
                                {{ user.books_read_count }}
                                {{
                                    user.books_read_count === 1
                                        ? "book"
                                        : "books"
                                }}
                                read
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-serif text-3xl font-semibold text-primary">
                        Reviews
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Published thoughts from {{ user.name }}.
                    </p>
                </div>
            </div>

            <div
                v-if="reviews.meta.total === 0 || reviews.data.length === 0"
                class="my-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 text-center text-sm text-muted-foreground md:py-12"
            >
                <Icon
                    name="NotebookPen"
                    class="size-8" />
                <h3 class="font-serif text-2xl font-semibold">
                    No reviews yet
                </h3>
                <p>{{ user.name }} has not published any reviews.</p>
            </div>

            <ul
                v-else
                class="divide-y divide-muted rounded-xl bg-white shadow dark:divide-zinc-950 dark:bg-zinc-900"
            >
                <li
                    v-for="review in reviews.data"
                    :key="review.uuid"
                    class="group flex flex-col items-start gap-4 p-4 md:flex-row md:p-6"
                >
                    <BookCardHorizontal
                        v-if="review.book"
                        :book="review.book"
                        :include-actions="false"
                        class="md:w-1/3"
                    />
                    <SingleReview
                        v-if="review.book"
                        :book="review.book"
                        :review="review"
                        class="flex-1 py-0"
                    />
                </li>
            </ul>

            <CustomPagination
                v-if="reviews.meta.total > reviews.meta.per_page"
                :meta="reviews.meta"
                page-name="reviews_page"
            />
        </section>

        <section class="space-y-4">
            <div>
                <h2 class="font-serif text-3xl font-semibold text-primary">
                    Recent Activity
                </h2>
                <p class="text-sm text-muted-foreground">
                    Reading activity and updates from {{ user.name }}.
                </p>
            </div>

            <div
                v-if="
                    activities.meta.total === 0 || activities.data.length === 0
                "
                class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 text-center text-sm text-muted-foreground md:py-12"
            >
                <Icon
                    name="Activity"
                    class="size-8" />
                <h3 class="font-serif text-2xl font-semibold">
                    No activity yet
                </h3>
                <p>{{ user.name }} has not logged any public activity.</p>
            </div>

            <ul
                v-else
                class="divide-y divide-muted rounded-xl bg-white shadow dark:divide-zinc-950 dark:bg-zinc-900"
            >
                <SingleActivity
                    v-for="activity in activities.data"
                    :key="activity.id"
                    :activity="activity"
                />
            </ul>

            <CustomPagination
                v-if="activities.meta.total > activities.meta.per_page"
                :meta="activities.meta"
                page-name="activities_page"
            />
        </section>
    </div>
</template>
