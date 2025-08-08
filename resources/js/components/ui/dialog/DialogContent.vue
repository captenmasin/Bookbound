<script setup lang="ts">
import DialogOverlay from './DialogOverlay.vue'
import { cn } from '@/lib/utils'
import { X } from 'lucide-vue-next'
import { computed, type HTMLAttributes } from 'vue'
import {
    DialogClose,
    DialogContent,
    type DialogContentEmits,
    type DialogContentProps,
    DialogPortal,
    useForwardPropsEmits
} from 'reka-ui'

const props = defineProps<DialogContentProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<DialogContentEmits>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <DialogPortal>
        <DialogOverlay
        />
        <DialogContent
            v-bind="forwarded"
            :class="
                cn(
                    'fixed left-1/2 top-1/2 z-50 grid w-full max-w-lg bg-popover/60 dark:bg-white/20 p-1 shadow-xs backdrop-blur-[1px] -translate-x-1/2 -translate-y-1/2 gap-4 duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-50 data-[state=closed]:slide-out-to-bottom-[20%] data-[state=open]:slide-in-from-bottom-[10%] rounded-3xl',
                    props.class,
                )
            "
        >
            <div
                class="bg-popover rounded-(--dialog-inner-radius) p-6 border dark:border-white/30 border-black/20"
                style="--dialog-inner-radius: calc(21px);">
                <slot />

                <DialogClose
                    class="absolute right-6 top-6 rounded-sm opacity-70 transition-opacity hover:opacity-100 ring-offset-background focus:outline-hidden focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground"
                >
                    <X class="h-4 w-4" />
                    <span class="sr-only">Close</span>
                </DialogClose>
            </div>
        </DialogContent>
    </DialogPortal>
</template>
