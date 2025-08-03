<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, watch } from 'vue'
import { type NavItem } from '@/types'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { Link, router, usePage } from '@inertiajs/vue3'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: useRoute('user.settings.profile.edit')
    },
    {
        title: 'Password',
        href: useRoute('user.settings.password.edit')
    },
    {
        title: 'Appearance',
        href: useRoute('user.settings.appearance')
    },
    {
        title: 'Danger zone',
        href: useRoute('user.settings.profile.danger')
    }
]

const page = usePage()
const currentPath = ref(page.props.currentUrl)

const selectedSettingPage = ref(sidebarNavItems.find(item => item.href === currentPath.value)?.href)

watch(selectedSettingPage, (newValue) => {
    if (newValue) {
        router.get(newValue)
    }
}, { immediate: false })

router.on('navigate', (event) => {
    currentPath.value = event.detail.page.props.currentUrl
})
</script>

<template>
    <AppLayout>
        <div class="px-4 max-w-5xl ">
            <Heading
                title="Settings"
                description="Manage your profile and account settings" />

            <div class="flex flex-col space-y-6 md:space-y-0 lg:space-y-0 space-x-8 lg:space-x-12 md:flex-row">
                <aside class="w-full md:w-48">
                    <div class=" flex md:hidden">
                        <Select
                            v-model="selectedSettingPage"
                            class="w-full">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Settings..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem
                                        v-for="item in sidebarNavItems"
                                        :key="item.href"
                                        :value="item.href">
                                        {{ item.title }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <nav class="flex-col space-y-1 hidden md:flex space-x-0">
                        <Button
                            v-for="item in sidebarNavItems"
                            :key="item.href"
                            variant="ghost"
                            :class="['w-full justify-start', { 'bg-accent': currentPath === item.href }]"
                            as-child
                        >
                            <Link :href="item.href">
                                {{ item.title }}
                            </Link>
                        </Button>
                    </nav>
                </aside>

                <div class="flex-1">
                    <section class="space-y-12">
                        <slot />
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
