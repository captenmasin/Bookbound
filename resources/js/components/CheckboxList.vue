<script setup lang="ts">
import { useId } from 'reka-ui'
import { Checkbox } from '@/components/ui/checkbox'

interface Option {
    label: string;
    value: string;
}

const props = defineProps({
    options: {
        type: Array as () => Option[],
        required: true
    },
    modelValue: {
        type: Array as () => string[],
        required: true
    }
})

const proxyStatus = defineModel<string[]>('modelValue')

const idPrefix = useId()

function isChecked (index: number) {
    if (!props.options[index]) {
        return false
    }

    return proxyStatus.value?.includes(props.options[index].value) || false
}
</script>

<template>
    <ul class="flex flex-col">
        <li
            v-for="(option, index) in options"
            :key="option.value">
            <label
                :class="[
                    proxyStatus?.includes(option.value) ? '' : '',
                    isChecked(index - 1) ? 'rounded-t-none' : '',
                    isChecked(index + 1) ? 'rounded-b-none' : '',
                    !isChecked(index) ? 'text-secondary-foreground hover:text-gray-900 dark:hover:text-white/80' : 'text-gray-900 dark:text-white',
                ]"
                class="flex w-full cursor-pointer items-center py-1.5 gap-2 transition-all"
                :for="`${idPrefix}-${option.value}`"
            >
                <Checkbox
                    :id="`${idPrefix}-${option.value}`"
                    :checked="proxyStatus?.includes(option.value)"
                    @update:checked="
                        (checked) => {
                            if (!proxyStatus) return;

                            if (checked) {
                                if (!proxyStatus.includes(option.value)) {
                                    proxyStatus.push(option.value);
                                }
                            } else {
                                const i = proxyStatus.indexOf(option.value);
                                if (i !== -1) proxyStatus.splice(i, 1);
                            }
                        }
                    "
                />
                <span class="text-sm">
                    {{ option.label }}
                </span>
            </label>
        </li>
    </ul>
</template>
