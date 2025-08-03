<script setup lang="ts">
import UserAvatar from '@/components/UserAvatar.vue'
import { computed } from 'vue'
import type { User } from '@/types'

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false
})

// Compute whether we should show the avatar image
const showAvatar = computed(() => props.user.avatar && props.user.avatar !== '')
</script>

<template>
    <UserAvatar
        :user="user"
        class="size-8" />

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ user.name }}</span>
        <span
            v-if="showEmail"
            class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
    </div>
</template>
