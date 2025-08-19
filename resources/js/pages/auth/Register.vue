<script setup lang="ts">
import Turnstile from 'cfturnstile-vue3'
import AuthBase from '@/layouts/AuthLayout.vue'
import TextLink from '@/components/TextLink.vue'
import InputError from '@/components/InputError.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { LoaderCircle } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { useForm, usePage } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    cf_response: null
})

const page = usePage()

function verifyTurnstile (token) {
    form.cf_response = token
}

const submit = () => {
    form.post(useRoute('register'), {
        onFinish: () => form.reset('password', 'password_confirmation')
    })
}
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account">
        <form
            class="flex flex-col gap-6"
            @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        placeholder="Full name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="name">Username</Label>
                    <Input
                        id="username"
                        v-model="form.username"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="username"
                        placeholder="Username" />
                    <InputError :message="form.errors.username" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            placeholder="Password"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                    </div>
                </div>
                <div class="-mt-5">
                    <InputError :message="form.errors.password" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <div
                    v-if="page.props.auth.turnstile.enabled"
                    class="flex flex-col -mt-4 items-center justify-center">
                    <Turnstile
                        :sitekey="'3' + page.props.auth.turnstile.site_key"
                        @callback="verifyTurnstile" />
                    <InputError
                        v-if="page.props.errors.cf_response"
                        :message="page.props.errors.cf_response" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="5"
                    :disabled="form.processing">
                    <LoaderCircle
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="useRoute('login')"
                    class="underline underline-offset-4"
                    :tabindex="6">
                    Log in
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
