<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { toast } from 'vue-sonner'
import { PropType, ref } from 'vue'
import { UserPasskey } from '@/types/user'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { router, useForm } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute'
import { useRequest } from '@/composables/useRequest'
import { startRegistration } from '@simplewebauthn/browser'

defineProps({
    passkeys: {
        type: Array as PropType<UserPasskey[]>
    }
})

const passwordInput = ref<HTMLInputElement | null>(null)
const currentPasswordInput = ref<HTMLInputElement | null>(null)

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: ''
})

const updatePassword = () => {
    form.put(useRoute('user.settings.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Password updated successfully')
            form.reset()
        },
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation')
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus()
                }
            }

            if (errors.current_password) {
                form.reset('current_password')
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus()
                }
            }
        }
    })
}

async function addPassKey () {
    try {
        const response = await useRequest(useRoute('profile.passkeys.generate-options'), 'GET')

        const options = typeof response === 'string' ? JSON.parse(response) : response

        const credential = await startRegistration(options)

        router.post(useRoute('profile.passkeys.store'), {
            options: JSON.stringify(options),
            passkey: JSON.stringify(credential)
        })
    } catch (error) {
        console.log(error)
    }
}

function deletePasskey (id) {
    if (confirm('Are you SURE you want to delete this passkey?')) {
        router.delete(useRoute('profile.passkeys.delete', id))
    }
}

defineOptions({
    layout: SettingsLayout
})
</script>

<template>
    <div class="flex flex-col space-y-8">
        <form
            class="space-y-6 md:space-y-8"
            @submit.prevent="updatePassword">
            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-1">
                <Label
                    for="password"
                    class="grid gap-1">
                    <p>New password</p>
                    <p class="text-xs text-muted-foreground">
                        Enter a new password to update your account password.
                    </p>
                </Label>
                <div class="flex flex-col w-full">
                    <Input
                        id="password"
                        v-model="form.password"
                        required
                        class="block w-full"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.password" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-1">
                <Label
                    for="password_confirmation"
                    class="grid gap-1">
                    <p>Confirm password</p>
                    <p class="text-xs text-muted-foreground">
                        Please confirm your new password by entering it again.
                    </p>
                </Label>
                <div class="flex flex-col w-full">
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        required
                        class="block w-full"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-1">
                <Label
                    for="current_password"
                    class="grid gap-1">
                    <p>Current password</p>
                    <p class="text-xs text-muted-foreground">
                        Please enter your current password to confirm the change.
                    </p>
                </Label>
                <div class="flex flex-col w-full">
                    <Input
                        id="current_password"
                        v-model="form.current_password"
                        required
                        class="block w-full"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.current_password" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <Button :disabled="form.processing">
                    Save password
                </Button>
            </div>
        </form>

        <HeadingSmall
            title="Passkeys"
            description="Use passkeys to log in without a password"
        />

        <div
            v-if="passkeys && passkeys?.length > 0"
            class="flex flex-col gap-6">
            <div
                v-for="passkey in passkeys"
                :key="passkey.id"
                class="flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <div class="flex">
                        <p class="font-mono bg-muted flex text-xs text-muted-foreground px-2 py-0.5 rounded-full">
                            {{ passkey.name }}
                        </p>
                    </div>
                    <p class="text-sm px-1">
                        Last used at: {{ passkey.last_used_at || 'Never' }}
                    </p>
                </div>
                <Button
                    variant="link"
                    class="text-destructive"
                    @click="deletePasskey(passkey.id)">
                    Delete
                </Button>
            </div>
        </div>

        <div class="flex justify-end">
            <Button
                variant="secondary"
                @click.prevent="addPassKey">
                Add a passkey
            </Button>
        </div>
    </div>
</template>
