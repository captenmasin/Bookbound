<script setup lang="ts">
import 'vue-sonner/style.css'
import MetaHead from '@/components/MetaHead.vue'
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'
import { watch } from 'vue'
import { toast } from 'vue-sonner'
import { Link, usePage } from '@inertiajs/vue3'
import { Toaster } from '@/components/ui/sonner'
import type { BreadcrumbItemType } from '@/types'
import { useRoute } from '@/composables/useRoute'

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
            toast.success(flash.success)
        }
        if (flash?.error) {
            toast.error(flash.error)
        }
        if (flash?.warning) {
            toast.warning(flash.warning)
        }
        if (flash?.info) {
            toast.info(flash.info)
        }
    },
    { immediate: true }
)
</script>

<template>
    <AppHeaderLayout>
        <MetaHead />
        <slot />
        <Toaster
            :duration="2000"
            class="pointer-events-auto" />
        <footer class="mt-auto py-4 hidden lg:flex text-xs text-muted-foreground justify-between border-t border-secondary">
            <p>
                &copy; {{ new Date().getFullYear() }} SpacemanCodes LTD. All rights reserved.
            </p>
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
