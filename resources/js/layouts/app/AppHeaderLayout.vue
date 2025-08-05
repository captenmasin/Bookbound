<script setup lang="ts">
import AppShell from '@/components/AppShell.vue'
import AppHeader from '@/components/AppHeader.vue'
import AppContent from '@/components/AppContent.vue'
import { logicAnd } from '@vueuse/math'
import { Label } from '@/components/ui/label'
import { useRoute } from '@/composables/useRoute'
import { Link, router, usePage } from '@inertiajs/vue3'
import type { BreadcrumbItemType, NavItem } from '@/types'
import { useIsCurrentUrl } from '@/composables/useIsCurrentUrl'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useActiveElement, useMagicKeys, whenever } from '@vueuse/core'
import { Home, LibraryBig, PlusSquareIcon, ScanBarcode } from 'lucide-vue-next'

const page = usePage()
const breadcrumbs = ref(page.props.breadcrumbs as BreadcrumbItemType[] | undefined)

const mainNavItems = ref<NavItem[]>([
    {
        title: 'Home',
        href: useRoute('home'),
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
        title: 'Add Book',
        href: useRoute('books.search'),
        icon: PlusSquareIcon,
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
const notUsingInput = computed(() =>
    activeElement.value?.tagName !== 'INPUT' &&
    activeElement.value?.tagName !== 'TEXTAREA')

whenever(logicAnd(h, notUsingInput), () => router.get(useRoute('home')))
whenever(logicAnd(b, notUsingInput), () => router.get(useRoute('user.books.index')))
whenever(logicAnd(s, notUsingInput), () => router.get(useRoute('books.search')))

const activeItemStyles = computed(
    () => (item: NavItem) => (item.isActive ? 'text-primary' : '')
)

function setActiveItems () {
    mainNavItems.value.forEach(item => {
        item.isActive = useIsCurrentUrl(item.href)
    })
}

function handleClick (item: NavItem) {
    mainNavItems.value.forEach(navItem => {
        navItem.isActive = (navItem.href === item.href)
    })
}

onMounted(() => nextTick(() => setActiveItems()))

router.on('navigate', (event) => {
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
            :nav-items="mainNavItems"
            :breadcrumbs="breadcrumbs" />
        <AppContent
            class="mt-4">
            <slot />
        </AppContent>
        <div
            style="padding-bottom: env(safe-area-inset-bottom)"
            class="sticky lg:hidden bg-background/75 border-t border-background-foreground backdrop-blur-sm bottom-0 left-0 right-0 z-50">
            <ul class="flex items-center w-full  pb-2 max-w-xl mx-auto">
                <li
                    v-for="item in mainNavItems"
                    :key="item.title"
                    class="flex flex-1">
                    <Link
                        :href="item.href"
                        prefetch
                        :class="[activeItemStyles(item)]"
                        class="flex flex-col sm:flex-row py-2 gap-1 sm:gap-2 relative w-full items-center justify-center text-sm text-foreground hover:text-primary"
                        @click="handleClick(item)">
                        <div
                            :class="[item.isActive ? 'bg-primary/10' : 'bg-transparent']"
                            class="rounded-full px-5 sm:px-4 sm:py-1 py-1.5 transition-all">
                            <component
                                :is="item.icon"
                                class="size-5" />
                        </div>
                        <Label class="font-medium text-xs sm:text-sm">
                            {{ item.title }}
                        </Label>
                    </Link>
                </li>
            </ul>
        </div>
    </AppShell>
</template>
