<script setup lang="ts">
import 'vue-sonner/style.css'
import MetaHead from '@/components/MetaHead.vue'
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'
import { toast } from 'vue-sonner'
import { Link, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Toaster } from '@/components/ui/sonner'
import type { BreadcrumbItemType } from '@/types'
import { useRoute } from '@/composables/useRoute'
import { nextTick, onMounted, ref, watch } from 'vue'
import { useAuthedUser } from '@/composables/useAuthedUser'
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/components/ui/dialog'

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => []
})

const page = usePage()

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            nextTick(() => {
                toast.success(flash.success)
            })
        }
        if (flash?.error) {
            nextTick(() => {
                toast.error(flash.error)
            })
        }
        if (flash?.warning) {
            nextTick(() => {
                toast.warning(flash.warning)
            })
        }
        if (flash?.info) {
            nextTick(() => {
                toast.info(flash.info)
            })
        }
    },
    { immediate: true }
)

const { authed } = useAuthedUser()
</script>

<template>
    <AppHeaderLayout>
        <MetaHead />
        <slot />
        <Toaster
            :duration="2000"
            class="pointer-events-auto" />
        <footer class="mt-auto hidden justify-between border-t border-secondary py-4 text-xs text-muted-foreground lg:flex">
            <p>&copy; {{ new Date().getFullYear() }} SpacemanCodes LTD. All rights reserved.</p>
            <div>
                <Link
                    :href="useRoute('privacy-policy')"
                    class="hover:text-primary hover:underline">
                    Privacy Policy
                </Link>
            </div>
        </footer>
    </AppHeaderLayout>
</template>
