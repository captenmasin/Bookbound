<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { PublicUser, User } from '@/types/user'
import { computed, PropType } from 'vue'
import { getInitials } from '@/composables/useInitials'
import { useContrast } from '@/composables/useContrast'
import { useImageTransform } from '@/composables/useImageTransform'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

const props = defineProps({
    user: {
        type: Object as PropType<User | PublicUser>,
        required: true
    },
    class: {
        type: String,
        default: ''
    },
    size: {
        type: Number,
        default: 32
    },
    fontSize: {
        type: String,
        default: ''
    },
    useUserColour: {
        type: Boolean,
        default: true
    }
})

const { getImageUrl } = useImageTransform()

const textColour = computed(() => {
    return useContrast(props.user.colour, 'text-zinc-900', 'text-white')
})
</script>

<template>
    <Avatar
        :key="user.avatar"
        :class="cn('size-8 overflow-hidden rounded-full', props.class)">
        <AvatarImage
            v-if="user.avatar && user.avatar !== ''"
            :src="getImageUrl(user.avatar, { width: size, height: size, crop: 'center', scale: false })"
            :alt="user.name"
        />
        <AvatarFallback
            :class="[
                fontSize,
                props.useUserColour ? textColour : 'bg-secondary text-secondary-foreground',
            ]"
            :style="props.useUserColour ? { backgroundColor: user.colour } : {}"
            class="rounded-full font-semibold"
        >
            {{ getInitials(user?.name) }}
        </AvatarFallback>
    </Avatar>
</template>
