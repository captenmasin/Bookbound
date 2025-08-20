<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import BookCard from '@/components/books/BookCard.vue'
import CheckboxList from '@/components/CheckboxList.vue'
import ShelfView from '@/components/books/ShelfView.vue'
import BookViewTabs from '@/components/books/BookViewTabs.vue'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import { Tag } from '@/types/tag'
import type { Book } from '@/types/book'
import type { Author } from '@/types/author'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { useUrlSearchParams } from '@vueuse/core'
import { useRoute } from '@/composables/useRoute'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useUserSettings } from '@/composables/useUserSettings'
import { useUserBookStatus } from '@/composables/useUserBookStatus'
import { computed, ref, watch, type PropType, nextTick } from 'vue'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

/* --------------------------------------------------------------------------
 * Props & Refs
 * -------------------------------------------------------------------------- */
const props = defineProps({
    books: Array as PropType<Book[]>,
    totalBooks: { type: Number, default: 0 },
    selectedStatuses: { type: Array as PropType<string[]>, default: () => [] },
    selectedAuthor: { type: String as PropType<string | null>, default: null },
    selectedTag: { type: String as PropType<string | null>, default: null },
    selectedSort: { type: String, default: null },
    selectedOrder: { type: String, default: 'desc' },
    authors: { type: Array as PropType<Author[]>, default: () => [] },
    tags: { type: Array as PropType<Tag[]>, default: () => [] }
})

const params = useUrlSearchParams('history')
const { possibleStatuses } = useUserBookStatus()

/** Search --------------------------------------------------------------- */
const searchInput = ref<HTMLInputElement | null>(null)
const search = ref(String(params.search || ''))
const currentSearch = ref(String(params.search || ''))
const displayFilters = ref(search.value !== '' || props.selectedStatuses.length > 0 || props.selectedAuthor !== null)

/** Filters -------------------------------------------------------------- */
const status = ref<string[]>(props.selectedStatuses)
const author = ref<string | null>(props.selectedAuthor)
const tag = ref<string | null>(props.selectedTag)
const sort = ref(props.selectedSort)
const order = ref<'asc' | 'desc'>(props.selectedOrder as 'asc' | 'desc')

/** View preferences ----------------------------------------------------- */
const page = usePage()
const view = ref<string>(page.props.auth.user.settings?.library.view ?? 'list')
const { updateSingleSetting } = useUserSettings()

/** Options -------------------------------------------------------------- */
const sortOptions = [
    { label: 'Added', value: 'added' },
    { label: 'Title', value: 'title' },
    { label: 'Author', value: 'author' },
    { label: 'Rating', value: 'rating' },
    { label: 'Published', value: 'published_date' },
    { label: 'Colour', value: 'colour' }
] as const

/* --------------------------------------------------------------------------
 * Watchers
 * -------------------------------------------------------------------------- */
watch(
    [author, tag, status, sort, order],
    () => {
        Object.assign(params, {
            author: author.value,
            tag: tag.value,
            status: status.value,
            sort: sort.value,
            order: order.value
        })
        submitForm()
    },
    { deep: true }
)

watch(view, (newView) => updateSingleSetting('library.view', newView))

/* --------------------------------------------------------------------------
 * Computed
 * -------------------------------------------------------------------------- */
const filteredBooks = computed(() => props.books ?? [])

const hasFiltered = computed(
    () => !!currentSearch.value || !!author.value || !!tag.value || sort.value !== null || status.value.length > 0 || order.value !== 'desc'
)

/* --------------------------------------------------------------------------
 * Methods
 * -------------------------------------------------------------------------- */
function submitForm () {
    currentSearch.value = search.value

    nextTick(() => {
        searchInput.value?.blur()
    })

    router.get(
        useRoute('user.books.index'),
        {
            search: search.value,
            author: author.value,
            tag: tag.value,
            status: status.value,
            sort: sort.value,
            order: order.value
        },
        { preserveScroll: true, preserveState: true, replace: true }
    )
}

watch(
    [filteredBooks, hasFiltered],
    () => {
        // Only save to localStorage if no filters are applied
        if (!hasFiltered.value) {
            const booksToSave = filteredBooks.value.map(book => ({
                title: book.title,
                authors: book.authors?.map(a => a.name) || [],
                status: book.user_status
            }))
            localStorage.setItem('offlineBooks', JSON.stringify(booksToSave))
        }
    },
    { immediate: true }
)

defineOptions({ layout: AppLayout })
</script>

<template>
    <div>
        <!-- Header --------------------------------------------------------- -->
        <div class="flex flex-col gap-2.5 md:flex-row md:items-center md:gap-4">
            <div class="flex items-center justify-between gap-8">
                <PageTitle class="flex gap-2.5">
                    <template v-if="currentSearch">
                        Search results for "{{ currentSearch }}"
                    </template>
                    <template v-else>
                        Your Library
                        <Badge
                            class="mt-1 font-sans text-xs"
                            variant="secondary">
                            {{ filteredBooks.length }}
                        </Badge>
                    </template>
                </PageTitle>
                <BookViewTabs
                    v-model="view"
                    class="flex w-32 flex-1 shrink-0 book-view-tabs mobile-book-view-tabs max-w-32 md:hidden" />
            </div>

            <!-- View & Sort Controls ---------------------------------------- -->
            <div class="flex w-full flex-col items-center gap-2.5 md:ml-auto md:w-auto md:flex-row md:gap-2">
                <!-- View toggle -->
                <BookViewTabs
                    v-model="view"
                    class="hidden book-view-tabs desktop-book-view-tabs md:flex" />

                <!-- Sort dropdown & order -->
                <div class="flex w-full items-center justify-end gap-2.5 md:w-56 md:gap-2">
                    <Select v-model="sort">
                        <SelectTrigger class="w-full">
                            <span
                                v-if="sort"
                                class="text-muted-foreground">Sort:</span>
                            <SelectValue placeholder="Sort by..." />
                            <span class="sr-only">
                                Select sort option
                            </span>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem
                                    v-for="opt in sortOptions"
                                    :key="opt.value"
                                    :value="opt.value">
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="cursor-pointer bg-white text-secondary-foreground"
                                    size="icon"
                                    @click="order = order === 'asc' ? 'desc' : 'asc'"
                                >
                                    <span class="sr-only">
                                        Toggle sort order
                                    </span>
                                    <Icon :name="order === 'asc' ? 'ArrowUpWideNarrow' : 'ArrowDownWideNarrow'" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <span>
                                    {{ order === 'asc' ? 'Ascending' : 'Descending' }}
                                </span>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                    <Button
                        class="relative cursor-pointer bg-white text-secondary-foreground md:hidden"
                        variant="outline"
                        size="icon"
                        @pointerup="displayFilters = !displayFilters"
                    >
                        <div
                            v-if="hasFiltered"
                            class="absolute rounded-full ring-2 top-1.5 right-1.5 size-2 bg-primary ring-primary/20" />
                        <Icon
                            name="Filter"
                            class="w-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main layout ----------------------------------------------------- -->
        <div
            class="flex flex-col items-start gap-0 md:mt-4 md:flex-row md:gap-8 md:pt-0">
            <aside
                :class="displayFilters ? 'mt-0 mb-4 h-[calc-size(auto,size)] overflow-auto border-secondary' : 'mt-0 h-0 overflow-hidden border-background'"
                class="relative md:sticky top-4 md:mb-0 -mx-4 w-[calc(100%+calc(var(--spacing)*8))] flex-col gap-2 md:mt-0 border-y bg-muted px-4 transition-all duration-500 md:mx-0 md:flex md:h-auto md:w-72 md:overflow-visible md:border-0 md:bg-transparent md:px-0"
            >
                <!-- Search ---------------------------------------------------- -->
                <div class="mt-4 flex flex-col gap-2 md:mt-0">
                    <form
                        class="flex w-full flex-col gap-2"
                        @submit.prevent="submitForm">
                        <div class="grid w-full gap-2">
                            <!--                            <Label for="query">Search</Label>-->
                            <div class="relative flex w-full">
                                <Input
                                    id="query"
                                    ref="searchInput"
                                    v-model="search"
                                    class="pr-10"
                                    placeholder="Title or keywords..." />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-1">
                                    <Button
                                        type="submit"
                                        variant="link"
                                        class="cursor-pointer"
                                        size="icon">
                                        <span class="sr-only">
                                            Search
                                        </span>
                                        <Icon name="Search" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Author filter -------------------------------------------- -->
                        <div class="grid w-full gap-2">
                            <!--                            <Label for="query">Author</Label>-->
                            <Select v-model="author">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Filter by author" />
                                    <span class="sr-only">
                                        Select author filter
                                    </span>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem :value="null">
                                            All Authors
                                        </SelectItem>
                                        <template v-if="authors.length">
                                            <SelectItem
                                                v-for="a in authors"
                                                :key="a.slug"
                                                :value="a.slug">
                                                {{ a.name }}
                                            </SelectItem>
                                        </template>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Author filter -------------------------------------------- -->
                        <div class="grid w-full gap-2">
                            <!--                            <Label for="query">Tag</Label>-->
                            <Select
                                v-model="tag"
                                class="flex w-full">
                                <SelectTrigger class="w-full md:max-w-72">
                                    <SelectValue placeholder="Filter by tag" />
                                    <span class="sr-only">
                                        Select tag filter
                                    </span>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem :value="null">
                                            All Tags
                                        </SelectItem>
                                        <template v-if="tags.length">
                                            <SelectItem
                                                v-for="t in tags"
                                                :key="t.slug"
                                                :value="t.slug">
                                                {{ t.name }}
                                            </SelectItem>
                                        </template>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </form>
                </div>

                <!-- Status filter -------------------------------------------- -->
                <div class="my-4">
                    <p class="mb-2 font-serif">
                        Filter by status
                    </p>
                    <CheckboxList
                        v-model="status"
                        :options="possibleStatuses" />
                </div>

                <!-- Reset button -------------------------------------------- -->
                <Button
                    v-if="hasFiltered"
                    class="mb-4 w-full"
                    as-child
                    variant="outline">
                    <Link
                        :href="useRoute('user.books.index')"
                        preserve-scroll>
                        Reset
                    </Link>
                </Button>
            </aside>

            <!-- Books list -------------------------------------------------- -->
            <section class="mt-4 flex w-full flex-1 flex-col md:mt-0 md:w-auto">
                <div
                    v-if="!filteredBooks.length"
                    class="flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center text-sm border-primary/10 text-muted-foreground md:py-16">
                    <Icon
                        name="BookDashed"
                        class="size-8" />
                    <h3 class="font-serif text-2xl font-semibold">
                        No books found
                    </h3>
                    <div
                        v-if="totalBooks === 0"
                        class="flex flex-col">
                        <p>You haven't added any books yet.</p>
                        <div class="mx-auto flex">
                            <Button
                                class="mt-4"
                                as-child>
                                <Link :href="useRoute('books.search')">
                                    Search for books
                                </Link>
                            </Button>
                        </div>
                    </div>
                    <p v-else>
                        Try adjusting your search or filters.
                    </p>
                </div>

                <ShelfView
                    v-if="view === 'shelf'"
                    :books="filteredBooks" />

                <ul
                    v-else
                    :class="view === 'list' ? 'grid-cols-1 gap-8 md:gap-4' : 'grid-cols-2 sm:grid-cols-3 gap-2.5 md:gap-2 xl:gap-4 md:grid-cols-4 xl:grid-cols-5'"
                    class="grid">
                    <li
                        v-for="book in filteredBooks"
                        :key="book.identifier"
                        class="w-full">
                        <BookCardHorizontal
                            v-if="view === 'list'"
                            :book="book" />
                        <BookCard
                            v-if="view === 'grid'"
                            :book="book" />
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
