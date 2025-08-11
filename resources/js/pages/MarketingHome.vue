<script setup>
import 'aos/dist/aos.css'
import AOS from 'aos'
import Icon from '@/components/Icon.vue'
import AppLogo from '@/components/AppLogo.vue'
import Silk from '@/components/backgrounds/Silk/Silk.vue'
import StarRatingDisplay from '@/components/StarRatingDisplay.vue'
import { onMounted, ref, watch } from 'vue'
import { useMediaQuery } from '@vueuse/core'
import { Link, usePage } from '@inertiajs/vue3'
import { useRoute } from '@/composables/useRoute.js'
import { Button } from '@/components/ui/button/index.js'
import { getInitials } from '@/composables/useInitials.js'
import { useAuthedUser } from '@/composables/useAuthedUser.js'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card/index.js'

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
        title: 'Organize Your Library',
        description: 'Easily categorize books by status: Reading, Completed, Plan to Read.',
        icon: 'LibraryBig'
    },
    {
        title: 'Effortless Scanning',
        description: 'Scan barcodes or search by title/author to instantly add books.',
        icon: 'ScanBarcode'
    },
    {
        title: 'Track Progress',
        description: 'Update statuses and view reading trends.',
        icon: 'ChartLine'
    },
    {
        title: 'Share & Discover',
        description: 'Connect with friends and uncover new recommendations.',
        icon: 'Share2'
    }
]

const howItWorksSteps = [
    {
        title: 'Add Books',
        description: 'Search or scan to import your collection in seconds.',
        icon: 'ScanLine'
    },
    {
        title: 'Track Progress',
        description: 'Update statuses and view reading trends.',
        icon: 'ChartLine'
    },
    {
        title: 'Share & Discover',
        description: 'Connect with friends and uncover new recommendations.',
        icon: 'Share2'
    }
]

const testimonials = [
    {
        name: 'Emma',
        role: 'Avid Reader',
        rating: 5,
        feedback: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod.'
    },
    {
        name: 'Marcus',
        role: 'Non-fiction Fan',
        rating: 5,
        feedback: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod.'
    },
    {
        name: 'Lena',
        role: 'Librarian',
        rating: 4,
        feedback: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod.'
    }
]

const freeFeatures = [
    {
        title: 'Personal Library',
        description: 'Track your reading journey with a personal library that grows with you.',
        icon: 'LibraryBig'
    },
    {
        title: 'Barcode Scanning',
        description: 'Add books instantly by scanning barcodes or searching by title/author.',
        icon: 'ScanBarcode'
    },
    {
        title: 'Reading Stats',
        description: 'Get insights into your reading habits and progress over time.',
        icon: 'ChartLine'
    },
    {
        title: 'Social Sharing',
        description: 'Share your favorite reads and discover new books through friends.',
        icon: 'Share2'
    }
]

const proFeatures = [
    {
        title: 'Unlimited Books',
        description: 'No limits on the number of books you can track.',
        icon: 'LibraryBig'
    },
    {
        title: 'Advanced Analytics',
        description: 'Detailed insights into your reading patterns and preferences.',
        icon: 'ChartLine'
    },
    {
        title: 'Private Notes',
        description: 'Keep personal notes and reviews for each book.',
        icon: 'Note'
    },
    {
        title: 'Priority Support',
        description: 'Get faster responses and dedicated help from our support team.',
        icon: 'Support'
    }
]

const faqs = [
    {
        question: 'Is my data private?',
        answer: 'Yes, your data is stored securely and never shared with third parties.'
    },
    {
        question: 'Can I import from other services?',
        answer: 'Yes, you can import your library from Goodreads, LibraryThing, and more.'
    },
    {
        question: 'What if I need help?',
        answer: 'We offer email support and a community forum for all users.'
    }
]

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
    AOS.init({
        once: true
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
            :class="mobileMenuOpen ? 'pointer-events-auto bg-black/60 backdrop-blur-sm dark:bg-white/20 ' : 'bg-transparent backdrop-blur-none pointer-events-none'"
            class="fixed w-full h-full z-30 left-0 top-14"
            @click="mobileMenuOpen = false" />
        <header
            class="fixed top-0 z-40 w-full rounded-full left-1/2 md:pt-2 -translate-x-1/2 transition-all">
            <div
                :class="[
                    mobileMenuOpen ? 'bg-background md:bg-background' :
                    (hasScrolled ? 'bg-white/75 md:bg-white/75 backdrop-blur-sm shadow-sm' : 'bg-transparent shadow-none ')
                ]"
                class="container md:rounded-xl transition-all mx-auto px-2.5 flex h-14 items-center justify-between">
                <a
                    class="flex items-center gap-2 font-semibold"
                    :href="useRoute('home')">
                    <AppLogo class="flex items-center" />
                </a>
                <nav class="hidden items-center gap-8 text-sm md:flex">
                    <a
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        class="text-foreground hover:text-accent-foreground/75 transition-all text-sm font-medium">
                        {{ link.label }}
                    </a>
                </nav>
                <div class="hidden gap-2 md:flex">
                    <Button as-child>
                        <Link :href="authed ? useRoute('user.books.index') : useRoute('register')">
                            <Icon name="Home" />
                            {{ authed ? 'Home' : 'Get Started Free' }}
                        </Link>
                    </Button>
                </div>
                <Button
                    class="md:hidden"
                    variant="secondary"
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
                :class="mobileMenuOpen ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
                class="absolute top-full overflow-hidden left-0 w-full border-t border-b border-sidebar-border/80 bg-background md:hidden">
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
                            <Icon name="Home" />
                            {{ authed ? 'Home' : 'Get Started Free' }}
                        </Link>
                    </Button>
                </div>
            </div>
        </header>
        <main>
            <section class="relative overflow-hidden">
                <div class="silk-container absolute z-1 opacity-10 inset-0">
                    <Silk
                        :speed="15"
                        :scale="1"
                        color="#fcfbf8"
                        :noise-intensity="1.5"
                        :rotation="0"
                        class="w-full h-full"
                    />
                </div>
                <div class="w-full h-36 bg-gradient-to-b from-transparent to-background absolute bottom-0 left-0 z-20" />
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[hsl(36,40%,98%)] to-[hsl(36,40%,94%)] dark:from-[hsl(0,0%,10%)] dark:to-[hsl(0,0%,6%)]" />
                <div class="relative z-10 container mx-auto grid items-center gap-10 px-4 pt-20 pb-16 sm:pb-28 sm:pt-48 md:grid-cols-2">
                    <div>
                        <h1 class="font-serif text-4xl sm:text-5xl md:text-6xl/16 text-pretty font-medium">
                            Your Reading Life at a Glance
                        </h1>
                        <p class="mt-4 text-lg text-foreground">
                            Discover, track, and share your favorite books with {{ page.props.app.name }}—your personal library companion.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
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
                                    src="https://placehold.co/600x400/EEE/31343C"
                                    :alt="`${page.props.app.name} dashboard showing stats and recent activity`"
                                    class="h-auto w-full rounded-lg"
                                >
                                <div
                                    data-aos="zoom-in"
                                    data-aos-delay="200"
                                    class="pointer-events-none absolute -top-6 -right-6 hidden w-40 rotate-4 rounded-lg border border-sidebar-border/80 bg-white p-1 shadow md:block">
                                    <img
                                        src="https://placehold.co/600x400/EEE/31343C"
                                        alt="Library view"
                                        class="rounded">
                                </div>
                                <div
                                    data-aos="zoom-in"
                                    data-aos-delay="300"
                                    class="pointer-events-none absolute -bottom-6 -left-6 hidden w-40 -rotate-6 rounded-lg border border-sidebar-border/80 bg-white p-1 shadow md:block">
                                    <img
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
                id="benefits"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                            Key Benefits
                        </h2>
                        <p class="mt-2 text-secondary-foreground">
                            Everything you need to love your reading routine.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Card
                            v-for="benefit in keyBenefits"
                            :key="benefit.title"
                            class="bg-white">
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
            <section
                id="showcase"
                class="bg-white py-16 sm:py-20">
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
                        class="flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth px-20 pb-4"
                        aria-label="Product showcase">
                        <div
                            v-for="screenshot in screenshots"
                            :key="screenshot.src"
                            class="single-screenshot shrink-0 basis-full snap-center md:basis-1/2">
                            <div class="relative aspect-[16/9] w-full max-w-3xl overflow-hidden rounded-xl border border-sidebar-border/80 shadow-sm">
                                <div
                                    class="absolute inset-0 h-5 rounded-t-xl"
                                    aria-hidden="true" />
                                <img
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
                            class="bg-white">
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
                        </Card>
                    </div>
                </div>
            </section>
            <section
                id="testimonials"
                class="bg-white">
                <div class="container mx-auto px-4 py-16 sm:py-20">
                    <div>
                        <div class="mb-8 sm:mb-10">
                            <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                                Loved by early readers
                            </h2>
                            <p class="mt-2 text-secondary-foreground">
                                A few words from our beta users.
                            </p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <Card
                                v-for="testimonial in testimonials"
                                :key="testimonial.name">
                                <CardHeader>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-primary">
                                            <span class="text-sm font-semibold">
                                                {{ getInitials(testimonial.name) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-medium">
                                                {{ testimonial.name }}
                                            </div>
                                            <div class="text-sm text-secondary-foreground">
                                                {{ testimonial.role }}
                                            </div>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div
                                        class="mb-2 -mt-4 flex items-center gap-1 text-yellow-600"
                                        aria-label="5 out of 5 stars">
                                        <StarRatingDisplay
                                            :star-width="16"
                                            :rating="testimonial.rating" />
                                    </div>
                                    <p class="text-secondary-foreground">
                                        {{ testimonial.feedback }}
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </section>
            <section
                id="pricing"
                class="container mx-auto max-w-4xl px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                            Plans &amp; Pricing
                        </h2>
                        <p class="mt-2 text-secondary-foreground">
                            Start free, upgrade anytime. Cancel whenever you like.
                        </p>
                    </div>
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Free
                                </CardTitle>
                                <CardDescription>
                                    Basic library management for getting started.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <ul class="space-y-2 text-sm">
                                    <li
                                        v-for="feature in freeFeatures"
                                        :key="feature.title"
                                        class="flex items-start gap-2">
                                        <Icon
                                            name="Check"
                                            class="size-4 mt-0.5 text-primary" />
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
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Pro
                                    <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium text-primary">Most popular</span>
                                </CardTitle>
                                <CardDescription> Unlimited books, advanced analytics, and priority support. </CardDescription>
                                <div class="mt-2 text-3xl font-semibold">
                                    $4.99<span class="text-base font-normal text-secondary-foreground">/mo</span>
                                </div>
                                <div class="text-sm text-secondary-foreground">
                                    Save with annual billing.
                                </div>
                            </CardHeader>
                            <CardContent>
                                <ul class="space-y-2 text-sm text-secondary-foreground">
                                    <li
                                        v-for="feature in proFeatures"
                                        :key="feature.title"
                                        class="flex items-start gap-2">
                                        <Icon
                                            name="Check"
                                            class="size-4 mt-0.5 text-primary" />
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
            </section>
            <section
                id="faq"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="font-serif text-3xl font-semibold tracking-tight sm:text-4xl">
                            Frequently asked questions
                        </h2>
                        <p class="mt-2 text-secondary-foreground">
                            Answers to common questions.
                        </p>
                    </div>

                    <Accordion
                        type="single"
                        class="w-full"
                        collapsible>
                        <AccordionItem
                            v-for="(item, index) in faqs"
                            :key="index"
                            :value="index.toString()">
                            <AccordionTrigger>{{ item.question }}</AccordionTrigger>
                            <AccordionContent>
                                {{ item.answer }}
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
            </section>
        </main>
        <footer class="mt-16 border-t border-sidebar-border/80 bg-background">
            <div class="container mx-auto grid gap-10 px-4 py-10 md:grid-cols-3">
                <div>
                    <div class="font-serif text-xl">
                        {{ page.props.app.name }}
                    </div>
                    <p class="mt-2 max-w-sm text-sm text-secondary-foreground">
                        Track your reading, organize your library, and share what you love.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <div class="mb-2 font-medium">
                            Quick Links
                        </div>
                        <ul class="space-y-2 text-secondary-foreground">
                            <li
                                v-for="link in links"
                                :key="link.href">
                                <a :href="link.href">
                                    {{ link.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-2 font-medium">
                            Legal
                        </div>
                        <ul class="space-y-2 text-secondary-foreground">
                            <li><a :href="useRoute('privacy-policy')">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="container px-4 mx-auto">
                <div class="text-xs text-secondary-foreground">
                    <div>© {{ new Date().getFullYear() }} {{ page.props.app.name }} by SpacemanCodes LTD. All rights reserved.</div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
html, body, *{
    scroll-behavior: smooth !important;
}
</style>
