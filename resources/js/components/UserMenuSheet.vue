<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import { nextTick, ref } from 'vue'
import type { User } from '@/types/user'
import { LogOut } from 'lucide-vue-next'
import { Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'

interface Props {
    user: User;
    items: {
        title: string;
        url: string;
        icon: any;
        if?: boolean,
        target?: string;
    }[];
}

const { logout } = useAuthedUser()

const userMobileMenuOpen = ref(false)

defineProps<Props>()

router.on('navigate', (event) => {
    nextTick(() => {
        userMobileMenuOpen.value = false
    })
})
</script>

<template>
    <div>
        <Sheet
            v-model:open="userMobileMenuOpen"
        >
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
            <SheetContent class="max-h-screen overflow-auto">
                <SheetHeader>
                    <SheetTitle>
                        <div class="flex items-center gap-2 px-1 text-left text-sm py-1.5">
                            <UserInfo
                                :user="user"
                                :show-email="true" />
                        </div>
                    </SheetTitle>
                </SheetHeader>
                <div class="px-6 pb-4 -mt-4 gap-2 flex flex-col h-full">
                    <template
                        v-for="item in items"
                        :key="item.title">
                        <div
                            v-if="!item.if || item.if"
                            :as-child="true">
                            <component
                                :is="item.target === '_blank' ? 'a' : Link"
                                class="flex items-center py-2 text-foreground text-lg font-medium gap-4 w-full"
                                :href="item.url"
                                :prefetch="item.target !== '_blank'">
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
