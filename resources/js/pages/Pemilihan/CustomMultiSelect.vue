<script setup lang="ts">
import { computed, defineProps, defineEmits, PropType } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { X } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: Array as PropType<string[]>, default: () => [] }, // Array ID yang dipilih
    options: { type: Array as PropType<{ value: string, label: string }[]>, required: true }, // Array objek: { value: string, label: string }
    placeholder: { type: String, default: 'Pilih opsi...' },
    showTags: { type: Boolean, default: true } // Tambahkan prop untuk kontrol tampilan tags
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
    const newValue = props.modelValue.filter(item => item !== value);
    emit('update:modelValue', newValue);
};

// Fungsi untuk menghapus semua item
const clearAll = () => {
    emit('update:modelValue', []);
};

const selectedCount = computed(() => props.modelValue.length);

// Dapatkan label untuk value yang dipilih
const selectedOptions = computed(() => {
    return props.modelValue.map(value => {
        const option = props.options.find(opt => opt.value === value);
        return {
            value,
            label: option?.label || value
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
                    {{ selectedCount > 0 ? `${selectedCount} dipilih` : placeholder }}
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-[--radix-popover-trigger-width] p-0 max-h-60 overflow-y-auto">
                <div class="p-2 space-y-1">
                    <div v-for="option in options" :key="option.value" 
                         class="flex items-center space-x-2 p-1 hover:bg-gray-100 cursor-pointer rounded-md"
                         @click="toggleOption(option.value)">
                        
                        <Checkbox
                            :id="option.value"
                            :checked="modelValue.includes(option.value)"
                            @update:checked="toggleOption(option.value)"
                        />
                        <label :for="option.value" class="text-sm font-medium leading-none cursor-pointer flex-1">
                            {{ option.label }}
                        </label>
                    </div>
                    <div v-if="options.length === 0" class="text-center text-sm text-gray-500 py-2">
                        Tidak ada opsi tersedia.
                    </div>
                </div>
            </PopoverContent>
        </Popover>

        <!-- Tags yang bisa dihapus -->
        <div v-if="showTags && selectedOptions.length > 0" class="space-y-2">
            <div class="flex justify-between items-center">
                <p class="text-sm font-medium text-gray-700">
                    Terpilih: {{ selectedCount }} dari {{ options.length }}
                </p>
                <Button 
                    type="button" 
                    variant="ghost" 
                    size="sm" 
                    @click="clearAll"
                    class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 h-6 px-2"
                >
                    Hapus semua
                </Button>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <div 
                    v-for="selected in selectedOptions" 
                    :key="selected.value"
                    class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-md text-sm"
                >
                    <span>{{ selected.label }}</span>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="removeOption(selected.value)"
                        class="h-4 w-4 p-0 hover:bg-blue-200 ml-1"
                    >
                        <X class="h-3 w-3" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>