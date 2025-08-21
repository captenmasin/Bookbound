<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import Loader from '@/components/Loader.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import JoinProTrigger from '@/components/JoinProTrigger.vue'
import SearchTipPopup from '@/components/SearchTipPopup.vue'
import HorizontalSkeleton from '@/components/books/HorizontalSkeleton.vue'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import { BookApiResult } from '@/types/book'
import { Badge } from '@/components/ui/badge'
import { useRoute } from '@/composables/useRoute'
import { Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator'
import { Input } from '@/components/ui/input/index.js'
import { computed, nextTick, PropType, ref } from 'vue'
import { Button } from '@/components/ui/button/index.js'
import { Deferred, Link, router } from '@inertiajs/vue3'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'

const props = defineProps({
    results: {
        type: Object as PropType<{ total: number; books: BookApiResult[] }>,
        default: () => ({
            total: 0,
            books: []
        })
    },
    perPage: {
        type: Number,
        default: 10
    },
    page: {
        type: Number,
        default: 1
    },
    initialQuery: {
        type: String,
        default: ''
    },
    previousSearches: {
        type: Array as PropType<{ id: number; search_term: string, search_term_normalised: string, type: string }[]>,
        default: () => []
    }
})

const query = ref(props.initialQuery)
const loadingMore = ref(false)

const { authedUser } = useAuthedUser()

function searchBooks () {
    router.get(
        useRoute('books.search'),
        {
            q: query.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            onBefore: () => {
                loadingMore.value = true
            },
            onFinish: () => {
                loadingMore.value = false
            }
        }
    )
}

function loadMore () {
    router.reload({
        data: {
            page: props.page + 1
        },
        only: ['results', 'page'],
        onBefore: () => {
            loadingMore.value = true
        },
        onFinish: () => {
            loadingMore.value = false
            // window.scrollTo({
            //     top: document.documentElement.scrollHeight - 100,
            //     behavior: 'smooth'
            // })
        }
    })
}

const hasSearch = computed(() => {
    return props.initialQuery !== '' && props.initialQuery !== null
})

function formatNumber (num: number) {
    return new Intl.NumberFormat('en-US', { style: 'decimal' }).format(num)
}

const searchInput = ref<HTMLInputElement | null>(null)

function clearInput () {
    query.value = ''
    // Focus the input after clearing
    nextTick(() => {
        searchInput.value.focus()
    })
}

defineOptions({
    layout: AppLayout
})
</script>

<template>
    <div>
        <div
            v-if="!hasSearch"
            class="mt-16 md:mt-24 w-full flex flex-col gap-6 items-center justify-between max-w-4xl mx-auto">
            <div class="flex flex-col items-center justify-center">
                <h1 class="text-4xl md:text-5xl font-serif font-semibold">
                    Find Book
                </h1>
                <p class="text-secondary-foreground">
                    Search for books by title, author, or keywords.
                </p>
            </div>
            <form
                class="flex w-full gap-4 md:flex-col md:gap-8"
                @submit.prevent="searchBooks">
                <div class="flex w-full flex-col gap-1">
                    <div class="relative">
                        <Input
                            id="query"
                            v-model.trim="query"
                            autofocus
                            class="px-4 py-6 text-lg"
                            placeholder="Search..." />
                        <div class="absolute inset-y-0 right-0 my-2 flex items-center pr-3">
                            <Button
                                id="searchSubmit"
                                type="submit"
                                variant="link"
                                class="cursor-pointer"
                                size="icon">
                                <span class="sr-only"> Search </span>
                                <Icon
                                    name="Search"
                                    class="size-5" />
                            </Button>
                        </div>
                    </div>
                    <SearchTipPopup />
                </div>
            </form>
            <div class="flex flex-col mt-0 gap-6 w-full md:hidden">
                <div
                    class="my-0 flex items-center">
                    <Separator class="flex flex-1" />
                    <span class="flex px-4 text-sm text-muted-foreground">or</span>
                    <Separator class="flex flex-1" />
                </div>
                <Button
                    as-child
                    class="w-full">
                    <Link :href="useRoute('books.scan')">
                        <Icon name="ScanBarcode" />
                        Scan Barcode
                    </Link>
                </Button>
            </div>
        </div>

        <div
            v-else>
            <div class="flex items-center justify-between">
                <PageTitle>Find Book</PageTitle>
            </div>

            <div class="mt-4 flex flex-col items-start gap-8 md:mt-4 md:flex-row">
                <aside class="top-4 left-0 w-full md:sticky md:w-80">
                    <form
                        class="flex gap-4 md:flex-col md:gap-8"
                        @submit.prevent="searchBooks">
                        <div class="flex w-full flex-col gap-1">
                            <div class="relative">
                                <Input
                                    id="query"
                                    ref="searchInput"
                                    v-model.trim="query"
                                    autofocus
                                    :class="hasSearch ? 'pr-18' : 'pr-10'"
                                    placeholder="Title or keywords..."
                                />
                                <div class="absolute inset-y-0 right-0 my-2 flex items-center pr-1">
                                    <Button
                                        v-if="query"
                                        type="button"
                                        variant="link"
                                        class="h-full text-primary cursor-pointer rounded-none border-r border-muted-foreground/10"
                                        size="icon"
                                        @click="clearInput"
                                    >
                                        <span class="sr-only"> Clear search </span>
                                        <Icon name="X" />
                                    </Button>

                                    <Button
                                        id="searchSubmit"
                                        type="submit"
                                        variant="link"
                                        class="cursor-pointer"
                                        size="icon">
                                        <span class="sr-only"> Search </span>
                                        <Icon name="Search" />
                                    </Button>
                                </div>
                            </div>
                            <SearchTipPopup />
                        </div>
                        <div
                            v-if="previousSearches && previousSearches.length"
                            class="hidden flex-col md:flex">
                            <h2 class="font-serif text-xl font-semibold text-accent-foreground">
                                Previous searches
                            </h2>
                            <ul class="divide-y divide-muted p-0">
                                <li
                                    v-for="previousSearch in previousSearches"
                                    :key="previousSearch.id"
                                    class="flex items-center gap-2 py-2">
                                    <Link
                                        class="text-sm text-accent-foreground hover:text-primary"
                                        :href="useRoute('books.search', { q: previousSearch.search_term })"
                                    >
                                        <Badge
                                            v-if="previousSearch.type !== 'query'"
                                            variant="secondary">
                                            {{ previousSearch.type }}
                                        </Badge>
                                        {{ previousSearch.search_term_normalised }}
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </form>
                    <!--                    <SearchTipPopup class="md:hidden" />-->
                </aside>
                <section class="flex w-full flex-1 flex-col md:w-auto">
                    <div
                        v-if="hasSearch && results && results.total > 0"
                        class="mb-4 flex justify-between text-sm font-medium text-muted-foreground">
                        <p class="hidden md:flex">
                            Found {{ formatNumber(results.total) }} books
                        </p>
                        <p class="hidden md:flex">
                            Showing {{ formatNumber(results.books.length) }} results
                        </p>
                        <p class="md:hidden">
                            Showing {{ formatNumber(results.books.length) }} of {{ formatNumber(results.total) }} results
                        </p>
                    </div>

                    <div
                        v-if="!hasSearch && !authedUser?.subscription.can_add_book"
                        class="mb-4">
                        <Alert class="flex flex-col justify-between gap-2 md:flex-row md:items-center md:gap-4">
                            <div>
                                <AlertTitle>Heads up!</AlertTitle>
                                <AlertDescription>
                                    You've reached the limit of books you can add with your current plan. Upgrade to Pro or remove some books to continue
                                    adding new ones.
                                </AlertDescription>
                            </div>
                            <JoinProTrigger class="w-full flex-1">
                                <Button
                                    size="sm"
                                    class="w-full flex-1">
                                    Upgrade now
                                </Button>
                            </JoinProTrigger>
                        </Alert>
                    </div>

                    <div
                        v-if="!hasSearch"
                        class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 text-center text-sm text-muted-foreground md:py-16"
                    >
                        <Icon
                            name="Search"
                            class="size-8" />
                        <h3 class="font-serif text-2xl font-semibold">
                            Start searching
                        </h3>
                        <p>Search for books by title or author.</p>
                        <p class="md:hidden">
                            or
                        </p>
                        <Button
                            as-child
                            class="md:hidden">
                            <Link :href="useRoute('books.scan')">
                                <Icon name="ScanBarcode" />
                                Scan Barcode
                            </Link>
                        </Button>
                    </div>

                    <Deferred
                        v-else
                        data="results">
                        <template #fallback>
                            <div>
                                <div class="mt-1 mb-4 flex items-center justify-between">
                                    <Skeleton class="h-4 w-32" />
                                    <Skeleton class="h-4 w-36" />
                                </div>
                                <div class="relative">
                                    <ul class="relative -mt-2 divide-y divide-muted-foreground/5">
                                        <li
                                            v-for="n in 3"
                                            :key="n">
                                            <HorizontalSkeleton />
                                        </li>
                                    </ul>
                                    <div class="absolute top-24 left-1/2 flex -translate-1/2 flex-col items-center gap-2 md:top-1/2">
                                        <Loader
                                            color="#FFFFFF"
                                            class="mx-auto hidden w-10 md:w-18 dark:flex" />
                                        <Loader
                                            color="#913608"
                                            class="mx-auto flex w-10 md:w-18 dark:hidden" />
                                        <p>Searching&hellip;</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div
                            v-if="results && results.total > 0"
                            class="-mt-4">
                            <div class="divide-y divide-muted-foreground/5">
                                <BookCardHorizontal
                                    v-for="book in hasSearch ? results.books : []"
                                    :key="book.identifier"
                                    class="py-4"
                                    :book="book" />
                            </div>

                            <div v-if="loadingMore">
                                <HorizontalSkeleton />
                            </div>

                            <div
                                v-if="results.books.length < results.total"
                                class="mt-4 mb-36 flex items-center justify-center">
                                <Button
                                    variant="secondary"
                                    :disabled="loadingMore"
                                    @click="loadMore">
                                    <Icon
                                        v-if="!loadingMore"
                                        name="Plus"
                                        class="w-4" />
                                    <Icon
                                        v-if="loadingMore"
                                        name="LoaderCircle"
                                        class="w-4 animate-spin" />
                                    Load {{ perPage }} more
                                </Button>
                            </div>
                        </div>
                        <div
                            v-else
                            class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 text-center text-sm text-muted-foreground md:py-16"
                        >
                            <Icon
                                name="BookDashed"
                                class="size-8" />
                            <h3 class="font-serif text-2xl font-semibold">
                                No books found
                            </h3>
                            <p>Try adjusting your search terms or use different keywords.</p>
                        </div>
                    </Deferred>
                </section>
            </div>
        </div>
    </div>
</template>
