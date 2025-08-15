<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import { Link } from '@inertiajs/vue3'
import type { User } from '@/types/user'
import { LogOut } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'

interface Props {
    user: User;
    items: {
        tag?: string;
        title: string;
        url?: string;
        icon: any;
        if?: boolean,
        target?: string;
        action?: () => void;
    }[];
}

const { logout } = useAuthedUser()

defineProps<Props>()
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger
            :as-child="true">
            <Button
                variant="ghost"
                size="icon"
                class="relative w-auto rounded-full p-1 size-10 focus-within:ring-primary focus-within:ring-2"
            >
                <UserAvatar :user="user" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            align="end"
            class="w-56">
            <DropdownMenuLabel class="p-0 font-normal">
                <div class="flex items-center gap-2 px-1 text-left text-sm py-1.5">
                    <UserInfo
                        :user="user"
                        :show-email="true" />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <template
                    v-for="item in items"
                    :key="item.title">
                    <DropdownMenuItem
                        v-if="item.if || !('if' in item)"
                        :as-child="true">
                        <component
                            :is="item.tag ? item.tag : (item.target === '_blank' ? 'a' : Link)"
                            class="block w-full"
                            :href="item.url"
                            :target="item.target"
                            :prefetch="item.target !== '_blank'"
                            @click="item.action ? item.action() : null">
                            <component
                                :is="item.icon"
                                class="mr-2 h-4 w-4" />
                            {{ item.title }}
                        </component>
                    </DropdownMenuItem>
                </template>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem :as-child="true">
                <button
                    class="block w-full"
                    @click="logout">
                    <LogOut class="mr-2 h-4 w-4" />
                    Log out
                </button>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </dropdownmenu>
</template>
