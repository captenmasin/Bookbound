<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import BarcodeScanner from '@/components/books/BarcodeScanner.vue'

defineOptions({
    layout: AppLayout
})
</script>

<template>
    <div class="mx-auto w-full max-w-4xl">
        <div class="flex items-center justify-between">
            <PageTitle> Scan Barcode </PageTitle>
        </div>
        <div
            class="mt-4">
            <BarcodeScanner />
        </div>
    </div>
</template>
