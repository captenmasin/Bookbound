<script setup lang="ts">
import Image from '@/components/Image.vue'
import { Book } from '@/types/book'
import { Link, usePage } from '@inertiajs/vue3'

withDefaults(
    defineProps<{
        book: Book;
    }>(),
    {}
)
</script>

<template>
    <div
        class="flex md:items-center gap-4 md:gap-8"
        :style="`--colour: ${book.colour};`">
        <div class="flex w-20 shrink-0 items-center gap-2 md:w-40">
            <Link
                :href="book.links.show"
                :page-props="{ ...usePage().props, book }"
                class="group"
                component="books/Show">
                <div class="aspect-book overflow-clip h-full w-full object-cover shadow-xs transition-all group-hover:shadow-xl">
                    <Image
                        v-if="book.cover"
                        :src="book.cover"
                        :height="315"
                        :width="200"
                        class="size-full object-cover"
                    />
                </div>
            </Link>
        </div>
        <div class="grid h-full w-full grid-cols-3 gap-4">
            <div class="col-span-3 flex flex-col justify-center md:col-span-2">
                <p
                    v-if="book.primary_category"
                    class="mb-1 font-sans text-[11px] tracking-wider text-primary uppercase">
                    {{ book.primary_category }}
                </p>
                <Link
                    :href="book.links.show"
                    :page-props="{ ...usePage().props, book }"
                    class="hover:text-primary"
                    component="books/Show">
                    <h3 class="line-clamp-2 font-serif text-lg font-bold">
                        {{ book.title }}
                    </h3>
                </Link>
                <p
                    v-if="book.authors"
                    class="mt-2 font-serif text-sm text-primary/80 italic">
                    {{ book.authors.map((author) => author.name).join(', ') }}
                </p>
            </div>
            <div class="hidden flex-col justify-center border-l border-primary/10 py-4 pl-8 md:flex">
                <div class="space-y-2">
                    <div class="border-outline-variant/30 flex items-baseline justify-between border-b border-dotted pb-1">
                        <span class="text-[10px] tracking-wider text-primary/60 uppercase">Format</span>
                        <span class="font-serif text-sm font-semibold text-primary capitalize">
                            {{ book.binding }}
                        </span>
                    </div>
                    <div class="border-outline-variant/30 flex items-baseline justify-between border-b border-dotted pb-1">
                        <span class="text-[10px] tracking-wider text-primary/60 uppercase">Pages</span>
                        <span class="font-serif text-sm font-semibold text-primary">
                            {{ book.page_count }}
                        </span>
                    </div>
                    <div
                        v-if="book.user_notes?.length"
                        class="flex flex-col pt-1">
                        <span class="mb-1 text-[10px] tracking-wider text-primary/60 uppercase">Note</span>
                        <p class="font-body line-clamp-3 text-xs italic">
                            {{ book.user_notes[0]?.content }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
