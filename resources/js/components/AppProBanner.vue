<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import { onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { useRoute } from '@/composables/useRoute'
import { useAuthedUser } from '@/composables/useAuthedUser'
import { useCookies } from '@vueuse/integrations/useCookies'

const cookies = useCookies(['displayProBanner'])

const { subscribedToPro } = useAuthedUser()

const displayProBanner = ref(false)
const proHighlights = ['Unlimited books', 'Private notes', 'Custom covers']

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
            class="relative mb-6 overflow-hidden border border-primary bg-primary dark:bg-zinc-900 text-white shadow-[0_18px_60px_-28px_rgba(0,0,0,0.6)] md:mb-0 dark:border-white/10"
        >
            <div
                class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/3 border-l border-white/10 bg-white/5 lg:block"
            />

            <div
                class="relative flex flex-col  gap-6 p-5 md:p-6 lg:flex-row lg:items-stretch lg:gap-8"
            >
                <div class="flex-1 flex-col pt-4 items-center justify-center">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-11 items-center justify-center rounded-full border border-white/20 bg-white/10 shadow-inner shadow-white/10 backdrop-blur"
                        >
                            <Icon
                                name="Sparkles"
                                class="size-5" />
                        </div>
                        <div>
                            <p
                                class="text-[11px] font-semibold tracking-[0.26em] text-white/70 uppercase"
                            >
                                Bookbound Pro
                            </p>
                            <h2
                                class="font-serif text-2xl leading-tight text-white md:text-3xl"
                            >
                                Your library without the ceiling
                            </h2>
                        </div>
                    </div>

                    <p
                        class="mt-4 max-w-xl text-sm leading-6 text-white/80 md:text-base"
                    >
                        Unlock the tools that make the app feel complete: keep
                        every book, write private notes, and personalize your
                        shelves with custom covers.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <div
                            v-for="highlight in proHighlights"
                            :key="highlight"
                            class="inline-flex items-center gap-2 border border-white/15 bg-white/10 px-3 py-1.5 text-sm text-white/90 backdrop-blur-sm"
                        >
                            <Icon
                                name="Check"
                                class="size-4 text-white" />
                            <span>{{ highlight }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <Button
                            variant="white"
                            size="lg"
                            as-child
                        >
                            <a :href="useRoute('checkout')"> Upgrade to Pro </a>
                        </Button>
                        <p class="text-sm text-white/65">
                            Cancel any time.
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 border-t border-white/10 pt-5 lg:w-1/3 pl-8 lg:flex-none lg:border-t-0 lg:pt-0"
                >
                    <div
                        class="text-[11px] font-semibold tracking-[0.26em] text-white/60 uppercase"
                    >
                        Included with Pro
                    </div>
                    <div class="grid gap-2.5">
                        <div
                            class="flex items-start gap-3 border border-white/10 bg-black/10 p-3 backdrop-blur-sm"
                        >
                            <Icon
                                name="LibraryBig"
                                class="mt-0.5 size-4 text-white/80"
                            />
                            <div>
                                <p class="text-sm font-medium text-white">
                                    Unlimited collection size
                                </p>
                                <p class="mt-1 text-sm leading-5 text-white/65">
                                    Keep every read, reread, and wishlist title
                                    in one place.
                                </p>
                            </div>
                        </div>
                        <div
                            class="flex items-start gap-3 border border-white/10 bg-black/10 p-3 backdrop-blur-sm"
                        >
                            <Icon
                                name="ImagePlus"
                                class="mt-0.5 size-4 text-white/80"
                            />
                            <div>
                                <p class="text-sm font-medium text-white">
                                    Custom covers
                                </p>
                                <p class="mt-1 text-sm leading-5 text-white/65">
                                    Replace default artwork with editions and
                                    designs that match your shelves.
                                </p>
                            </div>
                        </div>
                        <div
                            class="flex items-start gap-3 border border-white/10 bg-black/10 p-3 backdrop-blur-sm"
                        >
                            <Icon
                                name="NotebookPen"
                                class="mt-0.5 size-4 text-white/80"
                            />
                            <div>
                                <p class="text-sm font-medium text-white">
                                    Notes that stay private
                                </p>
                                <p class="mt-1 text-sm leading-5 text-white/65">
                                    Capture thoughts, quotes, and reading logs
                                    just for you.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button
                class="absolute top-4 right-4 flex size-8 cursor-pointer items-center justify-center border border-white/10 bg-black/10 text-white/55 transition hover:bg-black/20 hover:text-white"
                @click="closeProBanner"
            >
                <Icon
                    name="X"
                    class="size-4" />
            </button>
        </section>
    </Transition>
</template>
