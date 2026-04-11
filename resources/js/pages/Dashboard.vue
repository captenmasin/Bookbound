<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import TagCloud from '@/components/TagCloud.vue'
import BookCard from '@/components/books/BookCard.vue'
import DashboardStats from '@/components/DashboardStats.vue'
import BookDisplay from '@/components/books/BookDisplay.vue'
import { Tag } from '@/types/tag'
import { Author } from '@/types/author'
import { useTimeAgo } from '@vueuse/core'
import { Activity } from '@/types/activity'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { Skeleton } from '@/components/ui/skeleton'
import { Card, CardTitle } from '@/components/ui/card'
import { UserBookStatus } from '@/enums/UserBookStatus'
import { Book, BookRecommendation } from '@/types/book'
import { computed, onMounted, PropType, ref } from 'vue'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { Deferred, Link, router, usePage } from '@inertiajs/vue3'
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'

type Stats = {
    booksInLibrary: number;
    completedBooks: number;
    readingBooks: number;
    pagesRead: number | string;
    planToRead: number | string;
};

type Weather = {
    condition: string;
    icon: string;
    label: string;
    isFallback: boolean;
};

const props = defineProps({
    activities: {
        type: Array as PropType<Activity[]>,
        default: () => []
    },
    currentlyReading: {
        type: Array as PropType<Book[]>,
        default: () => []
    },
    recommendations: {
        type: Array as PropType<BookRecommendation[]>,
        default: () => []
    },
    statValues: {
        type: Object as PropType<Stats>,
        default: () => ({})
    },
    tags: {
        type: Array as PropType<Tag[]>,
        default: () => []
    },
    authors: {
        type: Array as PropType<Author[]>,
        default: () => []
    },
    topGenres: {
        type: Array as PropType<string[]>,
        default: () => []
    },
    weather: {
        type: Object as PropType<Weather | null>,
        default: null
    },
    insights: {
        type: Object,

        default: () => ({
            read: 0,
            dropped: 0
        })
    }
})

const page = usePage()
const { authedUser } = useAuthedUser()
const hasUpgraded = ref(false)

const stats = [
    {
        name: 'Books in library',
        value: props.statValues.booksInLibrary,
        link: useRoute('user.books.index'),
        icon: 'LibraryBig',
        color: 'text-primary'
    },
    {
        name: 'Read',
        value: props.statValues.completedBooks,
        link: useRoute('user.books.index', { 'status[]': UserBookStatus.Read }),
        icon: 'CircleCheck',
        color: 'text-green-500'
    },
    {
        name: 'Reading',
        value: props.statValues.readingBooks,
        link: useRoute('user.books.index', { 'status[]': UserBookStatus.Reading }),
        icon: 'BookOpen',
        color: 'text-yellow-500'
    },
    {
        name: 'Plan to read',
        // value: props.statValues.pagesRead,
        value: props.statValues.planToRead,
        link: useRoute('user.books.index', { 'status[]': UserBookStatus.PlanToRead }),
        icon: 'BookMarked',
        color: 'text-blue-500'
    }
    // {
    //     name: 'Pages read this year',
    //     // value: props.statValues.pagesRead,
    //     value: '//TODO',
    //     link: useRoute('user.books.index'),
    //     icon: 'Hash',
    //     color: 'text-blue-500'
    // }
]

const firstName = computed(() => {
    if (!authedUser.value) return ''
    return authedUser.value.name.split(' ')[0]
})

onMounted(() => {
    router.prefetch(useRoute('user.books.index'), { method: 'get' }, { cacheFor: '5m' })

    router.prefetch(useRoute('books.search'), { method: 'get' }, { cacheFor: '5m' })

    if (page.props.flash?.upgrade_success) {
        hasUpgraded.value = true
    }
})

const timeOfDay = computed(() => {
    const hour = new Date().getHours()
    if (hour >= 5 && hour < 12) return 'morning'
    if (hour >= 12 && hour < 18) return 'afternoon'
    return 'evening'
})

const fallbackWeather = computed(() => {
    if (timeOfDay.value === 'morning') {
        return {
            icon: 'Sun',
            label: 'Morning'
        }
    }

    if (timeOfDay.value === 'afternoon') {
        return {
            icon: 'SunMedium',
            label: 'Afternoon'
        }
    }

    return {
        icon: 'MoonStar',
        label: 'Evening'
    }
})

const greetingWeather = computed(() => {
    if (!props.weather) {
        return fallbackWeather.value
    }

    if (props.weather.isFallback) {
        return fallbackWeather.value
    }

    return {
        icon: props.weather.icon,
        label: props.weather.label
    }
})

defineOptions({ layout: AppLayout })
</script>

<template>
    <div>
        <Dialog
            v-model:open="hasUpgraded"
            class="relative mb-6 md:mb-0">
            <DialogContent class="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Icon
                            name="Sparkles"
                            class="size-6 text-primary" />
                        <h2 class="font-serif text-xl text-primary">
                            Welcome to {{ page.props.app.name }} Pro!
                        </h2>
                    </DialogTitle>
                </DialogHeader>
                <div class="mt-4">
                    <p class="text-secondary-foreground">
                        You've successfully upgraded to Pro. Enjoy all the premium features and benefits.
                    </p>
                </div>
                <DialogFooter class="mt-4">
                    <Button as-child>
                        <a
                            target="_blank"
                            :href="useRoute('billing')"> Manage Billing </a>
                    </Button>
                    <DialogClose as-child>
                        <Button
                            variant="outline"
                            class="ml-2">
                            Close
                        </Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <header class="mb-8 flex w-full flex-col justify-between gap-2.5 xs:flex-row md:mt-10 md:items-center">
            <div
                v-if="authedUser"
                class="flex w-full flex-col">
                <h1 class="flex w-full items-center gap-2 font-serif text-2xl font-bold tracking-tight text-primary md:gap-3 md:text-5xl">
                    <span
                        class="inline-flex items-center"
                        :aria-label="greetingWeather.label">
                        <Icon
                            :name="greetingWeather.icon"
                            class="size-5 text-primary md:size-10" />
                        <span class="sr-only">{{ greetingWeather.label }}</span>
                    </span>

                    Good {{ timeOfDay }}, {{ firstName }}
                </h1>
                <p class="font-serif text-lg text-primary/80 italic">
                    Here's a quick look at your library
                </p>
            </div>
        </header>

        <section>
            <div class="grid grid-cols-2 gap-2 md:grid-cols-4 md:gap-6">
                <Link
                    v-for="stat in stats"
                    :key="stat.name"
                    :href="stat.link"
                    prefetch
                    class="group relative flex items-center border border-card-outline bg-card px-3 md:px-4 py-2.5 md:py-3 text-primary hover:brightness-95 md:p-8 md:transition-all"
                >
                    <div class="w-full">
                        <p class="mb-1 md:mb-2 md:pr-5 text-xs tracking-wider text-primary/50 uppercase">
                            {{ stat.name }}
                        </p>
                        <div class="flex w-full items-center justify-between">
                            <p class="font-serif text-xl font-bold text-primary md:text-4xl">
                                {{ stat.value }}
                            </p>
                            <Icon
                                v-if="stat.icon"
                                :name="stat.icon"
                                class="text-primary opacity-20 group-hover:opacity-100 md:size-8" />
                        </div>
                    </div>
                </Link>
            </div>
        </section>

        <div class="mt-10 flex flex-col items-start gap-6 md:mt-16 md:flex-row md:gap-18">
            <div class="flex w-full flex-col md:mt-0 md:w-auto md:flex-1">
                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <h2
                            v-if="currentlyReading && currentlyReading.length"
                            class="font-serif text-2xl font-semibold text-primary">
                            Currently reading
                        </h2>
                        <Button
                            variant="link"
                            as-child>
                            <Link :href="useRoute('user.books.index')">
                                View full library
                            </Link>
                        </Button>
                    </div>

                    <div v-if="currentlyReading && currentlyReading.length">
                        <ul class="flex flex-col gap-4 md:gap-8">
                            <li
                                v-for="book in currentlyReading"
                                :key="book.identifier">
                                <BookDisplay :book="book" />
                            </li>
                        </ul>
                    </div>

                    <article
                        v-else
                        class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 text-center text-sm text-muted-foreground md:py-12"
                    >
                        <Icon
                            name="BookOpen"
                            class="size-8" />
                        <h2 class="font-serif text-2xl font-semibold">
                            Currently reading
                        </h2>
                        <p>You aren't reading anything right now</p>
                        <Button
                            class="mt-2"
                            as-child>
                            <Link :href="useRoute('books.search')">
                                Add books to your library
                            </Link>
                        </Button>
                    </article>
                </section>

                <section class="mt-10 md:mt-12">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-serif text-2xl font-semibold text-primary">
                            Recommended next
                        </h2>
                        <!--                        <Button variant="link" as-child>-->
                        <!--                            <Link :href="useRoute('books.search')"> Browse books </Link>-->
                        <!--                        </Button>-->
                    </div>

                    <Deferred data="recommendations">
                        <template #fallback>
                            <div
                                class="-mx-4 flex gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 md:grid-cols-3 lg:grid-cols-5"
                            >
                                <Skeleton
                                    v-for="n in 5"
                                    :key="n"
                                    class="aspect-book w-[200px] sm:w-auto" />
                            </div>
                        </template>

                        <div
                            v-if="recommendations && recommendations.length"
                            class="-mx-4 flex gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 md:grid-cols-3 lg:grid-cols-5"
                        >
                            <BookCard
                                v-for="recommendation in recommendations"
                                :key="recommendation.book.id"
                                class="aspect-book w-[200px] shrink-0 sm:w-auto"
                                :book="recommendation.book"
                            />
                        </div>

                        <article
                            v-else
                            class="rounded-lg border-2 border-dashed border-primary/10 px-4 py-16 text-center text-sm text-muted-foreground"
                        >
                            <Icon
                                name="Sparkles"
                                class="mx-auto size-6" />
                            <!--                            <h3 class="mt-2 font-serif text-2xl font-semibold text-primary">-->
                            <!--                                Recommended next-->
                            <!--                            </h3>-->
                            <p class="mt-2">
                                Add a couple more books to your library to unlock recommendations.
                            </p>
                        </article>
                    </Deferred>
                </section>

                <section
                    v-if="activities && activities.length"
                    class="mt-8 md:mt-12">
                    <div class="mb-1 flex items-center justify-between">
                        <h2 class="mb-2 font-serif text-2xl font-semibold text-primary">
                            Recent activity
                        </h2>
                        <Button
                            as-child
                            variant="link">
                            <Link :href="useRoute('user.activities.index')">
                                View all activities
                            </Link>
                        </Button>
                    </div>

                    <div class="rounded-md bg-white p-6 pb-0 ring-1 ring-primary/10">
                        <div class="ml-2 space-y-0 border-l border-primary/10 pl-8">
                            <div
                                v-for="(activity, index) in activities"
                                :key="activity.id"
                                class="relative pb-10">
                                <div
                                    :class="index === 0 ? 'border-primary bg-primary' : 'border-primary bg-white'"
                                    class="absolute top-1 -left-9.5 h-3 w-3 rounded-full border ring-4 ring-white"
                                />
                                <p class="font-label mb-1 text-[10px] tracking-widest text-primary/40 uppercase">
                                    {{ useTimeAgo(activity.created_at) }}
                                </p>
                                <p
                                    class="font-serif text-sm text-pretty md:text-base"
                                    v-html="activity.description" />
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="flex w-full flex-col gap-8 md:w-1/4">
                <DashboardStats
                    :insights="insights"
                    :top-genres="topGenres" />

                <Card>
                    <CardTitle class="mb-2">
                        Top tags
                    </CardTitle>
                    <TagCloud
                        v-if="tags && tags.length"
                        :tags
                        :limit="10" />
                    <div v-else>
                        <p class="text-sm text-muted-foreground">
                            Add more books to see your top tags.
                        </p>
                    </div>
                </Card>
                <Card>
                    <CardTitle class="mb-2">
                        Top authors
                    </CardTitle>

                    <ul
                        v-if="authors && authors.length"
                        class="divide-y divide-primary/10 p-0">
                        <li
                            v-for="author in authors"
                            :key="author.uuid"
                            class="flex items-center gap-2 py-2">
                            <Link
                                class="text-sm text-accent-foreground hover:text-primary"
                                :href="useRoute('books.search', { q: 'author: ' + author.name })"
                            >
                                {{ author.name }}
                            </Link>
                        </li>
                    </ul>
                    <div v-else>
                        <p class="text-sm text-muted-foreground">
                            Add more books to see your top authors.
                        </p>
                    </div>
                </Card>
            </div>
        </div>
    </div>
</template>
