<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import SingleActivity from '@/components/SingleActivity.vue'
import CustomPagination from '@/components/CustomPagination.vue'
import { PropType } from 'vue'
import { Activity } from '@/types/activity'
import { Paginated } from '@/types/pagination'

defineOptions({ layout: AppLayout })

const props = defineProps({
    activities: {
        type: Object as PropType<Paginated<Activity>>,
        default: () => ({ data: [], links: {}, meta: {} })
    }
})
</script>

<template>
    <div>
        <PageTitle class="mb-4">
            Your Activities
        </PageTitle>

        <div
            v-if="activities.meta.total === 0 || activities.data.length === 0"
            class="mb-4 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center text-sm border-primary/10 text-muted-foreground md:py-12"
        >
            <Icon
                name="Activity"
                class="size-8" />
            <h3 class="font-serif text-2xl font-semibold">
                Nothing to see here
            </h3>
            <p v-if="activities.data.length === 0">
                There's no activities on this page
            </p>
            <p v-else>
                You haven't done anything yet. Start by adding a book or a note!
            </p>
        </div>

        <ul
            v-else
            class="rounded-xl bg-white shadow divide-y divide-muted dark:divide-zinc-950 dark:bg-zinc-900">
            <SingleActivity
                v-for="activity in props.activities.data"
                :key="activity.id"
                :activity="activity"
            />
        </ul>
        <CustomPagination
            v-if="activities.meta.total > activities.meta.per_page"
            class="my-4"
            :meta="activities.meta" />
    </div>
</template>
