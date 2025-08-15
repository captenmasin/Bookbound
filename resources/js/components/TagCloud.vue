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
    <ul class="space-y-1 space-x-1">
        <li
            v-for="tag in tags.slice(0, tagsLimit)"
            :key="tag.slug"
            class="inline-block"
        >
            <Link
                :href="useRoute('user.books.index', { tag: tag.slug })"
                class="rounded-full px-2 text-xs py-0.5 bg-black/2 text-muted-foreground hover:bg-primary hover:text-primary-foreground dark:bg-primary/10 md:transition-colors dark:hover:text-primary">
                {{ tag.name }}
            </Link>
        </li>
        <li
            v-if="tags.length > tagsLimit"
            class="inline-block">
            <button
                class="cursor-pointer rounded-full px-2 text-xs bg-primary/10 py-0.5 text-primary hover:bg-primary/20"
                @click="tagsLimit = 999"
            >
                +{{ tags.length - tagsLimit }} more
            </button>
        </li>
    </ul>
</template>
