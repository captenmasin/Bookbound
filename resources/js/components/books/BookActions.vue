<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import { PropType } from 'vue'
import { router } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'
import { Book, BookApiResult } from '@/types/book'
import { UserBookStatus } from '@/enums/UserBookStatus'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { useUserBookStatus } from '@/composables/useUserBookStatus'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

defineProps({
    book: {
        type: Object as PropType<Book | BookApiResult>,
        required: true
    }
})

const emit = defineEmits(['updated', 'added', 'removed'])

const { possibleStatuses, updateStatus, selectedStatuses, addedBookIdentifiers, addingBooks, addBookToUser, removeBookFromUser } =
    useUserBookStatus()

const { authed } = useAuthedUser()

function selectNewStatus (book: BookApiResult | Book, status: UserBookStatus | 'REMOVE') {
    if (!authed.value) {
        router.get(
            useRoute('login', {
                redirect: useRoute('books.show', book)
            })
        )
        return
    }

    if (status === 'REMOVE') {
        removeBookFromUser(book, () => {
            emit('removed', book)
        })
        return
    }

    if (book?.identifier) {
        if (addedBookIdentifiers.value.has(book.identifier)) {
            updateStatus(
                book,
                status,
                () => {
                    emit('updated', book, status)
                },
                () => {
                    selectedStatuses.value[book.identifier] = null
                }
            )
        } else {
            addBookToUser(
                book.identifier,
                status,
                () => {
                    emit('added', book, status)
                },
                () => {
                    selectedStatuses.value[book.identifier] = null
                }
            )
        }
    }
}
</script>

<template>
    <div class="relative flex w-full flex-1 items-center justify-end gap-2">
        <div
            v-if="addingBooks.includes(book.identifier)"
            class="absolute top-1/2 left-2 aspect-square w-6 shrink-0 -translate-y-1/2">
            <div class="flex animate-spin items-center justify-center rounded-full size-full bg-muted text-muted-foreground">
                <Icon
                    name="LoaderCircle"
                    class="size-4" />
            </div>
        </div>

        <div class="w-full md:w-full">
            <Select
                v-model="selectedStatuses[book.identifier]"
                @update:model-value="(value) => selectNewStatus(book, value as UserBookStatus)">
                <SelectTrigger
                    class="w-full"
                    :class="addingBooks.includes(book.identifier) ? 'pl-9' : ''">
                    <SelectValue placeholder="Add to library" />
                    <span class="sr-only"> Select book status </span>
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem
                            v-for="status in possibleStatuses"
                            :key="status.value"
                            :value="status.value">
                            {{ status.label }}
                        </SelectItem>

                        <SelectItem
                            v-if="addedBookIdentifiers.has(book.identifier)"
                            icon="Trash"
                            class="flex w-full justify-between text-destructive focus:bg-destructive/15 focus:text-destructive"
                            value="REMOVE"
                        >
                            Remove
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>
