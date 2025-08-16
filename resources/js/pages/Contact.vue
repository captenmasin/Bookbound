<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent } from '@/components/ui/card'
import { useAuthedUser } from '@/composables/useAuthedUser'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select'

defineProps({
    options: {
        type: Object as () => Record<string, any>,
        default: () => ({})
    },
    email: {
        type: String,
        default: ''
    }
})

const { authed, authedUser } = useAuthedUser()

const form = useForm({
    name: authed ? authedUser.value?.name : '',
    email: authed ? authedUser.value?.email : '',
    subject: '',
    message: ''
})

function submit () {
    form.post(useRoute('contact.submit'), {
        onSuccess: () => {
            form.reset()
        },
        preserveScroll: true,
        preserveState: true
    })
}

defineOptions({ layout: AppLayout })
</script>

<template>
    <div class="mt-4 md:mt-12">
        <div class="flex w-full not-pwa:max-w-5xl mx-auto flex-col justify-between gap-4 md:gap-10 lg:flex-row lg:gap-20">
            <div class="flex flex-col md:max-w-lg gap-6 justify-between md:gap-2">
                <div class="flex flex-col gap-2">
                    <PageTitle class="md:text-5xl">
                        Contact Us
                    </PageTitle>
                    <p class="text-muted-foreground text-sm md:text-base text-pretty">
                        If you have any questions, suggestions, or feedback, please feel free to reach out to us using the form or the contact details provided below. We value your input and will do our best to respond promptly.
                    </p>
                </div>
                <div v-if="email">
                    <h2 class="font-medium md:text-lg">
                        Contact Details
                    </h2>
                    <a
                        :href="`mailto:${email}`"
                        class="text-primary text-sm hover:underline">
                        {{ email }}
                    </a>
                </div>
            </div>
            <Card class="flex flex-1 max-w-md flex-col gap-6">
                <CardContent>
                    <form
                        class="grid w-full items-center gap-6"
                        @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                autofocus
                                :tabindex="1" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email">Email </Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autofocus
                                :tabindex="2" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="subject">Subject</Label>
                            <Select v-model="form.subject">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="How can we help?" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="(label, value) in options"
                                        :key="value"
                                        :value="value">
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <InputError :message="form.errors.subject" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="message">Message</Label>
                            <Textarea
                                id="message"
                                v-model="form.message"
                                autofocus
                                :tabindex="4" />
                            <InputError :message="form.errors.message" />
                        </div>
                        <Button :disabled="form.processing || !form.isDirty">
                            Send Message
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
