<script setup lang="ts">
import { computed, defineProps, defineEmits } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

const props = defineProps({
    modelValue: { type: Array, default: () => [] }, // Array ID yang dipilih
    options: { type: Array<{ value: string, label: string }>, required: true }, // Array objek: { value: string, label: string }
    placeholder: { type: String, default: 'Pilih opsi...' }
});

const emit = defineEmits(['update:modelValue']);

// Logika untuk menambahkan/menghapus ID dari modelValue
const toggleOption = (value: string) => { // Pastikan tipe adalah string
    // indexOf pada array string berfungsi baik
    const currentIndex = props.modelValue.indexOf(value);
    const newValue = [...props.modelValue];

    if (currentIndex === -1) {
        // PENTING: Pastikan kita hanya memasukkan string ke array.
        newValue.push(value);
    } else {
        newValue.splice(currentIndex, 1);
    }
    emit('update:modelValue', newValue);
};
const selectedCount = computed(() => props.modelValue.length);
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" class="w-full justify-between">
                {{ selectedCount > 0 ? `${selectedCount} dipilih` : placeholder }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[--radix-popover-trigger-width] p-0 max-h-60 overflow-y-auto">
            <div class="p-2 space-y-1">
                <div v-for="option in options" :key="option.value" 
                     class="flex items-center space-x-2 p-1 hover:bg-gray-100 cursor-pointer rounded-md">
                    
                    <Checkbox
                        :id="option.value"
                        :checked="modelValue.includes(option.value)"
                        @update:checked="toggleOption(option.value)"
                    />
                    <label :for="option.value" class="text-sm font-medium leading-none">
                        {{ option.label }}
                    </label>
                </div>
                <div v-if="options.length === 0" class="text-center text-sm text-gray-500 py-2">
                    Tidak ada opsi tersedia.
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>