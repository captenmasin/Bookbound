<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import BarcodeScanner from '@/components/books/BarcodeScanner.vue'

defineOptions({
    layout: AppLayout
})
</script>

<template>
    <div class="w-full max-w-4xl mx-auto">
        <div class="flex items-center justify-between">
            <PageTitle> Scan Book </PageTitle>
        </div>
        <div
            class="mt-4">
            <BarcodeScanner />
        </div>
    </div>
</template>
