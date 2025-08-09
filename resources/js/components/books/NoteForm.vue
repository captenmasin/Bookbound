<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import InputError from '@/components/InputError.vue'
import JoinProDialog from '@/components/JoinProDialog.vue'
import { Book } from '@/types/book'
import { PropType, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { Textarea } from '@/components/ui/textarea'
import { useAuthedUser } from '@/composables/useAuthedUser'

const props = defineProps({
    book: {
        type: Object as PropType<Book>,
        required: true
    }
})

const noteForm = useForm({
    content: ''
})

const { authedUser } = useAuthedUser()

const submit = () => {
    noteForm.post(useRoute('notes.store', props.book), {
        only: ['book'],
        preserveScroll: true,
        onSuccess: () => {
            noteForm.reset()
        }
    })
}

const noteInput = ref<HTMLInputElement | null>(null)
</script>

<template>
    <div>
        <form
            v-if="authedUser?.subscription.allow_private_notes"
            @submit.prevent="submit">
            <Textarea
                id="noteInput"
                ref="noteInput"
                v-model="noteForm.content"
                class="min-h-24 md:min-h-18"
                placeholder="Add a private note about this book..." />
            <InputError :message="noteForm.errors.content" />
            <div class="flex mt-2 items-center justify-between">
                <div>
                    <a
                        href="https://www.markdownguide.org/"
                        class="text-secondary-foreground text-sm hover:underline"
                        target="_blank"
                        rel="nofollow ">
                        Markdown syntax is supported.
                    </a>
                </div>
                <div class="flex items-center gap-4 ">
                    <Button
                        v-if="noteForm.isDirty"
                        variant="secondary"
                        type="button"
                        @click="noteForm.reset()">
                        Cancel
                    </Button>
                    <Button
                        variant="default"
                        type="submit">
                        Save
                    </Button>
                </div>
            </div>
        </form>
        <div
            v-else
            class="flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 text-center text-sm text-muted-foreground md:py-8">
            <div class="text-secondary-foreground text-sm">
                <p>
                    Pro users can add private notes to their books.
                </p>
                <p>
                    Upgrade to unlock this feature.
                </p>
            </div>
            <JoinProDialog>
                <Button size="sm">
                    Upgrade now
                </Button>
            </JoinProDialog>
        </div>
    </div>
</template>
