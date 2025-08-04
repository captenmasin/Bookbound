<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import useEmitter from '@/composables/useEmitter'
import InputError from '@/components/InputError.vue'
import ColorPicker from '@/components/ColorPicker.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { ref } from 'vue'
import { toast } from 'vue-sonner'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { getInitials } from '@/composables/useInitials'
import { Link, router, useForm } from '@inertiajs/vue3'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { useUserSettings } from '@/composables/useUserSettings'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>()

const { authedUser } = useAuthedUser()

const { getSingleSetting } = useUserSettings()

const form = useForm({
    name: authedUser.value?.name,
    username: authedUser.value?.username,
    email: authedUser.value?.email,
    avatar: authedUser.value?.avatar,
    profile_colour: getSingleSetting('profile.colour', '#000000')
})

const fileInput = ref<HTMLInputElement | null>(null)
const fileInputKey = ref(0)
const previewUrl = ref<string | null>(authedUser.value?.avatar || null)

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        form.avatar = target.files[0]

        form.clearErrors('avatar')
        previewUrl.value = URL.createObjectURL(target.files[0])
    }
}

const resetForm = () => {
    form.reset()
    form.clearErrors()
    fileInputKey.value++
    previewUrl.value = authedUser.value?.avatar || null
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

function deleteAvatar () {
    router.delete(useRoute('user.settings.profile.avatar.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            fileInputKey.value++
            previewUrl.value = authedUser.value?.avatar || null
            console.log(fileInputKey.value)
        },
        onError: () => {
            toast.error('Failed to delete avatar')
        }
    })
}

const submit = () => {
    form.transform((data) => {
        if (typeof data.avatar === 'string') {
            delete data.avatar
        }

        return data
    }).post(useRoute('user.settings.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            useEmitter.emit('avatar:updated')
            toast.success('Profile updated successfully')
        }
    })
}

defineOptions({
    layout: SettingsLayout
})
</script>

<template>
    <div class="flex flex-col space-y-6">
        <form
            class="space-y-6 md:space-y-8"
            @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-1">
                <Label
                    for="name"
                    class="grid gap-1">
                    <p>Full name</p>
                    <p class="text-xs text-muted-foreground">
                        Your display name
                    </p>
                </Label>
                <div class="flex flex-col w-full">
                    <Input
                        id="name"
                        v-model="form.name"
                        class="block w-full"
                        required
                        autocomplete="name"
                        placeholder="Full name" />
                    <InputError
                        class="mt-2"
                        :message="form.errors.name" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-1">
                <Label
                    for="username"
                    class="grid gap-1">
                    <p>Username</p>
                    <p class="text-xs text-muted-foreground">
                        A unique name for your profile
                    </p>
                </Label>
                <div class="flex flex-col w-full">
                    <Input
                        id="username"
                        v-model="form.username"
                        required
                        class="block w-full"
                        autocomplete="username"
                        placeholder="Username" />
                    <InputError
                        class="mt-2"
                        :message="form.errors.username" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-1">
                <Label
                    for="avatar"
                    class="grid gap-1">
                    <p>Avatar</p>
                    <p class="text-xs text-muted-foreground">
                        A profile picture to represent you
                    </p>
                </Label>

                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <label for="avatar">
                            <Avatar
                                :key="fileInputKey"
                                class="flex overflow-hidden rounded-full border size-10 border-input-border">
                                <AvatarImage
                                    v-if="previewUrl"
                                    :src="previewUrl"
                                    class="aspect-auto object-cover"
                                    :alt="authedUser?.name" />
                                <AvatarFallback class="rounded-full bg-secondary font-semibold text-secondary-foreground">
                                    {{ getInitials(authedUser?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </label>
                        <div class="grid w-full items-start ">
                            <div class="flex gap-2">
                                <Input
                                    id="avatar"
                                    ref="fileInput"
                                    :key="fileInputKey"
                                    type="file"
                                    accept="image/*"
                                    @input="handleFileChange" />
                                <Button
                                    v-if="authedUser?.avatar"
                                    type="button"
                                    variant="destructive-ghost"
                                    size="icon"
                                    @click="deleteAvatar">
                                    <span class="sr-only">Remove avatar</span>
                                    <Icon name="Trash" />
                                </Button>
                            </div>

                            <progress
                                :class="form.progress ? 'opacity-100' : 'opacity-0'"
                                :value="form.progress?.percentage"
                                class="-mt-1 w-full px-1 form-progress-bar h-[2px]"
                                max="100"
                            >
                                {{ form.progress?.percentage }}%
                            </progress>
                        </div>
                    </div>
                    <InputError
                        class="mt-2"
                        :message="form.errors.avatar" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-1">
                <Label
                    for="profile_colour"
                    class="grid gap-1">
                    <p>
                        Favourite Colour
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Your favourite colour for your profile
                    </p>
                </Label>

                <div class="flex flex-col w-full">
                    <div>
                        <ColorPicker v-model="form.profile_colour" />
                    </div>

                    <InputError
                        class="mt-2"
                        :message="form.errors.profile_colour" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-1">
                <Label
                    for="email"
                    class="grid gap-1">
                    <p>
                        Email address
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Your email address for notifications and account recovery
                    </p>
                </Label>

                <div class="flex flex-col w-full">
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full"
                        required
                        autocomplete="username"
                        placeholder="Email address"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.email" />
                </div>
            </div>

            <div
                v-if="mustVerifyEmail && !authedUser?.email_verified"
                class="mt-1">
                <p class="text-sm text-muted-foreground">
                    Your email address is unverified.
                    <Link
                        :href="useRoute('verification.send')"
                        method="post"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Click here to resend the verification email.
                    </Link>
                </p>

                <div
                    v-if="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600">
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <div
                    class="mt-8 flex items-center justify-end gap-4">
                    <Button
                        type="button"
                        variant="secondary"
                        :disabled="form.processing || !form.isDirty"
                        @click="resetForm">
                        Reset
                    </Button>

                    <Button
                        type="submit"
                        :disabled="form.processing || !form.isDirty">
                        Save
                    </Button>
                </div>
            </div>
        </form>
    </div>
</template>
