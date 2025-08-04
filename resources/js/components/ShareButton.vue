<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import Bluesky from '~/images/icons/bluesky.svg?raw'
import ThreadsIcon from '~/images/icons/threads.svg?raw'
import BlueskyIcon from '~/images/icons/bluesky.svg?raw'
import FacebookIcon from '~/images/icons/facebook.svg?raw'
import LinkedInIcon from '~/images/icons/linkedin.svg?raw'
import WhatsappIcon from '~/images/icons/whatsapp.svg?raw'
import XTwitterIcon from '~/images/icons/x-twitter.svg?raw'
import { ref, useSlots } from 'vue'
import { Button } from '@/components/ui/button'
import { ShareNetwork } from 'vue3-social-sharing'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'

const props = defineProps({
    url: {
        type: String,
        required: true
    },
    title: {
        type: String,
        default: ''
    },
    text: {
        type: String,
        default: ''
    },
    modalTitle: {
        type: String,
        default: 'Share'
    }
})

const shareModalOpen = ref(false)

function closeModal () {
    shareModalOpen.value = false
}

function showModal () {
    shareModalOpen.value = true
}

function handleClick () {
    if (navigator.share && !navigator.canShare) {
        navigator.share({
            title: props.title,
            text: props.text,
            url: props.url
        }).catch((error) => {
            console.error('Error sharing:', error)
        })
    } else {
        console.warn('Web Share API is not supported in this browser.')
        // show modal
        showModal()
    }
}

const shareNetworks = [
    {
        name: 'facebook',
        icon: FacebookIcon,
        color: '#3b5998'
    },
    {
        name: 'x',
        icon: XTwitterIcon,
        color: '#000'
    },
    {
        name: 'linkedin',
        icon: LinkedInIcon,
        color: '#0077b5'
    },
    {
        name: 'whatsapp',
        icon: WhatsappIcon,
        color: '#25d366'
    },
    {
        name: 'threads',
        icon: ThreadsIcon,
        color: '#000'
    },
    {
        name: 'bluesky',
        icon: BlueskyIcon,
        color: '#1c9bf0'
    }
]

const slots = useSlots()
</script>

<template>
    <div>
        <Button
            variant="white"
            class="cursor-pointer"
            :size="slots.default ? 'sm' : 'icon'"
            @click="handleClick">
            <Icon name="share" />
            <slot />
        </Button>

        <Dialog v-model:open="shareModalOpen">
            <DialogContent class="sm:max-w-sm">
                <DialogHeader class="space-y-3">
                    <DialogTitle>
                        {{ modalTitle }}
                    </DialogTitle>
                </DialogHeader>

                <div class="flex items-center justify-between flex-wrap">
                    <ShareNetwork
                        v-for="network in shareNetworks"
                        v-slot="{ share }"
                        :key="network.name"
                        :network="network.name"
                        :url="url"
                        :title="title"
                        :description="text"
                    >
                        <button
                            :style="{ backgroundColor: network.color }"
                            class="flex p-2 justify-center aspect-square items-center rounded-lg gap-1 cursor-pointer"
                            @click="share">
                            <div
                                class="flex mx-auto w-6 text-white"
                                v-html="network.icon" />
                            <span class="sr-only">{{ network.name }}</span>
                        </button>
                    </ShareNetwork>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
