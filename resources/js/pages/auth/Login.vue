<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import AuthBase from '@/layouts/AuthLayout.vue'
import TextLink from '@/components/TextLink.vue'
import InputError from '@/components/InputError.vue'
import { toast } from 'vue-sonner'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { LoaderCircle } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { router, useForm } from '@inertiajs/vue3'
import { Checkbox } from '@/components/ui/checkbox'
import { useRequest } from '@/composables/useRequest'
import { Separator } from '@/components/ui/separator'

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    redirect?: string;
}>()

const form = useForm({
    login: '',
    password: '',
    remember: true,
    redirect: props.redirect || null
})

const submit = async () => {
    await fetch('/sanctum/csrf-cookie', {
        credentials: 'include'
    })

    form.post(useRoute('login'), {
        onFinish: () => {
            form.reset('password')
        }
    })
}

async function loginWithPassKey () {
    try {
        const response = await useRequest(useRoute('passkeys.authentication_options'), 'GET')

        const options = typeof response === 'string' ? JSON.parse(response) : response

        const startAuthenticationResponse = await window.startAuthentication({ optionsJSON: options })

        router.post(useRoute('passkeys.login'), {
            start_authentication_response: JSON.stringify(startAuthenticationResponse)
        })
    } catch (error) {
        toast.error('Failed to log in with passkey. Please try again.')
    }
}
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email/username and password below to log in">
        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form
            class="flex flex-col gap-6"
            @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="login">Email address or username</Label>
                    <Input
                        id="login"
                        v-model="form.login"
                        autofocus
                        :tabindex="1" />
                    <InputError :message="form.errors.login" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="useRoute('password.request')"
                            class="text-sm"
                            :tabindex="5">
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label
                        for="remember"
                        class="flex items-center space-x-3">
                        <Checkbox
                            id="remember"
                            v-model:checked="form.remember"
                            :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="form.processing">
                    <LoaderCircle
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin" />
                    Log in
                </Button>
            </div>

            <div class="my-0 flex items-center">
                <Separator class="flex flex-1" />
                <span class="flex px-4 text-sm text-muted-foreground">or</span>
                <Separator class="flex flex-1" />
            </div>

            <Button
                type="button"
                variant="secondary"
                @click="loginWithPassKey">
                <Icon
                    name="Fingerprint"
                    class="size-4" />
                Use Passkey
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink
                    :href="useRoute('register')"
                    :tabindex="5">
                    Sign up
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
