<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
</script>

<template>
    <div>
        <Popover>
            <PopoverTrigger as-child>
                <button class="inline-flex w-auto hover:text-foreground items-center px-0 py-1 cursor-pointer gap-1 text-sm text-secondary-foreground">
                    <Icon name="Lightbulb" />
                    Search tips
                </button>
            </PopoverTrigger>
            <PopoverContent
                class="w-auto px-2 py-3 bg-white dark:bg-neutral-900"
                align="start">
                <div class="px-6 text-sm">
                    <ul class="space-y-2 list-disc">
                        <li>
                            Search by author using <code class="rounded-sm bg-muted px-1">author: <em>example</em></code>
                        </li>
                        <li>
                            Search by tag using <code class="rounded-sm bg-muted px-1">tag: <em>example</em></code>
                        </li>
                    </ul>
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>
