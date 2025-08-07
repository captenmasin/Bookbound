<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import TagCloud from '@/components/TagCloud.vue'
import BookCard from '@/components/books/BookCard.vue'
import SingleActivity from '@/components/SingleActivity.vue'
import { Tag } from '@/types/tag'
import { Book } from '@/types/book'
import { Author } from '@/types/author'
import { Activity } from '@/types/activity'
import { Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { computed, onMounted, PropType } from 'vue'
import { UserBookStatus } from '@/enums/UserBookStatus'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { breakpointsTailwind, useBreakpoints } from '@vueuse/core'

type Stats = {
    booksInLibrary: number;
    completedBooks: number;
    readingBooks: number;
    pagesRead: number | string;
    planToRead: number | string;
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
    }
})

const { authedUser } = useAuthedUser()

const breakpoints = useBreakpoints(breakpointsTailwind)
const mdAndSmaller = breakpoints.smallerOrEqual('md')

const actions = [
    { name: 'View your library', smallName: 'Your library', icon: 'LibraryBig', url: useRoute('user.books.index') },
    { name: 'Find a new book', smallName: 'Find book', icon: 'Search', url: useRoute('books.search') },
    { name: 'Scan a barcode', smallName: 'Scan barcode', icon: 'ScanBarcode', url: useRoute('books.scan'), mobileOnly: true }
]

const stats = [
    {
        name: 'Books in library',
        value: props.statValues.booksInLibrary,
        link: useRoute('user.books.index'),
        icon: 'LibraryBig',
        color: 'text-primary'
    },
    {
        name: 'Completed',
        value: props.statValues.completedBooks,
        link: useRoute('user.books.index', { 'status[]': UserBookStatus.Completed }),
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
    router.prefetch(
        useRoute('user.books.index'),
        { method: 'get' },
        { cacheFor: '5m' }
    )

    router.prefetch(
        useRoute('books.search'),
        { method: 'get' },
        { cacheFor: '5m' }
    )
})

defineOptions({ layout: AppLayout })
</script>

<template>
    <div>
        <header class="mt-0 md:mt-6 mb-4 flex w-full gap-2.5 md:items-center justify-between flex-col xs:flex-row">
            <div
                v-if="authedUser"
                class="flex flex-col">
                <h1 class="font-serif text-2xl font-semibold text-foreground md:text-3xl">
                    Welcome back, {{ firstName }}
                </h1>
                <p class="text-sm text-accent-foreground">
                    Here's a quick look at your library
                </p>
            </div>
            <ul class="flex gap-1 md:gap-4">
                <li
                    v-for="action in actions"
                    :key="action.name"
                    :class="action.mobileOnly ? 'md:hidden' : ''">
                    <Button
                        :variant="mdAndSmaller ? 'ghost' : 'ghost'"
                        :size="mdAndSmaller ? 'icon' : 'sm'"
                        :as="Link"
                        :href="action.url"
                        class="md:text-primary">
                        <Icon
                            :name="action.icon"
                            class="size-4" />
                        <span class="sr-only">
                            {{ action.name }}
                        </span>
                        <span class="hidden xl:inline">
                            {{ action.name }}
                        </span>
                        <span class="hidden md:inline xl:hidden">
                            {{ action.smallName }}
                        </span>
                    </Button>
                </li>
            </ul>
        </header>

        <section>
            <div class="grid grid-cols-2 gap-2 md:grid-cols-4 md:gap-4">
                <Link
                    v-for="stat in stats"
                    :key="stat.name"
                    :href="stat.link"
                    prefetch
                    class="relative flex items-center justify-between rounded-md border-0 border-accent bg-secondary px-3 py-2 transition-all hover:bg-primary/20 md:p-4"
                >
                    <div>
                        <p class="text-sm text-current/60 pr-5">
                            {{ stat.name }}
                        </p>
                        <p class="text-xl font-semibold md:text-2xl">
                            {{ stat.value }}
                        </p>
                    </div>
                    <Icon
                        v-if="stat.icon"
                        :name="stat.icon"
                        class="absolute top-4 right-4 size-4 text-primary md:size-4" />
                </Link>
            </div>
        </section>

        <div class="mt-4 flex flex-col items-start gap-6 md:gap-8 md:mt-12 md:flex-row">
            <div class="flex w-full flex-col md:mt-0 md:w-auto md:flex-1">
                <section>
                    <h2
                        v-if="currentlyReading && currentlyReading.length"
                        class="mb-2 font-serif text-xl font-semibold text-accent-foreground">
                        Currently reading
                    </h2>

                    <div
                        v-if="currentlyReading && currentlyReading.length"
                        class="-mx-4 snap-x snap-mandatory overflow-x-auto px-4 md:mx-0 md:px-0">
                        <ul class="flex w-max flex-row gap-4 md:grid md:w-full md:grid-cols-5 md:gap-4">
                            <li
                                v-for="book in currentlyReading"
                                :key="book.identifier"
                                class="w-40 snap-center md:w-auto">
                                <BookCard :book="book" />
                            </li>
                            <li class="w-40 snap-center md:w-auto">
                                <Link
                                    :href="useRoute('books.search')"
                                    class="flex aspect-book size-full items-center justify-center rounded-md border-2 border-dashed border-primary/10 bg-secondary/50 p-4 text-center text-base font-semibold text-primary/50 transition-all hover:bg-secondary/75"
                                >
                                    Find more books
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <article
                        v-else
                        class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 md:py-12 text-center text-sm text-muted-foreground"
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

                <section
                    v-if="activities && activities.length"
                    class="mt-4 md:mt-12">
                    <div class="mb-1 flex items-center justify-between">
                        <h2 class="font-serif text-xl font-semibold text-accent-foreground">
                            Recent activity
                        </h2>
                        <Button
                            as-child
                            class="px-0"
                            variant="link">
                            <Link :href="useRoute('user.activities.index')">
                                View all
                            </Link>
                        </Button>
                    </div>
                    <ul class="divide-y divide-muted rounded-xl dark:divide-zinc-950 bg-white dark:bg-zinc-900 shadow">
                        <SingleActivity
                            v-for="activity in activities"
                            :key="activity.id"
                            :activity="activity" />
                    </ul>
                </section>
            </div>
            <div class="w-full md:w-72">
                <div>
                    <h2 class="mb-2 font-serif text-xl font-semibold text-accent-foreground">
                        Top tags
                    </h2>
                    <TagCloud
                        v-if="tags && tags.length"
                        :tags
                        :limit="10" />
                    <div v-else>
                        <p class="text-sm text-muted-foreground">
                            Add more books to see your top tags.
                        </p>
                    </div>
                </div>
                <div class="my-8">
                    <h2 class="mb-2 font-serif text-xl font-semibold text-accent-foreground">
                        Top authors
                    </h2>
                    <ul
                        v-if="authors && authors.length"
                        class="mt-2 divide-y divide-muted p-0">
                        <li
                            v-for="author in authors"
                            :key="author.uuid"
                            class="flex items-center gap-2 py-2">
                            <Link
                                class="text-sm text-accent-foreground hover:text-primary"
                                :href="useRoute('user.books.index', { author: author.slug })">
                                {{ author.name }}
                            </Link>
                        </li>
                    </ul>
                    <div v-else>
                        <p class="text-sm text-muted-foreground">
                            Add more books to see your top authors.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
