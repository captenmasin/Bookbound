<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import JoinProTrigger from '@/components/JoinProTrigger.vue'
import { onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { useCookies } from '@vueuse/integrations/useCookies'

const cookies = useCookies(['displayProBanner'])

const { subscribedToPro } = useAuthedUser()

const displayProBanner = ref(false)

function closeProBanner () {
    displayProBanner.value = false
    cookies.set('displayProBanner', false)
}

onMounted(() => {
    if (!subscribedToPro.value && cookies.get('displayProBanner') !== false) {
        displayProBanner.value = true
    }
})
</script>

<template>
    <Transition>
        <section
            v-show="displayProBanner"
            class="mb-6 border border-primary bg-primary px-4 py-4 text-white md:mb-0 md:px-5"
        >
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center border border-white/20 bg-white/10"
                    >
                        <Icon
                            name="Sparkles"
                            class="size-4" />
                    </div>

                    <div>
                        <h2 class="font-serif text-xl text-white">
                            Upgrade to Pro
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-white/80">
                            Unlock unlimited books, private notes, and custom
                            covers.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <JoinProTrigger as-child>
                        <Button
                            variant="white"
                            class="text-primary"
                        >
                            Upgrade
                        </Button>
                    </JoinProTrigger>

                    <button
                        class="flex size-9 cursor-pointer items-center justify-center border border-white/15 bg-white/10 text-white/70 transition hover:bg-white/15 hover:text-white"
                        @click="closeProBanner"
                    >
                        <Icon
                            name="X"
                            class="size-4" />
                    </button>
                </div>
            </div>
        </section>
    </Transition>
</template>
