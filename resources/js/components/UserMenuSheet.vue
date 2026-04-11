<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import UserInfo from '@/components/UserInfo.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import AppearanceTabs from '@/components/AppearanceTabs.vue'
import type { User } from '@/types/user'
import { LogOut } from 'lucide-vue-next'
import { nextTick, ref, watch } from 'vue'
import { usePwa } from '@/composables/usePwa'
import { Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { Separator } from '@/components/ui/separator'
import { useAuthedUser } from '@/composables/useAuthedUser'
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger
} from '@/components/ui/sheet'

interface Props {
    user: User;
    items: {
        title: string;
        url?: string;
        icon?: any;
        if?: boolean;
        target?: string;
        tag?: string;
        action?: () => void;
    }[];
}

const { logout } = useAuthedUser()
const { isPwa, isAndroid, isIos, isMacos } = usePwa()

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
            <SheetTrigger :as-child="true">
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
                style="padding-bottom: env(safe-area-inset-bottom)"
            >
                <SheetHeader>
                    <SheetTitle style="padding-top: env(safe-area-inset-top)">
                        <div
                            class="flex items-center gap-2 px-1 text-left text-sm pt-4 pb-4"
                        >
                            <UserInfo
                                :user="user"
                                :show-email="true" />
                        </div>
                    </SheetTitle>
                </SheetHeader>
                <div class="-mt-4 flex flex-col gap-1 px-6 pb-12">
                    <template
                        v-for="item in items"
                        :key="item.title">
                        <div
                            v-if="item.if || !('if' in item)"
                            :as-child="true">
                            <component
                                :is="
                                    item.tag
                                        ? item.tag
                                        : item.target === '_blank'
                                            ? 'a'
                                            : Link
                                "
                                class="flex w-full items-center gap-4 py-2 text-lg font-medium text-foreground"
                                :href="item.url"
                                :prefetch="item.target !== '_blank'"
                                @click="
                                    userMobileMenuOpen = false;
                                    item.action ? item.action() : null;
                                "
                            >
                                <component
                                    :is="item.icon"
                                    class="size-4.5" />
                                {{ item.title }}
                            </component>
                        </div>
                    </template>

                    <div class="mt-auto">
                        <template v-if="!isPwa">
                            <Separator
                                class="mt-auto -mx-6 w-auto my-2 data-[orientation=horizontal]:w-auto"
                            />
                            <div class="pb-1">
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Theme
                                </p>
                                <AppearanceTabs :icon-only="true" />
                            </div>
                        </template>
                        <div
                            v-if="isPwa"
                            class="block text-sm font-medium text-foreground/20"
                        >
                            <span v-if="isAndroid"> Android </span>
                            <span v-if="isIos"> iOS </span>
                            <span v-if="isMacos"> macOS </span>
                        </div>
                        <Separator
                            class="mt-auto -mx-6 w-auto my-2 data-[orientation=horizontal]:w-auto"
                        />
                        <Link
                            class="flex w-full items-center gap-4 py-2 text-lg font-medium text-foreground"
                            :href="useRoute('contact')"
                        >
                            <Icon
                                name="mail"
                                class="size-4.5" />
                            Contact us
                        </Link>
                        <button
                            tabindex="-1"
                            class="flex w-full items-center gap-4 py-2 text-lg font-medium"
                            @click="logout"
                        >
                            <LogOut class="size-4.5" />
                            Log out
                        </button>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </div>
</template>
