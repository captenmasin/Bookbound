<script setup>

</script>

<template>
    <div class="min-h-svh bg-[hsl(36,40%,97%)] text-stone-900">
        <header class="sticky top-0 z-40 w-full border-b border-stone-200 bg-[hsl(36,40%,97%)]/80 backdrop-blur">
            <div class="container mx-auto flex h-16 items-center justify-between px-4">
                <a
                    class="flex items-center gap-2 font-semibold text-stone-900"
                    href="#"><span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-orange-700 text-white"><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-library-big h-5 w-5"><rect
                            width="8"
                            height="18"
                            x="3"
                            y="3"
                            rx="1" /><path d="M7 3v18" /><path d="M20.4 18.9c.2.5-.1 1.1-.6 1.3l-1.9.7c-.5.2-1.1-.1-1.3-.6L11.1 5.1c-.2-.5.1-1.1.6-1.3l1.9-.7c.5-.2 1.1.1 1.3.6Z" /></svg><span class="sr-only">Devbound logo</span></span><span class="font-serif text-xl">Devbound</span></a><nav class="hidden items-center gap-6 text-sm text-stone-700 md:flex">
                                <a
                                    href="#benefits"
                                    class="hover:text-stone-900">Features</a><a
                                        href="#showcase"
                                        class="hover:text-stone-900">Showcase</a><a
                                            href="#how-it-works"
                                            class="hover:text-stone-900">How it works</a><a
                                                href="#pricing"
                                                class="hover:text-stone-900">Pricing</a><a
                                                    href="#faq"
                                                    class="hover:text-stone-900">FAQ</a>
                            </nav><div class="hidden gap-2 md:flex">
                    <a
                        href="#showcase"
                        data-slot="button"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 h-9 px-4 py-2 has-[>svg]:px-3">See It in Action</a><a
                            href="#pricing"
                            data-slot="button"
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive shadow-xs h-9 px-4 py-2 has-[>svg]:px-3 bg-orange-700 text-white hover:bg-orange-800">Get Started Free</a>
                </div><button
                    class="md:hidden inline-flex h-9 w-9 items-center justify-center rounded-md border border-stone-300"
                    aria-expanded="false"
                    aria-controls="mobile-menu">
                    <span class="sr-only">Toggle navigation</span><div class="space-y-1.5">
                        <span class="block h-0.5 w-5 bg-stone-700" /><span class="block h-0.5 w-5 bg-stone-700" /><span class="block h-0.5 w-5 bg-stone-700" />
                    </div>
                </button>
            </div><div
                id="mobile-menu"
                class="md:hidden hidden border-t border-stone-200 bg-[hsl(36,40%,97%)]">
                <div class="container mx-auto flex flex-col gap-2 px-4 py-4 text-stone-800">
                    <a href="#benefits">Features</a><a href="#showcase">Showcase</a><a href="#how-it-works">How it works</a><a href="#pricing">Pricing</a><a href="#faq">FAQ</a><div class="mt-2 flex gap-2">
                        <a
                            href="#showcase"
                            data-slot="button"
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50 h-9 px-4 py-2 has-[>svg]:px-3">See It in Action</a><a
                                href="#pricing"
                                data-slot="button"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive shadow-xs h-9 px-4 py-2 has-[>svg]:px-3 bg-orange-700 text-white hover:bg-orange-800">Get Started Free</a>
                    </div>
                </div>
            </div>
        </header><main>
            <section class="relative overflow-hidden">
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[hsl(36,40%,98%)] to-[hsl(36,40%,94%)]" /><div class="relative container mx-auto grid items-center gap-10 px-4 py-16 sm:py-24 md:grid-cols-2">
                        <div>
                            <h1 class="font-serif text-4xl leading-tight sm:text-5xl md:text-6xl">
                                Turn Your Reading Habit into a Journey
                            </h1><p class="mt-4 text-lg text-stone-700">
                                Discover, track, and share your favorite books with Devbound—your personal library companion.
                            </p><div class="mt-8 flex flex-wrap gap-3">
                                <a
                                    href="#pricing"
                                    data-slot="button"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive shadow-xs h-10 rounded-md px-6 has-[>svg]:px-4 bg-orange-700 text-white hover:bg-orange-800">Get Started Free</a><a
                                        href="#showcase"
                                        data-slot="button"
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 h-10 rounded-md px-6 has-[>svg]:px-4">See It in Action</a>
                            </div>
                        </div><div class="relative">
                            <div class="mx-auto max-w-xl">
                                <div class="relative -rotate-1 rounded-xl border border-stone-200 bg-white p-2 shadow-sm">
                                    <img
                                        src="/images/home.png"
                                        alt="Devbound dashboard showing stats and recent activity"
                                        class="h-auto w-full rounded-lg"><div class="pointer-events-none absolute -right-6 -top-6 hidden w-32 rotate-3 rounded-xl border border-stone-200 bg-white p-1 shadow md:block">
                                            <img
                                                src="/images/library.png"
                                                alt="Library view"
                                                class="rounded-lg">
                                        </div><div class="pointer-events-none absolute -bottom-6 -left-6 hidden w-32 -rotate-6 rounded-xl border border-stone-200 bg-white p-1 shadow md:block">
                                        <img
                                            src="/images/book.png"
                                            alt="Book detail page"
                                            class="rounded-lg">
                                    </div>
                                </div><div class="mt-4 text-center text-sm text-stone-500">
                                    Product UI previews
                                </div>
                            </div>
                        </div>
                    </div>
            </section><section
                id="benefits"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="text-3xl sm:text-4xl font-serif font-semibold tracking-tight">
                            Key Benefits
                        </h2><p class="mt-2 text-stone-600">
                            Everything you need to love your reading routine.
                        </p>
                    </div><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm border-stone-200 bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 text-orange-700">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-library-big h-5 w-5"><rect
                                            width="8"
                                            height="18"
                                            x="3"
                                            y="3"
                                            rx="1" /><path d="M7 3v18" /><path d="M20.4 18.9c.2.5-.1 1.1-.6 1.3l-1.9.7c-.5.2-1.1-.1-1.3-.6L11.1 5.1c-.2-.5.1-1.1.6-1.3l1.9-.7c.5-.2 1.1.1 1.3.6Z" /></svg>
                                </div><div
                                    data-slot="card-title"
                                    class="font-semibold mt-3 text-lg">
                                    Organize Your Library
                                </div>
                            </div><div
                                data-slot="card-content"
                                class="px-6 text-stone-600">
                                Easily categorize books by status: Reading, Completed, Plan to Read.
                            </div>
                        </div><div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm border-stone-200 bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 text-orange-700">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-barcode h-5 w-5"><path d="M3 5v14" /><path d="M8 5v14" /><path d="M12 5v14" /><path d="M17 5v14" /><path d="M21 5v14" /></svg>
                                </div><div
                                    data-slot="card-title"
                                    class="font-semibold mt-3 text-lg">
                                    Effortless Scanning
                                </div>
                            </div><div
                                data-slot="card-content"
                                class="px-6 text-stone-600">
                                Scan barcodes or search by title/author to instantly add books.
                            </div>
                        </div><div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm border-stone-200 bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 text-orange-700">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-chart-pie h-5 w-5"><path d="M21 12c.552 0 1.005-.449.95-.998a10 10 0 0 0-8.953-8.951c-.55-.055-.998.398-.998.95v8a1 1 0 0 0 1 1z" /><path d="M21.21 15.89A10 10 0 1 1 8 2.83" /></svg>
                                </div><div
                                    data-slot="card-title"
                                    class="font-semibold mt-3 text-lg">
                                    Insightful Statistics
                                </div>
                            </div><div
                                data-slot="card-content"
                                class="px-6 text-stone-600">
                                Track your reading progress, top genres, and favorite authors.
                            </div>
                        </div><div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm border-stone-200 bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 text-orange-700">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-notebook-pen h-5 w-5"><path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4" /><path d="M2 6h4" /><path d="M2 10h4" /><path d="M2 14h4" /><path d="M2 18h4" /><path d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z" /></svg>
                                </div><div
                                    data-slot="card-title"
                                    class="font-semibold mt-3 text-lg">
                                    Private Notes &amp; Reviews
                                </div>
                            </div><div
                                data-slot="card-content"
                                class="px-6 text-stone-600">
                                Keep private annotations or share reviews with friends.
                            </div>
                        </div>
                    </div>
                </div>
            </section><section
                id="showcase"
                class="bg-white">
                <div class="container mx-auto px-4 py-16 sm:py-20">
                    <div class="mb-8 sm:mb-10">
                        <h2 class="text-3xl sm:text-4xl font-serif font-semibold tracking-tight">
                            See Devbound in action
                        </h2><p class="mt-2 text-stone-600">
                            Highlights from the dashboard, library, and book details.
                        </p>
                    </div><div class="relative">
                        <div
                            class="flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-4"
                            aria-label="Product showcase">
                            <div class="snap-center shrink-0 basis-full md:basis-[80%]">
                                <div class="relative aspect-[16/9] w-full max-w-3xl overflow-hidden rounded-xl border border-stone-200 bg-stone-50 shadow-sm">
                                    <div
                                        class="absolute inset-0 h-5 rounded-t-xl bg-stone-200/70"
                                        aria-hidden="true" /><img
                                            src="/images/home.png"
                                            alt="Home dashboard with stats and activity"
                                            class="absolute inset-0 h-full w-full object-cover">
                                </div><p class="mt-3 text-center text-sm text-stone-600">
                                    Home dashboard with stats and activity
                                </p>
                            </div><div class="snap-center shrink-0 basis-full md:basis-[80%]">
                                <div class="relative aspect-[16/9] w-full max-w-3xl overflow-hidden rounded-xl border border-stone-200 bg-stone-50 shadow-sm">
                                    <div
                                        class="absolute inset-0 h-5 rounded-t-xl bg-stone-200/70"
                                        aria-hidden="true" /><img
                                            src="/images/library.png"
                                            alt="Library view with list of books"
                                            class="absolute inset-0 h-full w-full object-cover">
                                </div><p class="mt-3 text-center text-sm text-stone-600">
                                    Library view with list of books
                                </p>
                            </div><div class="snap-center shrink-0 basis-full md:basis-[80%]">
                                <div class="relative aspect-[16/9] w-full max-w-3xl overflow-hidden rounded-xl border border-stone-200 bg-stone-50 shadow-sm">
                                    <div
                                        class="absolute inset-0 h-5 rounded-t-xl bg-stone-200/70"
                                        aria-hidden="true" /><img
                                            src="/images/search.png"
                                            alt="Add Book search results and filters"
                                            class="absolute inset-0 h-full w-full object-cover">
                                </div><p class="mt-3 text-center text-sm text-stone-600">
                                    Add Book search results and filters
                                </p>
                            </div><div class="snap-center shrink-0 basis-full md:basis-[80%]">
                                <div class="relative aspect-[16/9] w-full max-w-3xl overflow-hidden rounded-xl border border-stone-200 bg-stone-50 shadow-sm">
                                    <div
                                        class="absolute inset-0 h-5 rounded-t-xl bg-stone-200/70"
                                        aria-hidden="true" /><img
                                            src="/images/book.png"
                                            alt="Book detail page with notes and reviews"
                                            class="absolute inset-0 h-full w-full object-cover">
                                </div><p class="mt-3 text-center text-sm text-stone-600">
                                    Book detail page with notes and reviews
                                </p>
                            </div><div class="snap-center shrink-0 basis-full md:basis-[80%]">
                                <div class="relative aspect-[16/9] w-full max-w-3xl overflow-hidden rounded-xl border border-stone-200 bg-stone-50 shadow-sm">
                                    <div
                                        class="absolute inset-0 h-5 rounded-t-xl bg-stone-200/70"
                                        aria-hidden="true" /><img
                                            src="/images/scan.png"
                                            alt="Barcode scanning interface"
                                            class="absolute inset-0 h-full w-full object-cover">
                                </div><p class="mt-3 text-center text-sm text-stone-600">
                                    Barcode scanning interface
                                </p>
                            </div>
                        </div><div class="pointer-events-none absolute inset-y-1/3 left-0 hidden w-full justify-between px-2 md:flex">
                            <button
                                data-slot="button"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive text-secondary-foreground size-9 pointer-events-auto bg-white/90 shadow hover:bg-white"
                                aria-label="Previous slide">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="lucide lucide-chevron-left h-5 w-5"><path d="m15 18-6-6 6-6" /></svg>
                            </button><button
                                data-slot="button"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive text-secondary-foreground size-9 pointer-events-auto bg-white/90 shadow hover:bg-white"
                                aria-label="Next slide">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="lucide lucide-chevron-right h-5 w-5"><path d="m9 18 6-6-6-6" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section><section
                id="how-it-works"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="text-3xl sm:text-4xl font-serif font-semibold tracking-tight">
                            How it works
                        </h2><p class="mt-2 text-stone-600">
                            Three simple steps to level up your reading.
                        </p>
                    </div><div class="grid gap-4 md:grid-cols-3">
                        <div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 text-orange-700">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-scan-line h-5 w-5"><path d="M3 7V5a2 2 0 0 1 2-2h2" /><path d="M17 3h2a2 2 0 0 1 2 2v2" /><path d="M21 17v2a2 2 0 0 1-2 2h-2" /><path d="M7 21H5a2 2 0 0 1-2-2v-2" /><path d="M7 12h10" /></svg>
                                </div><div
                                    data-slot="card-title"
                                    class="leading-none font-semibold mt-3">
                                    Add Books
                                </div>
                            </div><div
                                data-slot="card-content"
                                class="px-6 text-stone-600">
                                Search or scan to import your collection in seconds.
                            </div>
                        </div><div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 text-orange-700">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-chart-line h-5 w-5"><path d="M3 3v16a2 2 0 0 0 2 2h16" /><path d="m19 9-5 5-4-4-3 3" /></svg>
                                </div><div
                                    data-slot="card-title"
                                    class="leading-none font-semibold mt-3">
                                    Track Progress
                                </div>
                            </div><div
                                data-slot="card-content"
                                class="px-6 text-stone-600">
                                Update statuses and view reading trends.
                            </div>
                        </div><div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 text-orange-700">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-share2 h-5 w-5"><circle
                                            cx="18"
                                            cy="5"
                                            r="3" /><circle
                                                cx="6"
                                                cy="12"
                                                r="3" /><circle
                                                    cx="18"
                                                    cy="19"
                                                    r="3" /><line
                                                        x1="8.59"
                                                        x2="15.42"
                                                        y1="13.51"
                                                        y2="17.49" /><line
                                                            x1="15.41"
                                                            x2="8.59"
                                                            y1="6.51"
                                                            y2="10.49" /></svg>
                                </div><div
                                    data-slot="card-title"
                                    class="leading-none font-semibold mt-3">
                                    Share &amp; Discover
                                </div>
                            </div><div
                                data-slot="card-content"
                                class="px-6 text-stone-600">
                                Connect with friends and uncover new recommendations.
                            </div>
                        </div>
                    </div>
                </div>
            </section><section
                id="testimonials"
                class="bg-[hsl(36,40%,96%)]">
                <div class="container mx-auto px-4 py-16 sm:py-20">
                    <div>
                        <div class="mb-8 sm:mb-10">
                            <h2 class="text-3xl sm:text-4xl font-serif font-semibold tracking-tight">
                                Loved by early readers
                            </h2><p class="mt-2 text-stone-600">
                                A few words from our beta users.
                            </p>
                        </div><div class="grid gap-4 md:grid-cols-3">
                            <div
                                data-slot="card"
                                class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm bg-white">
                                <div
                                    data-slot="card-header"
                                    class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-600/10 text-orange-700">
                                            <span class="text-sm font-semibold">E</span>
                                        </div><div>
                                            <div class="font-medium">
                                                Emma
                                            </div><div class="text-sm text-stone-600">
                                                Avid Reader
                                            </div>
                                        </div>
                                    </div>
                                </div><div
                                    data-slot="card-content"
                                    class="px-6">
                                    <div
                                        class="mb-2 flex items-center gap-1 text-orange-600"
                                        aria-label="5 out of 5 stars">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="24"
                                                        height="24"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="24"
                                                            height="24"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg>
                                    </div><p class="text-stone-700">
                                        “<!-- -->Devbound made organizing my bookshelf effortless. I actually finish more books now.<!-- -->”
                                    </p>
                                </div>
                            </div><div
                                data-slot="card"
                                class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm bg-white">
                                <div
                                    data-slot="card-header"
                                    class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-600/10 text-orange-700">
                                            <span class="text-sm font-semibold">M</span>
                                        </div><div>
                                            <div class="font-medium">
                                                Marcus
                                            </div><div class="text-sm text-stone-600">
                                                Non‑fiction Fan
                                            </div>
                                        </div>
                                    </div>
                                </div><div
                                    data-slot="card-content"
                                    class="px-6">
                                    <div
                                        class="mb-2 flex items-center gap-1 text-orange-600"
                                        aria-label="5 out of 5 stars">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="24"
                                                        height="24"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="24"
                                                            height="24"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg>
                                    </div><p class="text-stone-700">
                                        “<!-- -->The stats help me see my reading streaks and top genres. Super motivating!<!-- -->”
                                    </p>
                                </div>
                            </div><div
                                data-slot="card"
                                class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm bg-white">
                                <div
                                    data-slot="card-header"
                                    class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-600/10 text-orange-700">
                                            <span class="text-sm font-semibold">L</span>
                                        </div><div>
                                            <div class="font-medium">
                                                Lena
                                            </div><div class="text-sm text-stone-600">
                                                Librarian
                                            </div>
                                        </div>
                                    </div>
                                </div><div
                                    data-slot="card-content"
                                    class="px-6">
                                    <div
                                        class="mb-2 flex items-center gap-1 text-orange-600"
                                        aria-label="4 out of 5 stars">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="24"
                                                        height="24"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="lucide lucide-star h-4 w-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg><svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="24"
                                                            height="24"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="lucide lucide-star h-4 w-4"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg>
                                    </div><p class="text-stone-700">
                                        “<!-- -->Scanning barcodes is lightning fast. Importing my library took minutes.<!-- -->”
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section><section
                id="pricing"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="text-3xl sm:text-4xl font-serif font-semibold tracking-tight">
                            Plans &amp; Pricing
                        </h2><p class="mt-2 text-stone-600">
                            Start free, upgrade anytime. Cancel whenever you like.
                        </p>
                    </div><div class="grid gap-6 md:grid-cols-2">
                        <div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div
                                    data-slot="card-title"
                                    class="font-semibold text-2xl">
                                    Free
                                </div><p class="text-stone-600">
                                    Basic library management for getting started.
                                </p>
                            </div><div
                                data-slot="card-content"
                                class="px-6">
                                <ul class="space-y-2 text-sm text-stone-700">
                                    <li class="flex items-start gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-check mt-0.5 h-4 w-4 text-orange-700"><path d="M20 6 9 17l-5-5" /></svg>Basic library management
                                    </li><li class="flex items-start gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-check mt-0.5 h-4 w-4 text-orange-700"><path d="M20 6 9 17l-5-5" /></svg>Barcode scanning
                                    </li><li class="flex items-start gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-check mt-0.5 h-4 w-4 text-orange-700"><path d="M20 6 9 17l-5-5" /></svg>Up to 100 books
                                    </li>
                                </ul>
                            </div><div
                                data-slot="card-footer"
                                class="flex items-center px-6 [.border-t]:pt-6">
                                <a
                                    href="#pricing"
                                    data-slot="button"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive shadow-xs h-9 px-4 py-2 has-[>svg]:px-3 w-full bg-orange-700 text-white hover:bg-orange-800">Start Free</a>
                            </div>
                        </div><div
                            data-slot="card"
                            class="text-card-foreground flex flex-col gap-6 rounded-xl py-6 shadow-sm border-2 border-orange-700 bg-white">
                            <div
                                data-slot="card-header"
                                class="@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6">
                                <div class="flex items-baseline justify-between">
                                    <div
                                        data-slot="card-title"
                                        class="font-semibold text-2xl">
                                        Pro
                                    </div><span class="rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800">Most popular</span>
                                </div><p class="text-stone-600">
                                    Unlimited books, advanced analytics, and priority support.
                                </p><div class="mt-2 text-3xl font-semibold">
                                    $4.99<span class="text-base font-normal text-stone-600">/mo</span>
                                </div><div class="text-sm text-stone-600">
                                    Save with annual billing.
                                </div>
                            </div><div
                                data-slot="card-content"
                                class="px-6">
                                <ul class="space-y-2 text-sm text-stone-700">
                                    <li class="flex items-start gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-check mt-0.5 h-4 w-4 text-orange-700"><path d="M20 6 9 17l-5-5" /></svg>Unlimited books
                                    </li><li class="flex items-start gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-check mt-0.5 h-4 w-4 text-orange-700"><path d="M20 6 9 17l-5-5" /></svg>Advanced analytics
                                    </li><li class="flex items-start gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-check mt-0.5 h-4 w-4 text-orange-700"><path d="M20 6 9 17l-5-5" /></svg>Private notes
                                    </li><li class="flex items-start gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-check mt-0.5 h-4 w-4 text-orange-700"><path d="M20 6 9 17l-5-5" /></svg>Priority support
                                    </li>
                                </ul>
                            </div><div
                                data-slot="card-footer"
                                class="flex items-center px-6 [.border-t]:pt-6">
                                <a
                                    href="#pricing"
                                    data-slot="button"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive shadow-xs h-9 px-4 py-2 has-[>svg]:px-3 w-full bg-orange-700 text-white hover:bg-orange-800">Upgrade to Pro</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section><section
                id="faq"
                class="container mx-auto px-4 py-16 sm:py-20">
                <div>
                    <div class="mb-8 sm:mb-10">
                        <h2 class="text-3xl sm:text-4xl font-serif font-semibold tracking-tight">
                            Frequently asked questions
                        </h2><p class="mt-2 text-stone-600">
                            Answers to common questions.
                        </p>
                    </div><div
                        data-slot="accordion"
                        class="w-full"
                        data-orientation="vertical">
                        <div
                            data-state="closed"
                            data-orientation="vertical"
                            data-slot="accordion-item"
                            class="border-b last:border-b-0">
                            <h3
                                data-orientation="vertical"
                                data-state="closed"
                                class="flex">
                                <button
                                    id="radix-«Rroul7»"
                                    type="button"
                                    aria-controls="radix-«R2roul7»"
                                    aria-expanded="false"
                                    data-state="closed"
                                    data-orientation="vertical"
                                    data-slot="accordion-trigger"
                                    class="focus-visible:border-ring focus-visible:ring-ring/50 flex flex-1 items-start justify-between gap-4 rounded-md py-4 text-left text-sm font-medium transition-all outline-none hover:underline focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50 [&amp;[data-state=open]>svg]:rotate-180"
                                    data-radix-collection-item="">
                                    Is my data private?<svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-chevron-down text-muted-foreground pointer-events-none size-4 shrink-0 translate-y-0.5 transition-transform duration-200"><path d="m6 9 6 6 6-6" /></svg>
                                </button>
                            </h3><div
                                id="radix-«R2roul7»"
                                data-state="closed"
                                hidden=""
                                role="region"
                                aria-labelledby="radix-«Rroul7»"
                                data-orientation="vertical"
                                data-slot="accordion-content"
                                class="data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down overflow-hidden text-sm"
                                style="--radix-accordion-content-height: var(--radix-collapsible-content-height); --radix-accordion-content-width: var(--radix-collapsible-content-width);" />
                        </div><div
                            data-state="closed"
                            data-orientation="vertical"
                            data-slot="accordion-item"
                            class="border-b last:border-b-0">
                            <h3
                                data-orientation="vertical"
                                data-state="closed"
                                class="flex">
                                <button
                                    id="radix-«R1boul7»"
                                    type="button"
                                    aria-controls="radix-«R3boul7»"
                                    aria-expanded="false"
                                    data-state="closed"
                                    data-orientation="vertical"
                                    data-slot="accordion-trigger"
                                    class="focus-visible:border-ring focus-visible:ring-ring/50 flex flex-1 items-start justify-between gap-4 rounded-md py-4 text-left text-sm font-medium transition-all outline-none hover:underline focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50 [&amp;[data-state=open]>svg]:rotate-180"
                                    data-radix-collection-item="">
                                    Can I import from other services?<svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-chevron-down text-muted-foreground pointer-events-none size-4 shrink-0 translate-y-0.5 transition-transform duration-200"><path d="m6 9 6 6 6-6" /></svg>
                                </button>
                            </h3><div
                                id="radix-«R3boul7»"
                                data-state="closed"
                                hidden=""
                                role="region"
                                aria-labelledby="radix-«R1boul7»"
                                data-orientation="vertical"
                                data-slot="accordion-content"
                                class="data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down overflow-hidden text-sm"
                                style="--radix-accordion-content-height: var(--radix-collapsible-content-height); --radix-accordion-content-width: var(--radix-collapsible-content-width);" />
                        </div><div
                            data-state="closed"
                            data-orientation="vertical"
                            data-slot="accordion-item"
                            class="border-b last:border-b-0">
                            <h3
                                data-orientation="vertical"
                                data-state="closed"
                                class="flex">
                                <button
                                    id="radix-«R1roul7»"
                                    type="button"
                                    aria-controls="radix-«R3roul7»"
                                    aria-expanded="false"
                                    data-state="closed"
                                    data-orientation="vertical"
                                    data-slot="accordion-trigger"
                                    class="focus-visible:border-ring focus-visible:ring-ring/50 flex flex-1 items-start justify-between gap-4 rounded-md py-4 text-left text-sm font-medium transition-all outline-none hover:underline focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50 [&amp;[data-state=open]>svg]:rotate-180"
                                    data-radix-collection-item="">
                                    How do I upgrade or cancel?<svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-chevron-down text-muted-foreground pointer-events-none size-4 shrink-0 translate-y-0.5 transition-transform duration-200"><path d="m6 9 6 6 6-6" /></svg>
                                </button>
                            </h3><div
                                id="radix-«R3roul7»"
                                data-state="closed"
                                hidden=""
                                role="region"
                                aria-labelledby="radix-«R1roul7»"
                                data-orientation="vertical"
                                data-slot="accordion-content"
                                class="data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down overflow-hidden text-sm"
                                style="--radix-accordion-content-height: var(--radix-collapsible-content-height); --radix-accordion-content-width: var(--radix-collapsible-content-width);" />
                        </div>
                    </div>
                </div>
            </section>
        </main><footer class="mt-16 border-t border-stone-200 bg-[hsl(36,40%,97%)]">
            <div class="container mx-auto grid gap-10 px-4 py-10 md:grid-cols-3">
                <div>
                    <div class="font-serif text-xl">
                        Devbound
                    </div><p class="mt-2 max-w-sm text-sm text-stone-600">
                        Track your reading, organize your library, and share what you love.
                    </p><div class="mt-4 flex gap-3">
                        <a
                            href="https://twitter.com"
                            aria-label="Twitter"
                            class="text-stone-700 hover:text-stone-900"><svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-twitter h-5 w-5"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z" /></svg></a><a
                                    href="https://github.com"
                                    aria-label="GitHub"
                                    class="text-stone-700 hover:text-stone-900"><svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-github h-5 w-5"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4" /><path d="M9 18c-4.51 2-5-2-7-2" /></svg></a><a
                                            href="https://linkedin.com"
                                            aria-label="LinkedIn"
                                            class="text-stone-700 hover:text-stone-900"><svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="lucide lucide-linkedin h-5 w-5"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z" /><rect
                                                    width="4"
                                                    height="12"
                                                    x="2"
                                                    y="9" /><circle
                                                        cx="4"
                                                        cy="4"
                                                        r="2" /></svg></a>
                    </div>
                </div><div class="grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <div class="mb-2 font-medium">
                            Quick Links
                        </div><ul class="space-y-2 text-stone-700">
                            <li><a href="#">Home</a></li><li><a href="#showcase">Library</a></li><li><a href="#showcase">Add Book</a></li><li><a href="#pricing">Pricing</a></li><li><a href="#faq">FAQ</a></li><li><a href="/contact">Contact</a></li>
                        </ul>
                    </div><div>
                        <div class="mb-2 font-medium">
                            Legal
                        </div><ul class="space-y-2 text-stone-700">
                            <li><a href="/terms">Terms of Service</a></li><li><a href="/privacy">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div><div class="text-sm text-stone-600">
                    <div>© 2025 Devbound by SpacemanCodes LTD. All rights reserved.</div>
                </div>
            </div>
        </footer>
    </div>
</template>
