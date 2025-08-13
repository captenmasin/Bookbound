<script setup lang="ts">
import 'aos/dist/aos.css'
import AOS from 'aos'
import Icon from '@/components/Icon.vue'
import AppLogo from '@/components/AppLogo.vue'
import Silk from '@/components/backgrounds/Silk/Silk.vue'
import SplitText from '@/components/textanimations/SplitText/SplitText.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute.js'
import { useCloned, useMediaQuery } from '@vueuse/core'
import { Button } from '@/components/ui/button/index.js'
import { useAuthedUser } from '@/composables/useAuthedUser.js'
import { nextTick, onMounted, PropType, ref, watch } from 'vue'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card/index.js'

const props = defineProps({
    price: {
        type: String,
        default: ''
    },
    interval: {
        type: String,
        default: ''
    },
    freeLimits: {
        type: Object as PropType<{ max_books: number }>
    }
})

const page = usePage()
const mobileMenuOpen = ref(false)
const hasScrolled = ref(false)
const isDesktop = useMediaQuery('(min-width: 768px)')

const { authed } = useAuthedUser()

const screenshots = [
    {
        src: 'https://placehold.co/600x400/EEE/31343C',
        alt: 'Home dashboard with stats and activity'
    },
    {
        src: 'https://placehold.co/600x400/EEE/31343C',
        alt: 'Library view with list of books'
    },
    {
        src: 'https://placehold.co/600x400/EEE/31343C',
        alt: 'Add Book search results and filters'
    },
    {
        src: 'https://placehold.co/600x400/EEE/31343C',
        alt: 'Book detail page with notes and reviews'
    },
    {
        src: 'https://placehold.co/600x400/EEE/31343C',
        alt: 'Barcode scanning interface'
    }
]

const links = [
    {
        href: '#benefits',
        label: 'Features'
    },
    {
        href: '#showcase',
        label: 'Showcase'
    },
    {
        href: '#how-it-works',
        label: 'How it works'
    },
    {
        href: '#pricing',
        label: 'Pricing'
    },
    {
        href: '#faq',
        label: 'FAQ'
    }
]

const keyBenefits = [
    {
        title: 'Organize Without Overthinking',
        description: 'Sort by author, title, or colour. No more messy shelves.',
        icon: 'LibraryBig',
        pro: false
    },
    {
        title: 'Scan, Don’t Type',
        description: 'Point your camera at a barcode and boom, it’s in your library.',
        icon: 'ScanBarcode',
        pro: false
    },
    {
        title: 'Review What You’ve Read',
        description: 'Share your thoughts… even if it’s just “meh.”',
        icon: 'Star',
        pro: false
    },
    {
        title: 'Know Your Trends',
        description: 'See your top subjects and authors – find out what you really love.',
        icon: 'ChartLine',
        pro: false
    }
    // {
    //     title: 'TODO',
    //     description: 'TODO',
    //     icon: 'ChartLine',
    //     pro: true
    // }
]

const howItWorksSteps = [
    {
        title: 'Add Books',
        description: 'Search or scan barcodes to build your library fast.',
        icon: 'ScanBarcode',
        image: 'https://placehold.co/600x400/EEE/31343C'
    },
    {
        title: 'Sort & Filter',
        description: 'Slice your collection any way you want: by author, subject, colour, or status.',
        icon: 'ArrowUpNarrowWide',
        image: 'https://placehold.co/600x400/EEE/31343C'
    },
    {
        title: 'Explore Your Books',
        description: ' Open any book to see its description, your notes, your review, and reviews from other readers.',
        icon: 'BookOpen',
        image: 'https://placehold.co/600x400/EEE/31343C'
    }
]

const testimonials = [
    {
        name: 'Future Me',
        rating: 5,
        feedback: 'Finally, I know which books I’ve lent out… and to who!'
    },
    {
        name: 'Probably You',
        rating: 5,
        feedback: 'I’m blaming this app for my overflowing TBR list.'
    },
    {
        name: 'Someone Clever',
        rating: 5,
        feedback: 'It’s like a personal librarian, but it doesn’t shush me.'
    }
]

const features = ref([
    {
        title: 'Up to ' + props.freeLimits.max_books + ' Books',
        enabled: true,
        bold: false
    },
    {
        title: 'Scan Barcodes',
        enabled: true,
        bold: false
    },
    {
        title: 'Search and Filter your Library',
        enabled: true,
        bold: false
    },
    {
        title: 'Preview Book Details',
        enabled: true,
        bold: false
    },
    {
        title: 'Track Book Status',
        enabled: true,
        bold: false
    },
    {
        title: 'Review and Rate Books',
        enabled: true,
        bold: false
    },
    {
        title: 'Private Notes',
        enabled: false,
        bold: false
    },
    {
        title: 'Custom Book Covers',
        enabled: false,
        bold: false
    }
])

const { cloned } = useCloned(features)
const proFeatures = cloned

proFeatures.value[0].title = 'Unlimited Books'
proFeatures.value[0].bold = true

let faqs = [
    {
        question: 'Question 1',
        answer: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
    }
]

faqs = [...faqs, ...faqs, ...faqs, ...faqs, ...faqs, ...faqs]

function moveSliderRight () {
    const slider = document.getElementById('product-screenshots')
    const singleSlide = document.querySelector('.single-screenshot')
    if (slider) {
        slider.scrollBy({
            left: singleSlide.clientWidth,
            behavior: 'smooth'
        })
    }
}

function moveSliderLeft () {
    const slider = document.getElementById('product-screenshots')
    const singleSlide = document.querySelector('.single-screenshot')
    if (slider) {
        slider.scrollBy({
            left: -singleSlide.clientWidth,

            behavior: 'smooth'
        })
    }
}

onMounted(() => {
    nextTick(() => {
        AOS.init({
            once: true
        })
    })

    let scrollLimit = 20
    if (isDesktop.value) {
        scrollLimit = 100
    }

    hasScrolled.value = window.scrollY > scrollLimit

    window.addEventListener('scroll', () => {
        hasScrolled.value = window.scrollY > scrollLimit
    })
})

watch(mobileMenuOpen, (newValue) => {
    if (newValue) {
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = ''
    }
})
</script>

<template>
    <div class="bg-background">
        <div
            :class="
                mobileMenuOpen
                    ? 'pointer-events-auto bg-black/60 backdrop-blur-sm dark:bg-white/20'
                    : 'pointer-events-none bg-transparent backdrop-blur-none'
            "
            class="fixed top-14 left-0 z-30 h-full w-full"
            @click="mobileMenuOpen = false"
        />
        <header class="fixed top-0 left-1/2 z-40 w-full -translate-x-1/2 rounded-full transition-all md:pt-2">
            <div
                :class="[
                    mobileMenuOpen
                        ? 'bg-background md:bg-background'
                        : hasScrolled
                            ? 'bg-white/75 shadow-sm backdrop-blur-sm md:bg-white/75'
                            : 'bg-transparent shadow-none',
                ]"
                class="container mx-auto flex h-14 items-center justify-between px-2.5 transition-all md:rounded-xl"
            >
                <a
                    class="flex items-center gap-2 font-semibold"
                    :href="useRoute('dashboard')">
                    <AppLogo
                        logo-border-color="border-primary/20"
                        class="flex items-center" />
                </a>
                <nav class="hidden items-center gap-8 text-sm md:flex">
                    <a
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        class="text-sm font-medium text-foreground transition-all hover:text-accent-foreground/75"
                    >
                        {{ link.label }}
                    </a>
                </nav>
                <div class="hidden gap-2 md:flex">
                    <Button as-child>
                        <Link :href="authed ? useRoute('user.books.index') : useRoute('register')">
                            <Icon
                                v-if="authed"
                                name="LibraryBig" />
                            {{ authed ? 'Your Library' : 'Get Started Free' }}
                        </Link>
                    </Button>
                </div>
                <Button
                    class="md:hidden"
                    variant="outline"
                    size="icon"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <span class="sr-only">Toggle navigation</span>
                    <Icon :name="mobileMenuOpen ? 'X' : 'Menu'" />
                </Button>
            </div>
            <div
                id="mobile-menu"
                :class="mobileMenuOpen ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'"
                class="absolute top-full left-0 w-full overflow-hidden border-t border-b border-sidebar-border/80 bg-background md:hidden"
            >
                <div class="container mx-auto flex flex-col gap-2 px-4 pt-4 pb-6">
                    <a
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        class="block py-1 text-foreground hover:text-accent-foreground/75"
                        @click="mobileMenuOpen = false"
                    >
                        {{ link.label }}
                    </a>
                    <Button as-child>
                        <Link :href="authed ? useRoute('user.books.index') : useRoute('register')">
                            <Icon
                                v-if="authed"
                                name="LibraryBig" />
                            {{ authed ? 'Your Library' : 'Get Started Free' }}
                        </Link>
                    </Button>
                </div>
            </div>
        </header>
        <main>
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
                <div class="relative z-10 container mx-auto grid items-center gap-10 px-4 pt-20 pb-16 sm:pt-48 sm:pb-28 md:grid-cols-2">
                    <div class="md:pr-24">
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
                    <div class="relative">
                        <div class="mx-auto max-w-2xl">
                            <div
                                data-aos="zoom-in"
                                class="relative -rotate-1 rounded-xl border border-sidebar-border/80 bg-white p-2 shadow-sm">
                                <img
                                    loading="lazy"
                                    src="https://placehold.co/600x400/EEE/31343C"
                                    :alt="`${page.props.app.name} dashboard showing stats and recent activity`"
                                    class="h-auto w-full rounded-lg"
                                >
                                <div
                                    data-aos="zoom-in"
                                    data-aos-delay="200"
                                    class="pointer-events-none absolute -top-6 -right-6 hidden w-40 rotate-4 rounded-lg border border-sidebar-border/80 bg-white p-1 shadow md:block"
                                >
                                    <img
                                        loading="lazy"
                                        src="https://placehold.co/600x400/EEE/31343C"
                                        alt="Library view"
                                        class="rounded">
                                </div>
                                <div
                                    data-aos="zoom-in"
                                    data-aos-delay="300"
                                    class="pointer-events-none absolute -bottom-6 -left-6 hidden w-40 -rotate-6 rounded-lg border border-sidebar-border/80 bg-white p-1 shadow md:block"
                                >
                                    <img
                                        loading="lazy"
                                        src="https://placehold.co/600x400/EEE/31343C"
                                        alt="Book detail page"
                                        class="rounded">
                                </div>
                            </div>
                            <div class="mt-4 text-center text-sm text-secondary-foreground/50">
                                Product UI previews
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section
                id="how-it-works"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                            How it works
                        </h2>
                        <p class="mt-2 text-secondary-foreground">
                            Three simple steps to level up your reading.
                        </p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <Card
                            v-for="step in howItWorksSteps"
                            :key="step.title"
                            class="bg-white pb-0 overflow-hidden">
                            <CardHeader>
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-secondary text-primary">
                                    <Icon
                                        :name="step.icon"
                                        class="h-5 w-5" />
                                </div>
                                <CardTitle class="mt-2">
                                    {{ step.title }}
                                </CardTitle>
                                <CardDescription class="text-pretty">
                                    {{ step.description }}
                                </CardDescription>
                            </CardHeader>
                            <img
                                loading="lazy"
                                :src="step.image"
                                :alt="`${step.title} screenshot`"
                                class="mt-auto w-full">
                        </Card>
                    </div>
                </div>
            </section>
            <section
                id="showcase"
                class="bg-white py-16 sm:py-28">
                <div class="container mx-auto px-4">
                    <div class="mb-8 sm:mb-10">
                        <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                            See {{ page.props.app.name }} in action
                        </h2>
                        <p class="mt-2 text-secondary-foreground">
                            Highlights from the dashboard, library, and book details.
                        </p>
                    </div>
                </div>
                <div class="relative">
                    <div
                        id="product-screenshots"
                        class="flex snap-x snap-mandatory gap-10 overflow-x-auto scroll-smooth px-4 pb-4 md:px-28"
                        aria-label="Product showcase"
                    >
                        <div
                            v-for="screenshot in screenshots"
                            :key="screenshot.src"
                            class="single-screenshot shrink-0 basis-10/12 snap-center md:basis-1/2"
                        >
                            <div class="relative aspect-[16/9] w-full max-w-3xl overflow-hidden rounded-xl border border-sidebar-border/80 shadow-sm">
                                <div
                                    class="absolute inset-0 h-5 rounded-t-xl"
                                    aria-hidden="true" />
                                <img
                                    loading="lazy"
                                    :src="screenshot.src"
                                    :alt="screenshot.alt"
                                    class="absolute inset-0 h-full w-full object-cover">
                            </div>
                            <p class="mt-3 text-center text-sm text-secondary-foreground">
                                {{ screenshot.alt }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute inset-y-1/2 left-0 z-50 hidden w-full -translate-y-12 justify-between px-4 md:flex">
                        <Button
                            size="icon"
                            variant="white"
                            @click="moveSliderLeft">
                            <Icon
                                name="ChevronLeft"
                                class="h-5 w-5" />
                        </Button>
                        <Button
                            size="icon"
                            variant="white"
                            @click="moveSliderRight">
                            <Icon
                                name="ChevronRight"
                                class="h-5 w-5" />
                        </Button>
                    </div>
                </div>
            </section>

            <section
                id="benefits"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                            Why You’ll Love It
                        </h2>
                        <p class="mt-2 text-secondary-foreground">
                            Everything you need to love your reading routine.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Card
                            v-for="benefit in keyBenefits"
                            :key="benefit.title"
                            class="relative bg-white">
                            <div
                                v-if="benefit.pro"
                                class="absolute top-3 right-3 rounded-full bg-primary px-2 py-1 text-xs font-medium text-white">
                                Pro Feature
                            </div>
                            <CardHeader>
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-secondary text-primary">
                                    <Icon
                                        :name="benefit.icon"
                                        class="h-5 w-5" />
                                </div>
                                <CardTitle class="mt-2">
                                    {{ benefit.title }}
                                </CardTitle>
                                <CardDescription class="text-pretty">
                                    {{ benefit.description }}
                                </CardDescription>
                            </CardHeader>
                        </Card>
                    </div>
                </div>
            </section>
            <!--                        <section-->
            <!--                            id="testimonials"-->
            <!--                            class="bg-white">-->
            <!--                <div class="container mx-auto px-4 py-16 sm:py-20">-->
            <!--                    <div>-->
            <!--                        <div class="mb-8 sm:mb-10">-->
            <!--                            <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">-->
            <!--                                Loved by early readers-->
            <!--                                <Icon-->
            <!--                                    name="Heart"-->
            <!--                                    class="inline-block animate-beat size-10 -mt-6 rotate-24 fill-current text-red-500" />-->
            <!--                            </h2>-->
            <!--                            <p class="mt-2 text-secondary-foreground">-->
            <!--                                A few words from our beta users.-->
            <!--                            </p>-->
            <!--                        </div>-->
            <!--                        <div class="grid gap-4 md:grid-cols-3">-->
            <!--                            <Card-->
            <!--                                v-for="testimonial in testimonials"-->
            <!--                                :key="testimonial.name">-->
            <!--                                <CardHeader>-->
            <!--                                    <div class="flex items-center gap-3">-->
            <!--                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-primary">-->
            <!--                                            <span class="text-sm font-semibold">-->
            <!--                                                {{ getInitials(testimonial.name) }}-->
            <!--                                            </span>-->
            <!--                                        </div>-->
            <!--                                        <div>-->
            <!--                                            <div class="font-medium">-->
            <!--                                                {{ testimonial.name }}-->
            <!--                                            </div>-->
            <!--                                        </div>-->
            <!--                                    </div>-->
            <!--                                </CardHeader>-->
            <!--                                <CardContent>-->
            <!--                                    <div-->
            <!--                                        class="mb-2 -mt-4 flex items-center gap-1 text-yellow-600"-->
            <!--                                        aria-label="5 out of 5 stars">-->
            <!--                                        <StarRatingDisplay-->
            <!--                                            :star-width="16"-->
            <!--                                            :rating="testimonial.rating" />-->
            <!--                                    </div>-->
            <!--                                    <p class="text-secondary-foreground">-->
            <!--                                        {{ testimonial.feedback }}-->
            <!--                                    </p>-->
            <!--                                </CardContent>-->
            <!--                            </Card>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </section>-->
            <section
                id="pricing"
                class="bg-white">
                <div class="container mx-auto px-4 py-16 sm:py-20">
                    <div>
                        <div class="mb-8 sm:mb-10">
                            <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                                Plans &amp; Pricing
                            </h2>
                            <p class="mt-2 text-secondary-foreground">
                                Start free, upgrade anytime. Cancel whenever you like.
                            </p>
                        </div>
                        <div class="mx-auto grid max-w-4xl gap-6 md:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle class="font-serif text-xl font-semibold">
                                        Starter
                                    </CardTitle>
                                    <div class="-mt-1 text-3xl font-medium text-primary">
                                        Free<span class="text-base font-normal text-secondary-foreground"> / forever</span>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <ul class="-mt-2 space-y-3 text-secondary-foreground">
                                        <li
                                            v-for="feature in features"
                                            :key="feature.title"
                                            :class="feature.enabled ? '' : 'text-secondary-foreground line-through opacity-50'"
                                            class="flex items-start gap-2"
                                        >
                                            <Icon
                                                :name="feature.enabled ? 'Check' : 'X'"
                                                :class="feature.enabled ? 'text-primary' : ''"
                                                class="mt-0.5 size-5" />
                                            {{ feature.title }}
                                        </li>
                                    </ul>
                                </CardContent>
                                <CardFooter class="mt-auto">
                                    <Button
                                        class="w-full"
                                        as-child>
                                        <Link :href="useRoute('register')">
                                            Start Free
                                        </Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                            <Card class="border-primary border-2">
                                <CardHeader>
                                    <CardTitle class="font-serif text-xl font-semibold">
                                        Pro
                                    </CardTitle>
                                    <div class="-mt-1 text-3xl font-medium text-primary">
                                        {{ price }}<span class="text-base font-normal text-secondary-foreground"> / {{ interval }}</span>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <ul class="-mt-2 space-y-3 text-secondary-foreground">
                                        <li
                                            v-for="feature in proFeatures"
                                            :key="feature.title"
                                            :class="feature.bold ? 'font-bold' : ''"
                                            class="flex items-start gap-2">
                                            <Icon
                                                name="Check"
                                                class="mt-0.5 size-5 text-primary" />
                                            {{ feature.title }}
                                        </li>
                                    </ul>
                                </CardContent>
                                <CardFooter class="mt-auto">
                                    <Button
                                        class="w-full"
                                        as-child>
                                        <Link :href="useRoute('register', { plan: 'pro' })">
                                            Upgrade to Pro
                                        </Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                        </div>
                    </div>
                </div>
            </section>
            <section
                id="faq"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div class="flex">
                    <div class="mb-8 sm:mb-10 w-1/2">
                        <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                            Frequently asked questions
                        </h2>
                        <p class="mt-2 text-secondary-foreground">
                            Answers to common questions.
                        </p>
                    </div>

                    <div class="flex flex-1">
                        <Accordion
                            type="single"
                            class="w-full"
                            collapsible
                            :default-value="'0'">
                            <AccordionItem
                                v-for="(item, index) in faqs"
                                :key="index"
                                :value="index.toString()">
                                <AccordionTrigger class="cursor-pointer">
                                    {{ item.question }}
                                </AccordionTrigger>
                                <AccordionContent>
                                    {{ item.answer }}
                                </AccordionContent>
                            </AccordionItem>
                        </Accordion>
                    </div>
                </div>
            </section>
        </main>
        <footer class="mt-16 border-t border-sidebar-border/80 bg-background pb-4">
            <div class="container mx-auto grid gap-10 px-4 py-10 md:grid-cols-2">
                <div>
                    <a
                        class="flex items-center gap-2 font-semibold"
                        :href="useRoute('dashboard')">
                        <AppLogo
                            logo-border-color="border-primary/20"
                            class="flex items-center" />
                    </a>
                    <p class="mt-3 max-w-sm text-sm text-secondary-foreground">
                        Track your reading, organize your library, and share what you love.
                    </p>
                </div>
                <div class="grid w-full grid-cols-6 gap-6 text-sm">
                    <div class="col-span-4">
                        <div class="mb-2 font-medium">
                            Quick Links
                        </div>
                        <ul class="columns-2 space-y-2 text-secondary-foreground">
                            <li
                                v-for="link in links"
                                :key="link.href">
                                <a :href="link.href">
                                    {{ link.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-span-2">
                        <div class="mb-2 font-medium">
                            Legal
                        </div>
                        <ul class="space-y-2 text-secondary-foreground">
                            <li><a :href="useRoute('privacy-policy')">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="container mx-auto px-4">
                <div class="text-xs text-secondary-foreground">
                    <div>© {{ new Date().getFullYear() }} {{ page.props.app.name }} by SpacemanCodes LTD. All rights reserved.</div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
html,
body,
* {
    scroll-behavior: smooth !important;
}
</style>
