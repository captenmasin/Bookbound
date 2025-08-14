<script setup lang="ts">
import 'aos/dist/aos.css'
import AOS from 'aos'
import Icon from '@/components/Icon.vue'
import AppLogo from '@/components/AppLogo.vue'
import HomeHero from '@/components/marketing/HomeHero.vue'
import ScanScreenshot from '~/images/marketing/scan-screenshot.webp'
import FilterScreenshot from '~/images/marketing/filter-screenshot.webp'
import ScanScreenshotDark from '~/images/marketing/scan-screenshot-dark.webp'
import FilterScreenshotDark from '~/images/marketing/filter-screenshot-dark.webp'
import SingleBookScreenshot from '~/images/marketing/single-book-screenshot.webp'
import SliderSearchScreenshot from '~/images/marketing/slider-search-screenshot.webp'
import SliderLibraryScreenshot from '~/images/marketing/slider-library-screenshot.webp'
import SingleBookScreenshotDark from '~/images/marketing/single-book-screenshot-dark.webp'
import SliderSearchScreenshotDark from '~/images/marketing/slider-search-screenshot-dark.webp'
import SliderSingleBookScreenshot from '~/images/marketing/slider-single-book-screenshot.webp'
import SliderLibraryScreenshotDark from '~/images/marketing/slider-library-screenshot-dark.webp'
import SliderLibraryShelfScreenshot from '~/images/marketing/slider-library-shelf-screenshot.webp'
import SliderSingleBookScreenshotDark from '~/images/marketing/slider-single-book-screenshot-dark.webp'
import SliderLibraryFilteredScreenshot from '~/images/marketing/slider-library-filtered-screenshot.webp'
import SliderLibraryShelfScreenshotDark from '~/images/marketing/slider-library-shelf-screenshot-dark.webp'
import SliderLibraryFilteredScreenshotDark from '~/images/marketing/slider-library-filtered-screenshot-dark.webp'
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

const links = [
    {
        href: '#how-it-works',
        label: 'How it works'
    },
    {
        href: '#showcase',
        label: 'Showcase'
    },
    {
        href: '#features',
        label: 'Features'
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

const footerLinks = [
    ...links,
    {
        href: useRoute('login'),
        label: 'Login'
    },
    {
        href: useRoute('register'),
        label: 'Get Started Free'
    }
]

const howItWorksSteps = [
    {
        title: 'Add Books',
        description: 'Search or scan barcodes to build your library fast.',
        icon: 'ScanBarcode',
        image: ScanScreenshot,
        darkImage: ScanScreenshotDark
    },
    {
        title: 'Sort & Filter',
        description: 'Slice your collection any way you want: by author, subject, colour, or status.',
        icon: 'ArrowUpNarrowWide',
        image: FilterScreenshot,
        darkImage: FilterScreenshotDark
    },
    {
        title: 'Explore & Rate',
        description: 'Open any book to see its details, rate, add notes, and leave reviews.',
        icon: 'BookOpen',
        image: SingleBookScreenshot,
        darkImage: SingleBookScreenshotDark
    }
]

const screenshots = [
    // {
    //     src: SliderHomeScreenshot,
    //     alt: 'Home dashboard with stats and activity'
    // },
    {
        src: SliderLibraryScreenshot,
        darkSrc: SliderLibraryScreenshotDark,
        alt: 'Library view with grid of books'
    },
    {
        src: SliderLibraryFilteredScreenshot,
        darkSrc: SliderLibraryFilteredScreenshotDark,
        alt: 'Filtered library view '
    },
    {
        src: SliderLibraryShelfScreenshot,
        darkSrc: SliderLibraryShelfScreenshotDark,
        alt: 'Alternative library view with shelf layout'
    },
    {
        src: SliderSearchScreenshot,
        darkSrc: SliderSearchScreenshotDark,
        alt: 'Book search and filter interface'
    },
    {
        src: SliderSingleBookScreenshot,
        darkSrc: SliderSingleBookScreenshotDark,
        alt: 'Single book details with reviews and notes'
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

const features = ref([
    {
        title: 'Up to ' + props.freeLimits?.max_books + ' Books',
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

const faqs = [
    {
        question: 'How do I add new books to my library',
        answer: 'Books can be added by searching for them or by scanning their barcodes using your device’s camera.'
    },
    {
        question: 'Can I track the status of my books?',
        answer: 'Yes, you can mark books as read, currently reading, or want to read.'
    },
    {
        question: 'Is there a limit to how many books I can add?',
        answer: `The free plan allows you to add up to ${props.freeLimits?.max_books} books. Upgrade to Pro for unlimited books.`
    },
    {
        question: 'Are reviews and ratings public?',
        answer: 'Yes, your reviews will be displayed publicly, but Pro users can write private notes that only they can see.'
    },
    {
        question: 'What if I forget my password?',
        answer: 'You can reset your password using the “Forgot Password” link on the login page.'
    },
    {
        question: 'Is my data secure?',
        answer: 'Yes, we take data security seriously and use industry-standard encryption to protect your information.'
    }
]

function moveSliderRight () {
    const slider = document.getElementById('product-screenshots')
    const singleSlide = document.querySelector('.single-screenshot')
    if (slider) {
        slider.scrollBy({
            left: singleSlide?.clientWidth,
            behavior: 'smooth'
        })
    }
}

function moveSliderLeft () {
    const slider = document.getElementById('product-screenshots')
    const singleSlide = document.querySelector('.single-screenshot')
    if (slider) {
        slider.scrollBy({
            left: singleSlide ? -singleSlide.clientWidth : 0,

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
        <header class="fixed top-0 md:px-12 left-1/2 z-40 w-full -translate-x-1/2 rounded-full transition-all md:pt-2">
            <div
                :class="[
                    mobileMenuOpen
                        ? 'bg-background md:bg-background'
                        : hasScrolled
                            ? 'bg-white/75 dark:bg-black/75 shadow-sm backdrop-blur-sm md:bg-white/75 md:dark:bg-black/75'
                            : 'bg-transparent shadow-none',
                ]"
                class="mx-auto flex h-14 items-center justify-between px-2.5 transition-all md:rounded-xl"
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
            <!--            <HomeHeroStacked />-->
            <HomeHero />
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
                            class="overflow-hidden bg-white dark:bg-background pb-0">
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
                                width="400"
                                height="330"
                                :alt="`${step.title} screenshot`"
                                class="mt-auto w-full dark:hidden">
                            <img
                                loading="lazy"
                                :src="step.darkImage"
                                width="400"
                                height="330"
                                :alt="`${step.title} screenshot`"
                                class="mt-auto w-full hidden dark:block">
                        </Card>
                    </div>
                </div>
            </section>
            <section
                id="showcase"
                class="bg-white dark:bg-neutral-900 py-16 sm:py-28">
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
                                    width="630"
                                    height="370"
                                    class="absolute inset-0 w-full object-cover dark:hidden">
                                <img
                                    loading="lazy"
                                    :src="screenshot.darkSrc"
                                    :alt="screenshot.alt"
                                    width="630"
                                    height="370"
                                    class="absolute inset-0 w-full object-cover hidden dark:block">
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
                            <span class="sr-only">Previous slide</span>
                            <Icon
                                name="ChevronLeft"
                                class="h-5 w-5" />
                        </Button>
                        <Button
                            size="icon"
                            variant="white"
                            @click="moveSliderRight">
                            <span class="sr-only">Next slide</span>
                            <Icon
                                name="ChevronRight"
                                class="h-5 w-5" />
                        </Button>
                    </div>
                </div>
            </section>

            <section
                id="features"
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
                            class="relative bg-white dark:bg-background">
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
                class="bg-white dark:bg-neutral-900">
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
                                                class="mt-0.5 size-5"
                                            />
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
                            <Card class="border-2 border-primary">
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
                                            class="flex items-start gap-2"
                                        >
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
                <div class="flex flex-col md:flex-row">
                    <div class="mb-2 w-full md:w-1/2 sm:mb-10">
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
                                v-for="link in footerLinks"
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
