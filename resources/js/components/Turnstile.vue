<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, watch, defineEmits, defineProps, toRef } from 'vue'

/** Minimal Window type so TS is happy */
declare global {
    interface Window {
        turnstile?: {
            render: (el: Element, opts: Record<string, any>) => string
            reset: (id?: string) => void
            remove: (id?: string) => void
            getResponse: (id?: string) => string | null
        }
    }
}

/** Load the Turnstile script once (singleton) */
let turnstileLoader: Promise<void> | null = null
function loadTurnstile (): Promise<void> {
    if (window.turnstile) return Promise.resolve()
    if (turnstileLoader) return turnstileLoader
    turnstileLoader = new Promise((resolve, reject) => {
        const src = 'https://challenges.cloudflare.com/turnstile/v0/api.js'
        if (document.querySelector(`script[src="${src}"]`)) {
            // If already present, resolve when it finishes initializing
            const check = () => (window.turnstile ? resolve() : setTimeout(check, 30))
            return check()
        }
        const s = document.createElement('script')
        s.src = src
        s.async = true
        s.defer = true
        s.onload = () => resolve()
        s.onerror = () => reject(new Error('Failed to load Turnstile'))
        document.head.appendChild(s)
    })
    return turnstileLoader
}

const props = defineProps<{
    /** PUBLIC sitekey from your CF widget (required) */
    sitekey: string
    /** Optional Turnstile options */
    action?: string
    cData?: string
    theme?: 'auto' | 'light' | 'dark'
    size?: 'normal' | 'flexible' | 'compact'
    tabindex?: number
    retry?: 'auto' | 'never'
    retryInterval?: number
    /** v-model for token */
    modelValue?: string | null
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', v: string | null): void
    (e: 'load', id: string): void
    (e: 'callback', token: string): void
    (e: 'expired'): void
    (e: 'error', msg?: unknown): void
}>()

const container = ref<HTMLElement | null>(null)
const widgetId = ref<string | null>(null)

function renderWidget () {
    if (!container.value || !window.turnstile) return
    if (!props.sitekey?.trim()) return
    // Clean any previous instance (defensive)
    if (widgetId.value && window.turnstile.remove) {
        try { window.turnstile.remove(widgetId.value) } catch {}
        widgetId.value = null
    }
    widgetId.value = window.turnstile.render(container.value, {
        sitekey: props.sitekey.trim(),
        action: props.action,
        cData: props.cData,
        theme: props.theme ?? 'auto',
        size: props.size ?? 'normal',
        tabindex: props.tabindex ?? 0,
        retry: props.retry ?? 'auto',
        'retry-interval': props.retryInterval ?? 8000,
        callback: (token: string) => {
            emit('update:modelValue', token)
            emit('callback', token)
        },
        'expired-callback': () => {
            emit('update:modelValue', null)
            emit('expired')
        },
        'error-callback': (m: unknown) => {
            emit('update:modelValue', null)
            emit('error', m)
        }
    })
    emit('load', widgetId.value!)
}

onMounted(async () => {
    // Only render on client
    await loadTurnstile()
    renderWidget()
})

onBeforeUnmount(() => {
    if (widgetId.value && window.turnstile?.remove) {
        try { window.turnstile.remove(widgetId.value) } catch {}
    }
})

/** Public methods for parent (reset after submit, etc.) */
function reset () {
    if (window.turnstile?.reset) window.turnstile.reset(widgetId.value ?? undefined)
    emit('update:modelValue', null)
}
function getResponse (): string | null {
    return window.turnstile?.getResponse?.(widgetId.value ?? undefined) ?? null
}
defineExpose({ reset, getResponse })
</script>

<template>
    <!-- Render target -->
    <div ref="container" />
</template>
