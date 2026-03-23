<script setup lang="ts">
import AppShell from '@/components/AppShell.vue'
import AppHeader from '@/components/AppHeader.vue'
import AppContent from '@/components/AppContent.vue'
import JoinProDialog from '@/components/JoinProDialog.vue'
import { logicAnd } from '@vueuse/math'
import { Label } from '@/components/ui/label'
import { usePwa } from '@/composables/usePwa'
import { useRoute } from '@/composables/useRoute'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, nextTick, onMounted, ref } from 'vue'
import type { BreadcrumbItemType, NavItem } from '@/types'
import { useIsCurrentUrl } from '@/composables/useIsCurrentUrl'
import { useActiveElement, useMagicKeys, whenever } from '@vueuse/core'
import { Home, LibraryBig, ScanBarcode, SearchIcon } from 'lucide-vue-next'

const page = usePage()
const breadcrumbs = ref(page.props.breadcrumbs as BreadcrumbItemType[] | undefined)
const { isNative } = usePwa()

const mainNavItems = ref<NavItem[]>([
    {
        title: 'Dashboard',
        href: useRoute('dashboard'),
        icon: Home,
        isActive: false
    },
    {
        title: 'Library',
        href: useRoute('user.books.index'),
        icon: LibraryBig,
        isActive: false
    },
    {
        title: 'Find Book',
        href: useRoute('books.search'),
        icon: SearchIcon,
        isActive: false
    },
    {
        title: 'Scan Book',
        href: useRoute('books.scan'),
        icon: ScanBarcode,
        isActive: false,
        mobileOnly: true
    }
])

const { h, b, s } = useMagicKeys()

const activeElement = useActiveElement()
const notUsingInput = computed(() => activeElement.value?.tagName !== 'INPUT' && activeElement.value?.tagName !== 'TEXTAREA')

whenever(logicAnd(h, notUsingInput), () => router.get(useRoute('dashboard')))
whenever(logicAnd(b, notUsingInput), () => router.get(useRoute('user.books.index')))
whenever(logicAnd(s, notUsingInput), () => router.get(useRoute('books.search')))

const activeItemStyles = computed(() => (item: NavItem) => (item.isActive ? 'text-primary' : ''))

function setActiveItems () {
    mainNavItems.value.forEach((item) => {
        item.isActive = useIsCurrentUrl(item.href)
    })
}

function handleClick (item: NavItem) {
    mainNavItems.value.forEach((navItem) => {
        navItem.isActive = navItem.href === item.href
    })
}

onMounted(() => nextTick(() => setActiveItems()))

router.on('navigate', () => {
    // const newBreadcrumbs = event.detail.page.props.breadcrumbs as BreadcrumbItemType[] | undefined
    // if (newBreadcrumbs) {
    //     breadcrumbs.value = newBreadcrumbs
    // }

    nextTick(() => {
        setActiveItems()
    })
})
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader
            v-if="!isNative"
            :nav-items="mainNavItems"
            :breadcrumbs="breadcrumbs" />
        <AppContent class="mt-4">
            <slot />
        </AppContent>
        <div
            v-if="!isNative"
            style="padding-bottom: env(safe-area-inset-bottom)"
            class="border-background-foreground sticky right-0 bottom-0 left-0 z-50 border-t bg-background/75 backdrop-blur-sm lg:hidden"
        >
            <ul class="mx-auto flex w-full max-w-xl items-center pb-2">
                <li
                    v-for="item in mainNavItems"
                    :key="item.title"
                    class="flex flex-1">
                    <Link
                        :href="item.href"
                        prefetch
                        :class="[activeItemStyles(item)]"
                        class="relative flex w-full flex-col items-center justify-center gap-1 py-2 text-sm text-foreground hover:text-primary sm:flex-row sm:gap-2"
                        @click="handleClick(item)"
                    >
                        <div
                            :class="[item.isActive ? 'bg-primary/10' : 'bg-transparent']"
                            class="rounded-full px-5 py-1.5 transition-all sm:px-4 sm:py-1"
                        >
                            <component
                                :is="item.icon"
                                class="size-5" />
                        </div>
                        <Label class="text-xs font-medium sm:text-sm">
                            {{ item.title }}
                        </Label>
                    </Link>
                </li>
            </ul>
        </div>

        <JoinProDialog />
    </AppShell>
</template>
