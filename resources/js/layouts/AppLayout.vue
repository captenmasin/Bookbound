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
            toast.success(flash.success)
        }
        if (flash?.error) {
            toast.error(flash.error)
        }
        if (flash?.warning) {
            toast.warning(flash.warning)
        }
        if (flash?.info) {
            nextTick(() => {
                toast.info(flash.info)
            })
        }
    },
    { immediate: true }
)

const showBetaDialog = ref(false)

function handleBetaDialog (value: boolean) {
    if (value === false) {
        document.cookie = 'beta_dialog_closed=true; path=/; max-age=31536000'
    }
}

const { authed } = useAuthedUser()

onMounted(() => {
    if (!authed.value) {
        return
    }

    const betaDialogClosed = document.cookie.split('; ').find(row => row.startsWith('beta_dialog_closed='))
    if (!betaDialogClosed) {
        showBetaDialog.value = true
    }
})
</script>

<template>
    <AppHeaderLayout>
        <MetaHead />

        <Dialog
            v-model:open="showBetaDialog"
            @update:open="handleBetaDialog">
            <DialogContent>
                <DialogHeader class="space-y-3">
                    <DialogTitle> Beta </DialogTitle>
                    <DialogDescription>
                        {{ page.props.app.name }} is currently in beta. Some features are expected to change, and some features may not work as
                        expected. We appreciate your patience and understanding during this period.
                    </DialogDescription>
                    <DialogDescription>
                        Please report any bugs or issues you encounter to our support team at
                        <a
                            class="text-black hover:underline dark:text-white"
                            href="mailto:support@spacemancodes.com">support@spacemancodes.com</a>
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2 mt-4">
                    <DialogClose as-child>
                        <Button>
                            Okay
                        </Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>

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
