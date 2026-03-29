<script setup lang="ts">
import { PropType } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious
} from '@/components/ui/pagination'

interface Meta {
    current_page: number;
    last_page: number;
    path: string;
    per_page: number;
    total: number;
}

const props = defineProps({
    meta: {
        type: Object as PropType<Meta>,
        required: true
    }
})

const page = usePage()

function pageUrl (page: number) {
    const [, search = ''] = page.url.split('?')
    const params = new URLSearchParams(search)

    if (page > 1) {
        params.set('page', String(page))
    } else {
        params.delete('page')
    }

    const query = params.toString()
    return `${props.meta.path}${query ? '?' + query : ''}`
}

function previous () {
    router.get(
        pageUrl(props.meta.current_page - 1),
        {},
        {
            // preserveScroll: true,
            // preserveState: true
        }
    )
}

function next () {
    router.get(
        pageUrl(props.meta.current_page + 1),
        {},
        {
            // preserveScroll: true,
            // preserveState: true
        }
    )
}

function prefetch (page: number) {
    if (page < 1 || page > props.meta.last_page) return
    router.prefetch(
        pageUrl(page),
        {
            method: 'get'
        },
        { cacheFor: '1m' }
    )
}
</script>

<template>
    <Pagination
        v-slot="{ page }"
        :items-per-page="meta.per_page"
        :total="meta.total"
        :default-page="meta.current_page">
        <PaginationContent v-slot="{ items }">
            <PaginationPrevious
                @mouseenter="prefetch(page - 1)"
                @click="previous" />

            <template
                v-for="(item, index) in items"
                :key="index">
                <PaginationItem
                    v-if="item.type === 'page'"
                    :value="item.value"
                    as-child
                    :is-active="item.value === page">
                    <Link
                        prefetch
                        :href="pageUrl(item.value)">
                        {{ item.value }}
                    </Link>
                </PaginationItem>
            </template>

            <!--            <PaginationEllipsis-->
            <!--                :index="5" />-->

            <PaginationNext @click="next" />
        </PaginationContent>
    </Pagination>
</template>
