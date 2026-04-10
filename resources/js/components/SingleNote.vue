<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import { cn } from '@/lib/utils'
import { Book } from '@/types/book'
import { Note } from '@/types/note'
import { router } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { MaybeRefOrGetter, PropType } from 'vue'
import { useRoute } from '@/composables/useRoute'
import { DateLike, useDateFormat } from '@vueuse/core'
import { useMarkdown } from '@/composables/useMarkdown'
import { UserBookStatus } from '@/enums/UserBookStatus'

const props = defineProps({
    book: {
        type: Object as PropType<Book>,
        required: true
    },
    note: {
        type: Object as PropType<Note>,
        required: true
    },
    class: {
        type: String,
        default: ''
    }
})

function formatDate (date: MaybeRefOrGetter<DateLike>) {
    return useDateFormat(date, 'Mo MMMM h:ma')
}

function deleteNote () {
    router.delete(
        useRoute('notes.destroy', { book: props.book, note: props.note }),
        {
            preserveScroll: true,
            only: ['notes', 'book']
        }
    )
}
</script>

<template>
    <div :class="cn('group', props.class)">
        <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-secondary-foreground">
                {{ formatDate(note.created_at) }}
            </div>
            <div class="flex items-center gap-4">
                <div
                    class="flex transition-all group-hover:opacity-100 md:opacity-0"
                >
                    <ConfirmationModal @confirmed="deleteNote()">
                        <template #title>
                            Are you sure you want to delete this note?
                        </template>
                        <template #description>
                            This action cannot be undone.
                        </template>
                        <template #trigger>
                            <Button
                                :id="`delete-note-` + note.id"
                                variant="link"
                                class="h-auto py-0 text-xs text-destructive"
                            >
                                Delete
                            </Button>
                        </template>
                    </ConfirmationModal>
                </div>
                <Badge
                    v-if="note.status"
                    variant="secondary"
                    class="text-xs ring ring-primary/10 -ring-offset-1">
                    {{ UserBookStatus[note.status] }}
                </Badge>
            </div>
        </div>
        <div
            class="mt-2 max-w-none prose prose-sm dark:prose-invert"
            v-html="useMarkdown(note.content)"
        />
    </div>
</template>
