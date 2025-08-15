<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import { Link } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

defineProps<{
    title?: string;
    description?: string;
}>()
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-6 p-6 min-h-svh bg-muted md:p-10">
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="useRoute('user.books.index')"
                class="flex items-center gap-2 self-center font-medium">
                <div class="flex h-9 w-9 items-center justify-center">
                    <AppLogoIcon class="rounded-lg fill-current text-black size-9 dark:text-white" />
                </div>
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="rounded-xl">
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <CardTitle class="text-xl">
                            {{ title }}
                        </CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
