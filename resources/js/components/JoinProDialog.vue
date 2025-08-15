<script setup lang="ts">
import useEmitter from '@/composables/useEmitter'
import { onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useMediaQuery } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { useForwardPropsEmits, type DialogRootEmits, type DialogRootProps } from 'reka-ui'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Drawer, DrawerClose, DrawerContent, DrawerDescription, DrawerFooter, DrawerHeader, DrawerTitle } from '@/components/ui/drawer'

const props = defineProps<DialogRootProps>()

// merge them together as one object‐style emit map
const emits = defineEmits<DialogRootEmits>()
const isOpen = ref(false)

const forwarded = useForwardPropsEmits(props, emits)

const isDesktop = useMediaQuery('(min-width: 768px)')

onMounted(() => {
    useEmitter.on('openJoinProDialog', () => {
        isOpen.value = true
    })
})
</script>

<template>
    <component
        :is="isDesktop ? Dialog : Drawer"
        v-model:open="isOpen"
        v-bind="forwarded">
        <component
            :is="isDesktop ? DialogContent : DrawerContent"
            class="sm:max-w-lg">
            <component
                :is="isDesktop ? DialogHeader : DrawerHeader">
                <!--                <AppLogo class="mb-2 flex w-full" />-->
                <component
                    :is="isDesktop ? DialogTitle : DrawerTitle"
                    class="font-serif text-xl md:text-3xl">
                    Pro
                </component>
                <component
                    :is="isDesktop ? DialogDescription : DrawerDescription"
                    class="overflow-auto text-sm text-secondary-foreground md:text-base">
                    <p>
                        Join {{ usePage().props.app.name }} Pro to unlock premium features.
                    </p>
                    <ul class="mt-2 list-disc pl-5">
                        <li>Unlimited books</li>
                        <li>Private notes</li>
                        <li>Custom book covers</li>
                        <li class="opacity-50">
                            Custom tags (coming soon)
                        </li>
                        <li class="opacity-50">
                            Shareable book collections (coming soon)
                        </li>
                        <li>And more!</li>
                    </ul>
                </component>
            </component>
            <component
                :is="isDesktop ? DialogFooter : DrawerFooter"
                :class="!isDesktop ? 'mb-4 gap-4' : 'gap-2 mt-6'"
                class="flex sm:justify-end">
                <component
                    :is="isDesktop ? DialogClose : DrawerClose"
                    as-child>
                    <Button
                        variant="outline">
                        Cancel
                    </Button>
                </component>
                <Button as-child>
                    <a :href="useRoute('checkout')">
                        Join
                    </a>
                </Button>
            </component>
        </component>
    </component>
</template>
