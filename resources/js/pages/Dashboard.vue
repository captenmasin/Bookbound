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
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { UserBookStatus } from '@/enums/UserBookStatus'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, onMounted, PropType, ref } from 'vue'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { useCookies } from '@vueuse/integrations/useCookies'
import { breakpointsTailwind, useBreakpoints } from '@vueuse/core'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Dialog, DialogContent, DialogClose, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'

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

const cookies = useCookies(['displayProBanner'])

const page = usePage()
const { authedUser, subscribedToPro } = useAuthedUser()
const hasUpgraded = ref(false)

const breakpoints = useBreakpoints(breakpointsTailwind)
const mdAndSmaller = breakpoints.smallerOrEqual('md')

const displayProBanner = ref(false)

const actions = [
    {
        name: 'View your library',
        smallName: 'Your library',
        icon: 'LibraryBig',
        url: useRoute('user.books.index')
    },
    {
        name: 'Find a new book',
        smallName: 'Find book',
        icon: 'Search',
        url: useRoute('books.search')
    },
    {
        name: 'Scan a barcode',
        smallName: 'Scan barcode',
        icon: 'ScanBarcode',
        url: useRoute('books.scan'),
        mobileOnly: true
    }
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

function closeProBanner () {
    displayProBanner.value = false
    cookies.set('displayProBanner', false)
}

onMounted(() => {
    router.prefetch(useRoute('user.books.index'), { method: 'get' }, { cacheFor: '5m' })

    router.prefetch(useRoute('books.search'), { method: 'get' }, { cacheFor: '5m' })

    if (page.props.flash?.upgrade_success) {
        hasUpgraded.value = true
    }

    if (!subscribedToPro.value && cookies.get('displayProBanner') !== false) {
        displayProBanner.value = true
    }
})

defineOptions({ layout: AppLayout })
</script>

<template>
    <div>
        <Transition>
            <Alert
                v-show="displayProBanner"
                class="relative mb-6 text-white bg-primary dark:bg-neutral-950 dark:border-neutral-900 border-primary p-6 pb-6 md:mb-0">
                <Icon
                    name="Sparkles"
                    class="mt-1 size-6" />
                <AlertTitle class="font-serif text-lg text-white md:text-xl">
                    Get more with Pro!
                </AlertTitle>
                <AlertDescription>
                    <p class="text-white">
                        Upgrade to Pro for advanced features like unlimited books, private notes, and more.
                    </p>
                    <div class="mt-4 flex items-center gap-4">
                        <Button
                            variant="white"
                            class="text-primary hover:text-white"
                            as-child>
                            <a
                                :href="useRoute('checkout')">
                                Upgrade now
                            </a>
                        </Button>
                    </div>
                </AlertDescription>
                <button
                    class="absolute top-3 right-4 cursor-pointer text-white/50 size-4 hover:text-white"
                    @click="closeProBanner">
                    <Icon
                        name="X"
                        class="size-4" />
                </button>
            </Alert>
        </Transition>

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
                            :href="useRoute('billing')">
                            Manage Billing
                        </a>
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

        <header class="mt-0 mb-4 flex w-full flex-col justify-between gap-2.5 xs:flex-row md:mt-6 md:items-center">
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
                        class="md:text-primary"
                    >
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
                    class="relative flex items-center justify-between rounded-md border-0 px-3 py-2 border-accent bg-secondary hover:bg-primary/20 active:bg-primary/20 md:p-4 md:transition-all"
                >
                    <div>
                        <p class="pr-5 text-sm text-current/60">
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

        <div class="mt-4 flex flex-col items-start gap-6 md:mt-12 md:flex-row md:gap-8">
            <div class="flex w-full flex-col md:mt-0 md:w-auto md:flex-1">
                <section>
                    <h2
                        v-if="currentlyReading && currentlyReading.length"
                        class="font-serif text-xl font-semibold text-accent-foreground">
                        Currently reading
                    </h2>

                    <div
                        v-if="currentlyReading && currentlyReading.length"
                        class="-mx-4 -mt-2 snap-x snap-mandatory overflow-x-auto px-4 py-4 md:-mx-2 md:px-2">
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
                                    class="flex items-center justify-center rounded-md border-2 border-dashed p-4 text-center text-base font-semibold transition-all aspect-book size-full border-primary/10 bg-secondary/50 text-primary/50 hover:bg-secondary/75"
                                >
                                    Find more books
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <article
                        v-else
                        class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center text-sm border-primary/10 text-muted-foreground md:py-12"
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
                    <ul class="rounded-xl bg-white shadow divide-y divide-muted dark:divide-zinc-950 dark:bg-zinc-900">
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
                        class="mt-2 p-0 divide-y divide-muted">
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
                </div>
            </div>
        </div>
    </div>
</template>
