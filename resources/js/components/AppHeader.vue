<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLogo from '@/components/AppLogo.vue'
import useEmitter from '@/composables/useEmitter'
import UserMenuSheet from '@/components/UserMenuSheet.vue'
import JoinProTrigger from '@/components/JoinProTrigger.vue'
import UserMenuDropdown from '@/components/UserMenuDropdown.vue'
import { useMediaQuery } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import { Link, usePage } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'
import type { BreadcrumbItem, NavItem } from '@/types'
import { UserPermission } from '@/enums/UserPermission'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useIsCurrentUrl } from '@/composables/useIsCurrentUrl'
import { Activity, BriefcaseBusiness, ChartLine, NotebookPen, Settings, Shield, Star, Wallet, Sparkles } from 'lucide-vue-next'
import { NavigationMenu, NavigationMenuItem, NavigationMenuList, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu'

interface Props {
    breadcrumbs?: BreadcrumbItem[];
    navItems?: NavItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    navItems: () => []
})

const { authed, authedUser, hasPermission } = useAuthedUser()

const activeItemStyles = computed(
    () => (url: string) => (useIsCurrentUrl(url) ? 'text-primary hover:text-primary dark:bg-neutral-800 dark:text-neutral-100' : '')
)

const isVisible = ref(true)
let lastScroll = window.scrollY

const handleScroll = () => {
    const currentScroll = window.scrollY

    if (currentScroll > lastScroll && currentScroll > 50) {
        isVisible.value = false // scrolling down
    } else if (currentScroll < lastScroll) {
        isVisible.value = true // scrolling up
    }

    lastScroll = currentScroll
}

const page = usePage()

const userMenuItems = ref([
    {
        title: 'Notes',
        url: useRoute('user.notes.index'),
        icon: NotebookPen
    },
    {
        title: 'Reviews',
        url: useRoute('user.reviews.index'),
        icon: Star
    },
    {
        title: 'Activities',
        url: useRoute('user.activities.index'),
        icon: Activity
    },
    {
        tag: 'a',
        title: 'Billing',
        url: useRoute('billing'),
        target: '_blank',
        icon: Wallet,
        if: authedUser.value?.subscription.subscribed
    },
    {
        tag: 'button',
        title: 'Upgrade to Pro',
        action: () => useEmitter.emit('openJoinProDialog'),
        icon: Sparkles,
        if: !authedUser.value?.subscription.subscribed
    },
    {
        title: 'Settings',
        url: useRoute('user.settings.profile.edit'),
        icon: Settings
    },
    {
        title: 'Admin',
        url: '/admin',
        icon: Shield,
        if: hasPermission(UserPermission.VIEW_ADMIN_PANEL),
        target: '_blank'
    },
    {
        title: 'Analytics',
        url: `https://dashboard.pirsch.io/?domain=${page.props.app.domain}&start=600&interval=live&scale=day`,
        icon: ChartLine,
        if: hasPermission(UserPermission.VIEW_ANALYTICS),
        target: '_blank'
    },
    {
        title: 'Horizon',
        url: '/horizon',
        icon: BriefcaseBusiness,
        if: hasPermission(UserPermission.VIEW_HORIZON_PANEL),
        target: '_blank'
    }
])

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})

const isDesktop = useMediaQuery('(min-width: 768px)')
</script>

<template>
    <div
        class="sticky safe-top safe-h-14 z-50 md:h-16 safe-pt border-b border-sidebar-border/80 transition-all duration-300 ease-in-out bg-background md:static md:translate-y-0"
        :class="{ '-NOT-translate-y-full': !isVisible }">
        <div class="mx-auto flex items-center h-full px-4 pwa:md:max-w-none md:max-w-7xl">
            <div
                v-if="authed"
                :class="$page.props.backUrl ? 'ml-0 opacity-100' : '-ml-8 opacity-0'"
                class="mr-2 transition-all duration-300 lg:hidden">
                <Link
                    tabindex="-1"
                    class="-ml-4 flex pl-2 text-primary"
                    :href="$page.props.backUrl ?? useRoute('dashboard')">
                    <Icon
                        name="ChevronLeft"
                        class="size-8 stroke-[1.5px]" />
                </Link>
            </div>

            <Link
                :href="useRoute('dashboard')"
                prefetch
                class="flex items-center gap-x-2">
                <span class="sr-only">
                    Go to Home
                </span>
                <AppLogo class="flex items-center" />
            </Link>

            <!-- Desktop Menu -->
            <div class="hidden h-full lg:flex lg:flex-1">
                <NavigationMenu
                    v-if="authed"
                    class="ml-10 flex h-full items-stretch">
                    <NavigationMenuList class="flex h-full items-stretch space-x-2">
                        <NavigationMenuItem
                            v-for="(item, index) in navItems"
                            :key="index"
                            :class="item.mobileOnly ? 'flex lg:hidden' : 'flex lg:flex-1'"
                            class="relative flex h-full items-center">
                            <Link
                                prefetch
                                :class="[navigationMenuTriggerStyle(), activeItemStyles(item.href), 'h-9 cursor-pointer px-3']"
                                :href="item.href"
                            >
                                <component
                                    :is="item.icon"
                                    v-if="item.icon"
                                    class="mr-2 h-4 w-4" />
                                {{ item.title }}
                            </Link>
                            <div
                                v-if="useIsCurrentUrl(item.href)"
                                class="absolute bottom-0 left-0 w-full translate-y-px bg-primary h-0.5 dark:bg-white"
                            />
                        </NavigationMenuItem>
                    </NavigationMenuList>
                </NavigationMenu>
            </div>

            <div
                v-if="authed && authedUser"
                class="ml-auto flex items-center space-x-2">
                <div>
                    <div
                        v-if="authedUser.subscription.subscribed"
                        class="rounded-full cursor-default select-none px-2 py-px font-serif font-semibold text-[10px] bg-primary text-primary-foreground">
                        PRO
                    </div>

                    <JoinProTrigger v-else>
                        <button class="mr-2 hidden cursor-pointer items-center gap-1 text-xs font-medium mt-1.5 text-primary xs:flex">
                            <Icon
                                name="Sparkles"
                                class="size-4" />
                            Upgrade to Pro
                        </button>
                    </JoinProTrigger>
                </div>

                <UserMenuDropdown
                    v-if="isDesktop"
                    :user="authedUser"
                    :items="userMenuItems" />

                <UserMenuSheet
                    v-else
                    :user="authedUser"
                    :items="userMenuItems"
                />
            </div>
            <div
                v-else
                class="ml-auto flex gap-2 md:gap-4">
                <Button
                    variant="secondary"
                    class="sm"
                    as-child>
                    <Link :href="useRoute('login')">
                        Login
                    </Link>
                </Button>
                <Button
                    class="sm"
                    as-child>
                    <Link :href="useRoute('register')">
                        Register
                    </Link>
                </Button>
            </div>
        </div>

        <!--        <div-->
        <!--            v-if="props.breadcrumbs.length > 1"-->
        <!--            class="flex w-full border-b border-sidebar-border/70">-->
        <!--            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">-->
        <!--                <Breadcrumbs :breadcrumbs="breadcrumbs" />-->
        <!--            </div>-->
        <!--        </div>-->
    </div>
</template>
