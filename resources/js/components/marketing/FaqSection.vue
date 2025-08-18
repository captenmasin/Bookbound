<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useUserBookStatus } from '@/composables/useUserBookStatus'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'

const props = usePage().props

const { possibleStatuses } = useUserBookStatus()

const faqs = [
    {
        question: 'How do I add new books to my library',
        answer: 'Books can be added by searching for them or by scanning their barcodes using your device’s camera.'
    },
    {
        question: 'Can I track the status of my books?',
        answer: `Yes, you can mark books as:
        <ul class="list-disc ml-6">
        ${possibleStatuses.map(s => `<li>${s.label}</li>`).join('')}
      </ul>`
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
</script>

<template>
    <section class="container mx-auto px-4 py-16 sm:py-20">
        <div class="flex flex-col md:flex-row">
            <div class="mb-2 w-full sm:mb-10 md:w-1/2">
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
                            <div
                                class="prose prose-sm max-w-none"
                                v-html="item.answer" />
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>
        </div>
    </section>
</template>
