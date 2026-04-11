<script setup lang="ts">
import useEmitter from '@/composables/useEmitter'
import { usePage } from '@inertiajs/vue3'
import { useMediaQuery } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useForwardPropsEmits, type DialogRootEmits, type DialogRootProps } from 'reka-ui'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Drawer, DrawerClose, DrawerContent, DrawerDescription, DrawerFooter, DrawerHeader, DrawerTitle } from '@/components/ui/drawer'

const props = defineProps<DialogRootProps>()

// merge them together as one object‐style emit map
const emits = defineEmits<DialogRootEmits>()
const isOpen = ref(false)
const hasMounted = ref(false)

const forwarded = useForwardPropsEmits(props, emits)

const isDesktop = useMediaQuery('(min-width: 768px)')
const showDesktopDialog = computed(() => (hasMounted.value ? isDesktop.value : true))
const openJoinProDialog = () => {
    isOpen.value = true
}

onMounted(() => {
    hasMounted.value = true
    useEmitter.on('openJoinProDialog', openJoinProDialog)
})

onUnmounted(() => {
    useEmitter.off('openJoinProDialog', openJoinProDialog)
})
</script>

<template>
    <component
        :is="showDesktopDialog ? Dialog : Drawer"
        v-model:open="isOpen"
        v-bind="forwarded">
        <component
            :is="showDesktopDialog ? DialogContent : DrawerContent"
            class="sm:max-w-lg">
            <component :is="showDesktopDialog ? DialogHeader : DrawerHeader">
                <!--                <AppLogo class="mb-2 flex w-full" />-->
                <component
                    :is="showDesktopDialog ? DialogTitle : DrawerTitle"
                    class="font-serif text-xl md:text-3xl">
                    Pro
                </component>
                <component
                    :is="showDesktopDialog ? DialogDescription : DrawerDescription"
                    class="overflow-auto text-sm text-secondary-foreground md:text-base"
                >
                    <p>Join {{ usePage().props.app.name }} Pro to unlock premium features.</p>
                    <ul class="mt-2 list-disc pl-5">
                        <li>Unlimited books</li>
                        <li>Private notes</li>
                        <li>Custom book covers</li>
                        <li class="opacity-50">
                            Shareable book collections (coming soon)
                        </li>
                        <li>And more!</li>
                    </ul>
                </component>
            </component>
            <component
                :is="showDesktopDialog ? DialogFooter : DrawerFooter"
                :class="!showDesktopDialog ? 'mb-4 gap-4' : 'mt-6 gap-2'"
                class="flex sm:justify-end"
            >
                <component
                    :is="showDesktopDialog ? DialogClose : DrawerClose"
                    as-child>
                    <Button variant="outline">
                        Cancel
                    </Button>
                </component>
                <Button as-child>
                    <a :href="useRoute('checkout')"> Join </a>
                </Button>
            </component>
        </component>
    </component>
</template>
