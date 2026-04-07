<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import { GoodreadsImport } from '@/types/goodreads-import'
import { PropType, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Progress } from '@/components/ui/progress'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
    activeImport: {
        type: Object as PropType<GoodreadsImport | null>,
        default: null,
    },
    recentImports: {
        type: Array as PropType<GoodreadsImport[]>,
        default: () => [],
    },
})

const form = useForm({
    file: null as File | null,
})

const fileName = ref('')

function setFile(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0] ?? null

    form.file = file
    fileName.value = file?.name ?? ''
}

function submit() {
    form.post(useRoute('user.books.imports.store'), {
        preserveScroll: true,
    })
}

defineOptions({ layout: AppLayout })
</script>

<template>
    <div class="mx-auto flex max-w-4xl flex-col gap-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <PageTitle>Import Goodreads</PageTitle>
                <p class="text-sm text-muted-foreground">
                    Upload your Goodreads export to merge shelves, ratings, reviews, and dates into your library.
                </p>
            </div>
            <Button
                variant="outline"
                as-child>
                <Link :href="useRoute('user.books.index')">
                    Back to Library
                </Link>
            </Button>
        </div>

        <Alert v-if="activeImport">
            <Icon
                name="LoaderCircle"
                class="size-4 animate-spin" />
            <AlertTitle>Import in progress</AlertTitle>
            <AlertDescription class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <span>
                    {{ activeImport.original_filename }} is still processing. You can monitor progress on the status page.
                </span>
                <Button
                    size="sm"
                    as-child>
                    <Link :href="activeImport.links.show">
                        View Status
                    </Link>
                </Button>
            </AlertDescription>
        </Alert>

        <Card>
            <CardHeader>
                <CardTitle>Upload CSV</CardTitle>
                <CardDescription>
                    Goodreads exports usually arrive as `goodreads_library_export.csv`.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form
                    class="flex flex-col gap-4"
                    @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Input
                            type="file"
                            accept=".csv,text/csv"
                            @change="setFile" />
                        <p
                            v-if="fileName"
                            class="text-sm text-muted-foreground">
                            Selected: {{ fileName }}
                        </p>
                        <InputError :message="form.errors.file" />
                    </div>

                    <div class="flex flex-col gap-3 rounded border border-dashed border-primary/20 p-4 text-sm text-muted-foreground">
                        <p>The importer will:</p>
                        <ul class="list-disc pl-4">
                            <li>map Goodreads shelves onto your Bookbound status,</li>
                            <li>merge non-exclusive shelves into your user tags,</li>
                            <li>upsert ratings and review text,</li>
                            <li>preserve date added and date read when present.</li>
                        </ul>
                    </div>

                    <div class="flex justify-end">
                        <Button
                            type="submit"
                            :disabled="form.processing || !form.file || !!activeImport">
                            Start Import
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <div class="grid gap-4">
            <h2 class="font-serif text-2xl font-semibold">
                Recent Imports
            </h2>
            <div
                v-if="!props.recentImports.length"
                class="rounded-lg border-2 border-dashed border-primary/10 px-4 py-8 text-center text-sm text-muted-foreground">
                No Goodreads imports yet.
            </div>
            <div
                v-else
                class="grid gap-4">
                <Card
                    v-for="item in props.recentImports"
                    :key="item.id"
                    class="gap-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="grid gap-1">
                            <p class="font-medium">
                                {{ item.original_filename }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Status: <span class="capitalize">{{ item.status }}</span>
                            </p>
                        </div>
                        <Button
                            size="sm"
                            variant="outline"
                            as-child>
                            <Link :href="item.links.show">
                                View Import
                            </Link>
                        </Button>
                    </div>
                    <Progress :model-value="item.progress_percent" />
                    <div class="grid gap-1 text-sm text-muted-foreground md:grid-cols-4">
                        <p>{{ item.processed_rows }} / {{ item.total_rows || 0 }} processed</p>
                        <p>{{ item.imported_rows }} imported</p>
                        <p>{{ item.merged_rows }} merged</p>
                        <p>{{ item.skipped_rows }} skipped</p>
                    </div>
                </Card>
            </div>
        </div>
    </div>
</template>
