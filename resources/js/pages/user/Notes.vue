<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import SingleNote from '@/components/SingleNote.vue'
import CustomPagination from '@/components/CustomPagination.vue'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import { PropType } from 'vue'
import { Note } from '@/types/note'
import { Paginated } from '@/types/pagination'

defineOptions({ layout: AppLayout })

const props = defineProps({
    notes: {
        type: Object as PropType<Paginated<Note>>,
        default: () => ({ data: [], links: {}, meta: {} })
    }
})
</script>

<template>
    <div>
        <PageTitle class="mb-4">
            Your Notes
        </PageTitle>

        <div
            v-if="notes.meta.total === 0 || notes.data.length === 0"
            class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center text-sm border-primary/10 text-muted-foreground md:py-12"
        >
            <Icon
                name="NotebookPen"
                class="size-8" />
            <h3 class="font-serif text-2xl font-semibold">
                Nothing to see here
            </h3>
            <p v-if="notes.data.length === 0">
                There's no notes on this page
            </p>
            <p v-else>
                You haven't added any notes to books yet
            </p>
        </div>

        <ul
            v-else
            class="rounded-xl bg-white shadow divide-y divide-muted dark:divide-zinc-950 dark:bg-zinc-900">
            <li
                v-for="note in props.notes.data"
                :key="note.id"
                class="flex flex-col items-start gap-4 p-4 group md:flex-row md:p-6"
            >
                <BookCardHorizontal
                    :book="note.book"
                    :include-actions="false"
                    class="md:w-1/3"
                />
                <SingleNote
                    :book="note.book"
                    :note="note"
                    class="flex-1 py-0"
                />
            </li>
        </ul>
        <CustomPagination
            v-if="notes.meta.total > notes.meta.per_page"
            class="my-4"
            :meta="notes.meta" />
    </div>
</template>
