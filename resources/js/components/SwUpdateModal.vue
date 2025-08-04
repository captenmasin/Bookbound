<script setup lang="ts">
import { ref, defineExpose } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const visible = ref(false)
let worker: ServiceWorker | null = null

function show (newWorker: ServiceWorker) {
    worker = newWorker
    visible.value = true
}

function close () {
    visible.value = false
    worker = null
}

function refresh () {
    if (worker) {
        worker.postMessage({ type: 'SKIP_WAITING' })
        window.location.reload()
    }
}

const page = usePage()

defineExpose({ show })
</script>

<template>
    <div
        v-if="visible"
        :data-state="visible ? 'open' : 'closed'"
        class="fixed inset-0 z-50 p-4 bg-black/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0">
        <div class="fixed left-1/2 top-1/2 z-50 grid w-full -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 max-w-[96vw] data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] rounded-lg sm:max-w-[425px]">
            <div class="flex flex-col gap-y-1.5 text-center sm:text-left">
                <h2 class="text-lg font-semibold leading-none tracking-tight">
                    Update Available
                </h2>
                <p class="text-sm text-muted-foreground">
                    A new version of {{ page.props.app.name }} is available. Refresh to update?
                </p>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:gap-x-2">
                <Button
                    variant="ghost"
                    @click="close">
                    Cancel
                </Button>
                <Button
                    variant="default"
                    @click="refresh">
                    Refresh
                </Button>
            </div>
        </div>
    </div>
</template>
