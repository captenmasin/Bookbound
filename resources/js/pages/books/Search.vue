<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import Loader from '@/components/Loader.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import HorizontalSkeleton from '@/components/books/HorizontalSkeleton.vue'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import { BookApiResult } from '@/types/book'
import { computed, PropType, ref } from 'vue'
import { useRoute } from '@/composables/useRoute'
import { Skeleton } from '@/components/ui/skeleton'
import { Input } from '@/components/ui/input/index.js'
import { Button } from '@/components/ui/button/index.js'
import { Deferred, Link, router } from '@inertiajs/vue3'

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
        type: Array as PropType<{ id:number, search_term: string }[]>,
        default: () => []
    }
    // initialAuthor: {
    //     type: String,
    //     default: ''
    // }
})

const query = ref(props.initialQuery)
// const author = ref(props.initialAuthor)
const loadingMore = ref(false)

// const showAuthorField = ref(author.value !== '' && author.value !== null)
// const showAuthorField = ref(true)

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

defineOptions({
    layout: AppLayout
})
</script>

<template>
    <div>
        <div class="flex items-center justify-between">
            <PageTitle> Add Book </PageTitle>
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
                                :class="hasSearch ? 'pr-18' : 'pr-10'"
                                placeholder="Title or keywords..."
                            />
                            <div class="absolute inset-y-0 right-0 my-2 flex items-center pr-1">
                                <Button
                                    v-if="hasSearch"
                                    type="button"
                                    variant="link"
                                    as-child
                                    class="h-full cursor-pointer rounded-none border-r border-muted-foreground/10"
                                    size="icon"
                                >
                                    <Link :href="useRoute('books.search')">
                                        <span class="sr-only"> Clear search </span>
                                        <Icon name="X" />
                                    </Link>
                                </Button>

                                <Button
                                    type="submit"
                                    variant="link"
                                    class="cursor-pointer"
                                    size="icon">
                                    <span class="sr-only"> Search </span>
                                    <Icon name="Search" />
                                </Button>
                            </div>
                        </div>
                        <span class="pl-1 hidden md:flex text-xs text-muted-foreground">
                            Search by author using <code class="bg-muted px-1 rounded-sm">author: name</code>
                        </span>
                    </div>
                    <Deferred
                        data="previousSearches">
                        <template #fallback />

                        <div
                            v-if="previousSearches && previousSearches.length"
                            class="hidden md:flex flex-col">
                            <h2 class="font-serif text-xl font-semibold text-accent-foreground">
                                Previous searches...
                            </h2>
                            <ul
                                class="divide-y divide-muted p-0">
                                <li
                                    v-for="previousSearch in previousSearches"
                                    :key="previousSearch.id"
                                    class="flex items-center gap-2 py-2">
                                    <Link
                                        class="text-sm text-accent-foreground hover:text-primary"
                                        :href="useRoute('books.search', { q: previousSearch.search_term })">
                                        {{ previousSearch.search_term }}
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </Deferred>
                </form>
                <span class="pl-1 pt-1 text-xs flex md:hidden text-muted-foreground">
                    Search by author using <code class="bg-muted px-1 rounded-sm">author: name</code>
                </span>
            </aside>
            <section class="flex w-full flex-1 flex-col md:w-auto">
                <div
                    v-if="hasSearch && results && results.total > 0"
                    class="mb-4 flex font-medium justify-between text-sm text-muted-foreground">
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
</template>
