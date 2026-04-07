<script setup lang="ts">
import { CircleGaugeIcon } from 'lucide-vue-next'
import { Progress } from '@/components/ui/progress'
import { Card, CardTitle } from '@/components/ui/card'

withDefaults(
    defineProps<{
        insights: {
            read: 0;
            dropped: 0;
        };
        topGenres?: string[];
    }>(),
    {
        topGenres: () => []
    }
)
</script>

<template>
    <Card>
        <CardTitle class="mb-2">
            Library stats
        </CardTitle>

        <div class="space-y-5">
            <div>
                <div class="mb-2 flex justify-between text-xs tracking-wide text-[#9c8f83] uppercase">
                    <span>Books read</span>
                    <span class="font-medium text-[#8a4b2f]">{{ insights.read }}%</span>
                </div>
                <Progress
                    :model-value="insights.read"
                    :max="100" />
            </div>

            <!-- History -->
            <div>
                <div class="mb-2 flex justify-between text-xs tracking-wide text-[#9c8f83] uppercase">
                    <span>Books dropped</span>
                    <span class="font-medium text-[#8a4b2f]">{{ insights.dropped }}%</span>
                </div>
                <Progress
                    :model-value="insights.dropped"
                    :max="100" />
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                <CircleGaugeIcon class="size-5" />
            </div>

            <div v-if="topGenres">
                <p class="text-xs tracking-wide text-[#9c8f83] uppercase">
                    Top genres
                </p>
                <p
                    v-if="topGenres.length"
                    class="text-sm leading-[1.25] text-pretty font-semibold text-[#2d2a26]">
                    {{ topGenres.join(', ') }}
                </p>

                <p
                    v-else
                    class="text-sm text-muted-foreground">
                    Add more books to see your top genres.
                </p>
            </div>
        </div>
    </Card>
</template>

<style scoped></style>
