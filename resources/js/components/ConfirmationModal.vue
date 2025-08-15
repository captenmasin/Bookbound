<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { useForwardPropsEmits, type DialogRootEmits, type DialogRootProps } from 'reka-ui'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'

// your extra events in property/signature form:
type ExtraEmits = {
    /** user clicked “Confirm” */
    confirmed(): void
    /** user clicked “Cancel” */
    'not-confirmed'(): void
}

const props = defineProps<DialogRootProps>()

// merge them together as one object‐style emit map
const emits = defineEmits<DialogRootEmits & ExtraEmits>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
    <Dialog v-bind="forwarded">
        <DialogTrigger as-child>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>
                    <slot name="title">
                        Are you sure?
                    </slot>
                </DialogTitle>
                <DialogDescription>
                    <slot name="description">
                        This action cannot be undone.
                    </slot>
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-4 flex gap-2 sm:justify-end">
                <DialogClose as-child>
                    <Button
                        variant="outline"
                        @click="emits('not-confirmed')">
                        Cancel
                    </Button>
                </DialogClose>
                <DialogClose as-child>
                    <Button @click="emits('confirmed')">
                        Confirm
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
