<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import SingleNote from '@/components/SingleNote.vue'
import NoteForm from '@/components/books/NoteForm.vue'
import { Book } from '@/types/book'
import { Card } from '@/components/ui/card'
import { computed, PropType, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { useAuthedUser } from '@/composables/useAuthedUser'

const props = defineProps({
    book: {
        type: Object as PropType<Book>,
        required: true
    }
})

const showAllNotes = ref(false)
const displayLimit = ref(3)

const { authed } = useAuthedUser()

const displayNotes = computed(() => {
    if (!props.book.user_notes) {
        return []
    }
    if (showAllNotes.value) {
        return props.book.user_notes
    }
    return props.book.user_notes.slice(0, displayLimit.value)
})
</script>

<template>
    <div>
        <div v-if="!authed">
            <div
                class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center text-sm text-muted-foreground border-primary/10"
            >
                <Icon
                    name="NotebookPen"
                    class="size-8" />
                <h3 class="font-serif text-2xl font-semibold">
                    Add notes
                </h3>
                <p>Log in to add private notes to about this book.</p>
            </div>
        </div>

        <div
            v-else
            class="flex flex-col">
            <NoteForm :book="book" />
            <div class="mt-4">
                <div class="flex flex-col divide-y divide-muted">
                    <Card
                        v-for="note in displayNotes"
                        :key="note.id">
                        <SingleNote
                            :book="book"
                            :note="note" />
                    </Card>
                </div>

                <div
                    v-if="
                        book.user_notes &&
                            book.user_notes?.length > displayLimit
                    "
                    class="my-3 flex items-center md:my-6"
                >
                    <Separator class="flex flex-1 bg-muted" />
                    <div class="flex px-4">
                        <Button
                            variant="link"
                            size="sm"
                            class="text-secondary-foreground"
                            @click="showAllNotes = !showAllNotes"
                        >
                            {{ showAllNotes ? "Show less" : "Show all" }}
                        </Button>
                    </div>
                    <Separator class="flex flex-1 bg-muted" />
                </div>
            </div>
        </div>
    </div>
</template>
