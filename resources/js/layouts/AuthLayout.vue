<script setup lang="ts">
import MetaHead from '@/components/MetaHead.vue'
import AuthLayout from '@/layouts/auth/AuthSplitLayout.vue'

defineProps<{
    title?: string;
    description?: string;
}>()
</script>

<template>
    <AuthLayout
        :title="title"
        :description="description">
        <MetaHead />
        <slot />
    </AuthLayout>
</template>
