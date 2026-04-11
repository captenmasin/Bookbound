<script setup lang="ts">
import { Tag } from '@/types/tag'
import { PropType, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'

const props = defineProps({
    tags: {
        type: Array as PropType<Tag[]>,
        required: true
    },
    limit: {
        type: Number,
        default: 5
    }
})

const tagsLimit = ref(props.limit)
</script>

<template>
    <ul class="gap-2 flex flex-wrap">
        <li
            v-for="tag in tags.slice(0, tagsLimit)"
            :key="tag.slug"
            class="inline-block"
        >
            <Link
                :href="useRoute('books.search', { q: 'tag: ' + tag.name })"
                class="px-2.5 text-xs py-1 bg-primary/10 uppercase text-zinc-800 hover:bg-primary hover:text-primary-foreground dark:bg-primary/10 md:transition-colors dark:hover:text-primary">
                {{ tag.name }}
            </Link>
        </li>
        <li
            v-if="tags.length > tagsLimit"
            class="inline-block">
            <button
                class="cursor-pointer px-2 text-xs bg-primary/10 py-0.75 text-primary hover:bg-primary/20"
                @click="tagsLimit = 999"
            >
                +{{ tags.length - tagsLimit }} more
            </button>
        </li>
    </ul>
</template>
