<script setup>
import Silk from '@/components/backgrounds/Silk/Silk.vue'
import HomeScreenshot from '~/images/marketing/home-screenshot.png'
import SplitText from '@/components/textanimations/SplitText/SplitText.vue'
import HomeScreenshotDark from '~/images/marketing/home-screenshot-dark.png'
import { Link, usePage } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute.js'
import { Button } from '@/components/ui/button/index.js'

const page = usePage()
</script>

<template>
    <section class="relative overflow-hidden">
        <div class="silk-container absolute inset-0 z-1 opacity-10">
            <Silk
                :speed="15"
                :scale="1"
                color="#ffffff"
                :noise-intensity="5"
                :rotation="0"
                class="h-full w-full" />
        </div>
        <div class="absolute bottom-0 left-0 z-20 h-36 w-full bg-gradient-to-b from-transparent to-background" />
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[hsl(36,40%,98%)] to-[hsl(36,40%,94%)] dark:from-[hsl(0,0%,10%)] dark:to-[hsl(0,0%,6%)]"
        />
        <div class="relative z-10 container mx-auto flex items-center gap-0 px-4 pt-20 pb-16 sm:pt-48 sm:pb-28">
            <div class="w-5/12 md:pr-24">
                <SplitText
                    text="Your Reading Life at a Glance"
                    class-name="font-serif text-4xl sm:text-5xl md:text-6xl/16 text-pretty font-medium"
                    :delay="100"
                    :duration="0.6"
                    ease="power3.out"
                    split-type="words"
                    :from="{ opacity: 0, y: 40 }"
                    :to="{ opacity: 1, y: 0 }"
                    :threshold="0.1"
                    root-margin="-100px"
                    text-align="left"
                />

                <p
                    data-aos="fade-up"
                    data-aos-delay="300"
                    class="mt-4 text-lg text-foreground">
                    Track what you’re reading, discover new favorites, and keep your library tidy – all without the dusty shelves.
                </p>
                <div
                    data-aos="fade-up"
                    data-aos-delay="400"
                    class="mt-8 flex flex-wrap gap-3">
                    <Button as-child>
                        <Link :href="useRoute('register')">
                            Get Started Free
                        </Link>
                    </Button>
                    <Button
                        variant="ghost"
                        as-child>
                        <a href="#showcase"> See It in Action </a>
                    </Button>
                </div>
            </div>
            <div class="w-7/12 relative">
                <div class="mx-auto w-full">
                    <div
                        data-aos="zoom-in"
                        class="relative rotate-3 rounded-xl border border-sidebar-border/80 bg-white dark:bg-neutral-900 p-2 shadow-sm">
                        <img
                            loading="lazy"
                            :src="HomeScreenshot"
                            :alt="`${page.props.app.name} dashboard showing stats and recent activity`"
                            class="h-auto w-full rounded-lg dark:hidden"
                        >
                        <img
                            loading="lazy"
                            :src="HomeScreenshotDark"
                            :alt="`${page.props.app.name} dashboard showing stats and recent activity`"
                            class="h-auto w-full rounded-lg hidden dark:block"
                        >
                        <!--                                <div-->
                        <!--                                    data-aos="zoom-in"-->
                        <!--                                    data-aos-delay="200"-->
                        <!--                                    class="pointer-events-none absolute -top-6 -right-6 hidden w-52 rotate-4 rounded-lg border border-sidebar-border/80 bg-white p-1.5 shadow md:block"-->
                        <!--                                >-->
                        <!--                                    <img-->
                        <!--                                        loading="lazy"-->
                        <!--                                        :src="SingleBookScreenshot"-->
                        <!--                                        alt="Library view"-->
                        <!--                                        class="rounded">-->
                        <!--                                </div>-->
                        <!--                                <div-->
                        <!--                                    data-aos="zoom-in"-->
                        <!--                                    data-aos-delay="300"-->
                        <!--                                    class="pointer-events-none absolute -bottom-6 -left-6 hidden w-52 -rotate-6 rounded-lg border border-sidebar-border/80 bg-white p-1.5 shadow md:block"-->
                        <!--                                >-->
                        <!--                                    <img-->
                        <!--                                        loading="lazy"-->
                        <!--                                        :src="PlanToReadScreenshot"-->
                        <!--                                        alt="Book detail page"-->
                        <!--                                        class="rounded">-->
                        <!--                                </div>-->
                    </div>
                    <div class="mt-4 text-center text-sm text-secondary-foreground/50">
                        Product UI previews
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
