<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import { GoodreadsImport } from '@/types/goodreads-import'
import { PropType, onMounted, onUnmounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import Icon from '@/components/Icon.vue'

const props = defineProps({
    importRecord: {
        type: Object as PropType<GoodreadsImport>,
        required: true,
    },
})

let intervalId: number | null = null

onMounted(() => {
    if (['pending', 'processing'].includes(props.importRecord.status)) {
        intervalId = window.setInterval(() => {
            router.reload({
                only: ['importRecord', 'flash'],
                preserveScroll: true,
            })
        }, 2000)
    }
})

watch(
    () => props.importRecord.status,
    (status) => {
        if (!['pending', 'processing'].includes(status) && intervalId) {
            window.clearInterval(intervalId)
            intervalId = null
        }
    }
)

onUnmounted(() => {
    if (intervalId) {
        window.clearInterval(intervalId)
    }
})

defineOptions({ layout: AppLayout })
</script>

<template>
    <div class="mx-auto flex max-w-4xl flex-col gap-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <PageTitle>Import Status</PageTitle>
                <p class="text-sm text-muted-foreground">
                    {{ importRecord.original_filename }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    as-child>
                    <Link :href="useRoute('user.books.imports.create')">
                        Import Another
                    </Link>
                </Button>
                <Button as-child>
                    <Link :href="useRoute('user.books.index')">
                        Back to Library
                    </Link>
                </Button>
            </div>
        </div>

        <Alert
            v-if="importRecord.status === 'failed'"
            variant="destructive">
            <Icon
                name="TriangleAlert"
                class="size-4" />
            <AlertTitle>Import failed</AlertTitle>
            <AlertDescription>
                {{ importRecord.error_message || 'The import stopped before completion.' }}
            </AlertDescription>
        </Alert>

        <Alert v-else-if="['pending', 'processing'].includes(importRecord.status)">
            <Icon
                name="LoaderCircle"
                class="size-4 animate-spin" />
            <AlertTitle>Import is running</AlertTitle>
            <AlertDescription>
                This page refreshes automatically while the background jobs process your Goodreads export.
            </AlertDescription>
        </Alert>

        <Card class="gap-6">
            <CardHeader>
                <CardTitle class="capitalize">
                    {{ importRecord.status }}
                </CardTitle>
                <CardDescription>
                    {{ importRecord.processed_rows }} of {{ importRecord.total_rows || 0 }} rows processed
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-6">
                <Progress :model-value="importRecord.progress_percent" />

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded border border-dashed border-primary/15 p-4">
                        <p class="text-sm text-muted-foreground">Imported</p>
                        <p class="font-serif text-3xl font-semibold">{{ importRecord.imported_rows }}</p>
                    </div>
                    <div class="rounded border border-dashed border-primary/15 p-4">
                        <p class="text-sm text-muted-foreground">Merged</p>
                        <p class="font-serif text-3xl font-semibold">{{ importRecord.merged_rows }}</p>
                    </div>
                    <div class="rounded border border-dashed border-primary/15 p-4">
                        <p class="text-sm text-muted-foreground">Skipped</p>
                        <p class="font-serif text-3xl font-semibold">{{ importRecord.skipped_rows }}</p>
                    </div>
                </div>

                <div class="grid gap-3 text-sm text-muted-foreground md:grid-cols-2">
                    <p>Failures: {{ importRecord.failed_rows }}</p>
                    <p>Blocked by plan limit: {{ importRecord.blocked_rows }}</p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Skipped Rows</CardTitle>
                <CardDescription>
                    Recent rows that could not be imported automatically.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="!importRecord.failures?.length"
                    class="rounded border border-dashed border-primary/10 px-4 py-6 text-sm text-muted-foreground">
                    No skipped rows recorded for this import.
                </div>
                <ul
                    v-else
                    class="grid gap-3">
                    <li
                        v-for="failure in importRecord.failures"
                        :key="failure.id"
                        class="rounded border p-4">
                        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                            <p class="font-medium">
                                Row {{ failure.row_number }}<span v-if="failure.title">: {{ failure.title }}</span>
                            </p>
                            <p
                                v-if="failure.author"
                                class="text-sm text-muted-foreground">
                                {{ failure.author }}
                            </p>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ failure.reason }}
                        </p>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
