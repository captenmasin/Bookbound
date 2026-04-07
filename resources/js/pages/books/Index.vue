<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import BookCard from '@/components/books/BookCard.vue'
import CheckboxList from '@/components/CheckboxList.vue'
import ShelfView from '@/components/books/ShelfView.vue'
import BookViewTabs from '@/components/books/BookViewTabs.vue'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import type { Book } from '@/types/book'
import type { Author } from '@/types/author'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { useUrlSearchParams } from '@vueuse/core'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useUserSettings } from '@/composables/useUserSettings'
import { useUserBookStatus } from '@/composables/useUserBookStatus'
import { computed, nextTick, ref, watch, type PropType } from 'vue'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select'

type CategoryOption = {
    name: string;
    slug: string;
};

/* --------------------------------------------------------------------------
 * Props & Refs
 * -------------------------------------------------------------------------- */
const props = defineProps({
    books: Array as PropType<Book[]>,
    totalBooks: { type: Number, default: 0 },
    selectedStatuses: { type: Array as PropType<string[]>, default: () => [] },
    selectedAuthor: { type: String as PropType<string | null>, default: null },
    selectedCategory: {
        type: String as PropType<string | null>,
        default: null
    },
    selectedSort: { type: String, default: null },
    selectedOrder: { type: String, default: 'desc' },
    authors: { type: Array as PropType<Author[]>, default: () => [] },
    categories: {
        type: Array as PropType<CategoryOption[]>,
        default: () => []
    }
})

const params = useUrlSearchParams('history')
const { possibleStatuses } = useUserBookStatus()

/** Search --------------------------------------------------------------- */
const searchInput = ref<HTMLInputElement | null>(null)
const search = ref(String(params.search || ''))
const currentSearch = ref(String(params.search || ''))
const displayFilters = ref(
    search.value !== '' ||
        props.selectedStatuses.length > 0 ||
        props.selectedAuthor !== null
)

/** Filters -------------------------------------------------------------- */
const status = ref<string[]>(props.selectedStatuses)
const author = ref<string | null>(props.selectedAuthor)
const category = ref<string | null>(props.selectedCategory)
const sort = ref(props.selectedSort)
const order = ref<'asc' | 'desc'>(props.selectedOrder as 'asc' | 'desc')

/** View preferences ----------------------------------------------------- */
const page = usePage()
const view = ref<'list' | 'grid' | 'shelf'>(
    (page.props.auth.user?.settings?.library.view as
        | 'list'
        | 'grid'
        | 'shelf'
        | undefined) ?? 'grid'
)
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
    [author, category, status, sort, order],
    () => {
        Object.assign(params, {
            author: author.value,
            category: category.value,
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
    () =>
        !!currentSearch.value ||
        !!author.value ||
        !!category.value ||
        sort.value !== null ||
        status.value.length > 0 ||
        order.value !== 'desc'
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
            category: category.value,
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
        if (typeof localStorage === 'undefined') {
            return
        }

        // Only save to localStorage if no filters are applied
        if (!hasFiltered.value) {
            const booksToSave = filteredBooks.value.map((book) => ({
                title: book.title,
                authors: book.authors?.map((a) => a.name) || [],
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
        <!-- Main layout ----------------------------------------------------- -->
        <div
            class="flex flex-col items-start gap-0 md:mt-4 md:flex-row md:gap-8 md:pt-0"
        >
            <aside
                :class="
                    displayFilters
                        ? 'mt-0 mb-4 h-[calc-size(auto,size)] overflow-auto border-secondary'
                        : 'mt-0 h-0 overflow-hidden border-background'
                "
                class="relative top-4 -mx-4 w-[calc(100%+calc(var(--spacing)*8))] flex-col gap-2 border-y bg-muted px-4 transition-all duration-500 md:sticky md:mx-0 md:mt-0 md:mb-0 md:flex md:h-auto md:w-72 md:overflow-visible md:border-0 md:bg-transparent md:px-0"
            >
                <!-- Search ---------------------------------------------------- -->
                <div class="mt-4 flex flex-col gap-2 md:mt-0">
                    <form
                        class="flex w-full flex-col gap-2"
                        @submit.prevent="submitForm"
                    >
                        <div class="grid w-full gap-2">
                            <Label for="query">Search</Label>
                            <div class="relative flex w-full">
                                <Input
                                    id="query"
                                    ref="searchInput"
                                    v-model="search"
                                    class="h-10 border-card-outline bg-card pr-10 shadow-none"
                                    placeholder="Title or keywords..."
                                />
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center pr-1"
                                >
                                    <Button
                                        type="submit"
                                        variant="link"
                                        class="cursor-pointer"
                                        size="icon"
                                    >
                                        <span class="sr-only"> Search </span>
                                        <Icon name="Search" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Author filter -------------------------------------------- -->
                        <div class="mt-2 grid w-full gap-2">
                            <Label> Filter by author </Label>
                            <Select v-model="author">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="All authors" />
                                    <span class="sr-only">
                                        Select author filter
                                    </span>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem :value="null">
                                            All authors
                                        </SelectItem>
                                        <template v-if="authors.length">
                                            <SelectItem
                                                v-for="a in authors"
                                                :key="a.slug"
                                                :value="a.slug"
                                            >
                                                {{ a.name }}
                                            </SelectItem>
                                        </template>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Author filter -------------------------------------------- -->
                        <div class="grid w-full gap-2 mt-2">
                            <p
                                class="flex justify-between font-sans text-xs font-semibold tracking-wide text-secondary-foreground uppercase"
                            >
                                Filter by category
                            </p>
                            <Select
                                v-model="category"
                                class="flex w-full">
                                <SelectTrigger class="w-full md:max-w-72">
                                    <SelectValue placeholder="All categories" />
                                    <span class="sr-only">
                                        Select category
                                    </span>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem :value="null">
                                            All categories
                                        </SelectItem>
                                        <template v-if="categories.length">
                                            <SelectItem
                                                v-for="categoryOption in categories"
                                                :key="categoryOption.slug"
                                                :value="categoryOption.slug"
                                            >
                                                {{ categoryOption.name }}
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
                    <p
                        class="mb-2 flex justify-between font-sans text-xs font-semibold tracking-wide text-secondary-foreground uppercase"
                    >
                        Filter by status
                    </p>
                    <CheckboxList
                        v-model="status"
                        :options="possibleStatuses"
                    />
                </div>

                <!-- Reset button -------------------------------------------- -->
                <!--                <Button-->
                <!--                    v-if="hasFiltered"-->
                <!--                    class="mb-4 w-full"-->
                <!--                    as-child-->
                <!--                    variant="outline">-->
                <!--                    <Link-->
                <!--                        :href="useRoute('user.books.index')"-->
                <!--                        preserve-scroll>-->
                <!--                        Reset-->
                <!--                    </Link>-->
                <!--                </Button>-->
            </aside>

            <!-- Books list -------------------------------------------------- -->
            <section class="mt-4 flex w-full flex-1 flex-col md:mt-0 md:w-auto">
                <div class="mb-4 flex flex-col gap-2">
                    <PageTitle
                        class="flex flex-col md:flex-row items-start w-full md:items-center md:justify-between gap-4 md:gap-2.5 text-primary"
                    >
                        <div>
                            <template v-if="currentSearch">
                                Search results for "{{ currentSearch }}"
                            </template>
                            <template v-else>
                                Your Library
                            </template>
                            <p
                                class="font-sans text-xs text-secondary-foreground"
                            >
                                {{ filteredBooks.length }} books
                                {{
                                    currentSearch || hasFiltered
                                        ? "found"
                                        : "available"
                                }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <BookViewTabs
                                v-model="view"
                                class="book-view-tabs mobile-book-view-tabs min-w-28 flex md:max-w-68 flex-1 shrink-0 font-sans"
                            />

                            <div
                                class="w-full md:w-auto bg-red-200 md:min-w-40 gap-2 font-sans font-normal grid"
                            >
                                <Select v-model="sort">
                                    <SelectTrigger
                                        class="w-full data-[size=default]:h-9"
                                    >
                                        <span
                                            v-if="sort"
                                            class="text-muted-foreground"
                                        >Sort:</span
                                        >
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
                                                :value="opt.value"
                                            >
                                                {{ opt.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </PageTitle>
                </div>

                <div
                    v-if="!filteredBooks.length"
                    class="flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 text-center text-sm text-muted-foreground md:py-16"
                >
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
                        <div class="mx-auto flex flex-col gap-2 md:flex-row">
                            <Button
                                class="mt-4"
                                as-child>
                                <Link :href="useRoute('books.search')">
                                    Search for books
                                </Link>
                            </Button>
                            <Button
                                class="mt-4"
                                variant="outline"
                                as-child>
                                <Link
                                    :href="
                                        useRoute('user.books.imports.create')
                                    "
                                >
                                    Import Goodreads
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
                    :class="
                        view === 'list'
                            ? 'grid-cols-1 gap-8 md:gap-4'
                            : 'grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 md:gap-2 xl:grid-cols-5 xl:gap-4'
                    "
                    class="grid"
                >
                    <li
                        v-for="book in filteredBooks"
                        :key="book.identifier"
                        class="w-full"
                    >
                        <BookCardHorizontal
                            v-if="view === 'list'"
                            :book="book"
                        />
                        <BookCard
                            v-if="view === 'grid'"
                            :book="book" />
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
