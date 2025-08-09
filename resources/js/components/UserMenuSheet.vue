<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import type { User } from '@/types/user'
import { LogOut } from 'lucide-vue-next'
import { nextTick, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { DropdownMenuItem } from '@/components/ui/dropdown-menu'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'

interface Props {
    user: User;
    items: {
        title: string;
        url: string;
        icon: any;
        if?: boolean,
        target?: string;
        tag?: string;
        action?: () => void;
    }[];
}

const { logout } = useAuthedUser()

const userMobileMenuOpen = ref(false)

defineProps<Props>()

// Add a fragment to the URL when the menu is open so that it can be closed when pressing the back button
watch(userMobileMenuOpen, (open) => {
    if (open) {
        const url = new URL(window.location.href)
        url.hash = 'open'
        window.history.pushState({}, '', url.toString())
    } else {
        const url = new URL(window.location.href)
        url.hash = ''
        window.history.pushState({}, '', url.toString())
    }
})

router.on('navigate', (event) => {
    nextTick(() => {
        userMobileMenuOpen.value = false
    })
})
</script>

<template>
    <div>
        <Sheet v-model:open="userMobileMenuOpen">
            <SheetTrigger
                :as-child="true">
                <Button
                    variant="ghost"
                    size="icon"
                    class="relative w-auto rounded-full p-1 size-10 focus-within:ring-primary focus-within:ring-2"
                >
                    <UserAvatar :user="user" />
                </Button>
            </SheetTrigger>
            <SheetContent
                class="max-h-screen overflow-auto"
                style="padding-top: env(safe-area-inset-top, 0px);">
                <SheetHeader>
                    <SheetTitle>
                        <div class="flex items-center gap-2 px-1 text-left text-sm">
                            <UserInfo
                                :user="user"
                                :show-email="true" />
                        </div>
                    </SheetTitle>
                </SheetHeader>
                <div class="px-6 pb-8 -mt-4 gap-2 flex flex-col h-full">
                    <template
                        v-for="item in items"
                        :key="item.title">
                        <div
                            v-if="item.if || !('if' in item)"
                            :as-child="true">
                            <component
                                :is="item.tag ? item.tag : (item.target === '_blank' ? 'a' : Link)"
                                class="flex items-center py-2 text-foreground text-lg font-medium gap-4 w-full"
                                :href="item.url"
                                :prefetch="item.target !== '_blank'"
                                @click="userMobileMenuOpen = false; item.action ? item.action() : null">
                                <component
                                    :is="item.icon"
                                    class="size-4.5" />
                                {{ item.title }}
                            </component>
                        </div>
                    </template>

                    <Separator class="mt-auto" />
                    <button
                        tabindex="-1"
                        class="flex items-center py-2 text-lg font-medium gap-4 w-full"
                        @click="logout">
                        <LogOut class="size-4.5" />
                        Log out
                    </button>
                </div>
            </SheetContent>
        </Sheet>
    </div>
</template>
