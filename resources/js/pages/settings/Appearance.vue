<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import AppearanceTabs from '@/components/AppearanceTabs.vue'
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Label } from '@/components/ui/label'
import { usePwa } from '@/composables/usePwa'
import { Switch } from '@/components/ui/switch'
import { useUserSettings } from '@/composables/useUserSettings'

const page = usePage()
const { updateMultipleSettings } = useUserSettings()

const formError = ref(null)

const settings = ref({
    'library.tilt_books': page.props.auth.user.settings?.library?.tilt_books
})

const { isPwa } = usePwa()

function submitForm () {
    updateMultipleSettings(settings.value)
        .catch(error => {
            formError.value = error?.message || 'An error occurred while updating settings.'
        })
}

watch(
    () => settings,
    (newVal, oldVal) => {
        submitForm()
    },
    { deep: true }
)

defineOptions({
    layout: SettingsLayout
})
</script>

<template>
    <div class="space-y-6">
        <div
            v-if="!isPwa"
            class="grid grid-cols-1 gap-2 md:grid-cols-2">
            <Label
                for="name"
                class="grid gap-1">
                <p>Theme</p>
                <p class="text-xs text-muted-foreground">
                    Enable dark mode, light mode, or system preference.
                </p>
            </Label>
            <div class="flex md:justify-end">
                <AppearanceTabs />
            </div>
        </div>

        <div class="grid grid-cols-3 md:grid-cols-2 items-center">
            <Label
                for="library-tilt"
                class="grid col-span-2 md:col-span-1 gap-1">
                <p>Book tilting</p>
                <p class="text-xs text-muted-foreground">
                    Enable or disable the tilt effect for books in your library.
                </p>
                <InputError
                    v-if="formError"
                    :message="formError" />
            </Label>
            <div class="flex flex-col w-full items-end">
                <Switch
                    id="library-tilt"
                    v-model="settings['library.tilt_books']" />
            </div>
        </div>
    </div>
</template>
