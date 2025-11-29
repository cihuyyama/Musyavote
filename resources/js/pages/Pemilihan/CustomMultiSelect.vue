<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { X } from 'lucide-vue-next';
import { computed, defineEmits, defineProps, PropType } from 'vue';

const props = defineProps({
    modelValue: { type: Array as PropType<string[]>, default: () => [] }, // Array ID yang dipilih
    options: {
        type: Array as PropType<{ value: string; label: string }[]>,
        required: true,
    }, // Array objek: { value: string, label: string }
    placeholder: { type: String, default: 'Pilih opsi...' },
    showTags: { type: Boolean, default: true }, // Tambahkan prop untuk kontrol tampilan tags
});

const emit = defineEmits(['update:modelValue']);

// Logika untuk menambahkan/menghapus ID dari modelValue
const toggleOption = (value: string) => {
    const currentIndex = props.modelValue.indexOf(value);
    const newValue = [...props.modelValue];

    if (currentIndex === -1) {
        newValue.push(value);
    } else {
        newValue.splice(currentIndex, 1);
    }
    emit('update:modelValue', newValue);
};

// Fungsi untuk menghapus satu item
const removeOption = (value: string) => {
    const newValue = props.modelValue.filter((item) => item !== value);
    emit('update:modelValue', newValue);
};

// Fungsi untuk menghapus semua item
const clearAll = () => {
    emit('update:modelValue', []);
};

const selectedCount = computed(() => props.modelValue.length);

// Dapatkan label untuk value yang dipilih
const selectedOptions = computed(() => {
    return props.modelValue.map((value) => {
        const option = props.options.find((opt) => opt.value === value);
        return {
            value,
            label: option?.label || value,
        };
    });
});
</script>

<template>
    <div class="space-y-2">
        <!-- Tombol Trigger Popover -->
        <Popover>
            <PopoverTrigger as-child>
                <Button variant="outline" class="w-full justify-between">
                    {{
                        selectedCount > 0
                            ? `${selectedCount} dipilih`
                            : placeholder
                    }}
                </Button>
            </PopoverTrigger>
            <PopoverContent
                class="max-h-60 w-[--radix-popover-trigger-width] overflow-y-auto p-0"
            >
                <div class="space-y-1 p-2">
                    <div
                        v-for="option in options"
                        :key="option.value"
                        class="flex cursor-pointer items-center space-x-2 rounded-md p-1 hover:bg-gray-100"
                        @click="toggleOption(option.value)"
                    >
                        <Checkbox
                            :id="option.value"
                            :checked="modelValue.includes(option.value)"
                            @update:checked="toggleOption(option.value)"
                        />
                        <label
                            :for="option.value"
                            class="flex-1 cursor-pointer text-sm leading-none font-medium"
                        >
                            {{ option.label }}
                        </label>
                    </div>
                    <div
                        v-if="options.length === 0"
                        class="py-2 text-center text-sm text-gray-500"
                    >
                        Tidak ada opsi tersedia.
                    </div>
                </div>
            </PopoverContent>
        </Popover>

        <!-- Tags yang bisa dihapus -->
        <div v-if="showTags && selectedOptions.length > 0" class="space-y-2">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-700">
                    Terpilih: {{ selectedCount }} dari {{ options.length }}
                </p>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="clearAll"
                    class="h-6 px-2 text-xs text-red-500 hover:bg-red-50 hover:text-red-700"
                >
                    Hapus semua
                </Button>
            </div>

            <div class="flex flex-wrap gap-2">
                <div
                    v-for="selected in selectedOptions"
                    :key="selected.value"
                    class="inline-flex items-center gap-1 rounded-md bg-blue-100 px-2 py-1 text-sm text-blue-800"
                >
                    <span>{{ selected.label }}</span>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="removeOption(selected.value)"
                        class="ml-1 h-4 w-4 p-0 hover:bg-blue-200"
                    >
                        <X class="h-3 w-3" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
