<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';

const view = defineModel<'list' | 'grid' | 'shelf'>({
    default: 'grid',
});

const views = [
    {
        value: 'grid',
        label: 'Grid',
        icon: 'LayoutGrid',
    },
    {
        value: 'list',
        label: 'List',
        icon: 'LayoutList',
    },
    {
        value: 'shelf',
        label: 'Shelf',
        icon: 'LibraryBig',
    },
];
</script>
<template>
    <Tabs v-model="view" class="flex w-full flex-1" :default-value="view">
        <TabsList class="w-full">
            <TabsTrigger v-for="item in views" :key="item.value" :value="item.value" class="px-0 md:px-4">
                <Icon :name="item.icon" class="w-4" />
                <span class="hidden md:flex">
                    {{ item.label }}
                </span>
            </TabsTrigger>
        </TabsList>
    </Tabs>
</template>
