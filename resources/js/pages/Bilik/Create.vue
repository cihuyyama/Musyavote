<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm as useInertiaForm } from '@inertiajs/vue3';

import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';

import { toTypedSchema } from '@vee-validate/zod';
import { z } from 'zod';
import { useForm as useVeeForm } from 'vee-validate';

import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/components/ui/form"

import { Checkbox } from "@/components/ui/checkbox";
import { Popover, PopoverTrigger, PopoverContent } from "@/components/ui/popover";

import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem
} from "@/components/ui/command";

import Input from '@/components/ui/input/Input.vue';
import { toast } from 'vue-sonner';
import { computed } from 'vue';
import { ChevronDown, X } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Bilik', href: '/biliks' },
    { title: 'Buat Bilik', href: '/biliks/create' },
];

const props = defineProps<{
    pemilihanOptions: Array<{ id: string; nama_pemilihan: string }>;
}>();

const formSchema = z.object({
    nama: z.string().min(1, "Nama lengkap wajib diisi"),
    username: z.string().min(1, "Username wajib diisi"),
    password: z.string().min(6, "Password minimal 6 karakter"),
    password_confirmation: z.string().min(6, 'Konfirmasi password minimal 6 karakter.'),
    pemilihan_ids: z.array(z.string()).optional(),
});

type FormData = {
    nama: string;
    username: string;
    password: string;
    password_confirmation: string;
    pemilihan_ids?: string[];
};

const formInertia = useInertiaForm<FormData>({
    nama: '',
    username: '',
    password: '',
    password_confirmation: '',
    pemilihan_ids: [],
});

// Vee-validate
const { handleSubmit, setFieldValue } = useVeeForm({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

// Sync selected pemilihan
const checkedPemilihanIds = computed({
    get: () => formInertia.pemilihan_ids || [],
    set: (val: string[]) => {
        formInertia.pemilihan_ids = val;
        setFieldValue("pemilihan_ids", val); // WAJIB agar VeeValidate ikut update
    },
});

// Toggle item - FIXED: menggunakan event dari CommandItem
function togglePemilihan(id: string) {
    let list = [...checkedPemilihanIds.value];
    
    if (list.includes(id)) {
        list = list.filter(x => x !== id);
    } else {
        list.push(id);
    }
    
    checkedPemilihanIds.value = list;
    setFieldValue("pemilihan_ids", list);
}

// Remove single selected item
function removePemilihan(id: string) {
    const list = checkedPemilihanIds.value.filter(x => x !== id);
    checkedPemilihanIds.value = list;
    setFieldValue("pemilihan_ids", list);
}

// Clear all selected items
function clearAllPemilihan() {
    checkedPemilihanIds.value = [];
    setFieldValue("pemilihan_ids", []);
}

// Check if item is selected
function isSelected(id: string): boolean {
    return checkedPemilihanIds.value.includes(id);
}

// Submit
const onSubmit = handleSubmit((values) => {
    formInertia.nama = values.nama;
    formInertia.username = values.username;
    formInertia.password = values.password;
    formInertia.password_confirmation = values.password_confirmation;
    formInertia.pemilihan_ids = values.pemilihan_ids || [];

    const submissionPromise = new Promise<{ message: string }>((resolve, reject) => {
        formInertia.post('/biliks', {
            onSuccess: () => {
                resolve({ message: 'Data Bilik berhasil disimpan!' });
                formInertia.reset();
            },
            onError: (errors) => {
                reject(Object.values(errors)[0] || 'Terjadi kesalahan validasi');
            },
        });
    });

    toast.promise(submissionPromise, {
        loading: 'Sedang memproses data...',
        success: (data: { message: string }) => data.message,
        error: (err: unknown) => `Gagal: ${String(err)}`,
    });
});
</script>

<template>
    <Head title="Bilik" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card class="rounded-lg border-none mt-2 w-full">
            <CardContent class="p-6 w-full">
                <div class="flex justify-center items-start min-h-[calc(100vh-56px)] w-full">
                    <div class="w-full">
                        <form class="w-2/3 space-y-6" @submit.prevent="onSubmit">

                            <!-- NAMA -->
                            <FormField v-slot="{ componentField }" name="nama">
                                <FormItem>
                                    <FormLabel>Nama Bilik</FormLabel>
                                    <FormControl>
                                        <Input type="text" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <!-- PEMILIHAN MULTI SELECT -->
                            <FormField v-slot="{ errorMessage }" name="pemilihan_ids">
                                <FormItem>
                                    <FormLabel>Pilih Pemilihan yang Diizinkan</FormLabel>

                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="w-full justify-between"
                                            >
                                                <span class="truncate">
                                                    <template v-if="checkedPemilihanIds.length === 0">
                                                        Pilih pemilihan...
                                                    </template>
                                                    <template v-else>
                                                        {{ checkedPemilihanIds.length }} dipilih
                                                    </template>
                                                </span>
                                                <ChevronDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </PopoverTrigger>

                                        <PopoverContent class="w-full p-0">
                                            <Command>
                                                <CommandInput placeholder="Cari pemilihan..." />
                                                <CommandEmpty>Tidak ada hasil.</CommandEmpty>

                                                <CommandGroup>
                                                    <CommandItem
                                                        v-for="item in props.pemilihanOptions"
                                                        :key="item.id"
                                                        :value="String(item.id)"
                                                        @select="() => togglePemilihan(item.id)"
                                                    >
                                                        <div class="flex items-center space-x-2 w-full">
                                                            <Checkbox
                                                                :checked="isSelected(item.id)"
                                                                @update:checked="() => togglePemilihan(item.id)"
                                                            />
                                                            <span>{{ item.nama_pemilihan }}</span>
                                                        </div>
                                                    </CommandItem>
                                                </CommandGroup>
                                            </Command>
                                        </PopoverContent>
                                    </Popover>

                                    <!-- Display selected items below the button -->
                                    <div v-if="checkedPemilihanIds.length > 0" class="mt-3 space-y-2">
                                        <div class="flex justify-between items-center">
                                            <p class="text-sm font-medium text-gray-700">Yang dipilih:</p>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="clearAllPemilihan"
                                                class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50"
                                            >
                                                Hapus semua
                                            </Button>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <div 
                                                v-for="item in props.pemilihanOptions.filter(opt => checkedPemilihanIds.includes(opt.id))" 
                                                :key="item.id"
                                                class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-md text-sm"
                                            >
                                                <span>{{ item.nama_pemilihan }}</span>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    @click="removePemilihan(item.id)"
                                                    class="h-4 w-4 p-0 hover:bg-blue-200"
                                                >
                                                    <X class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <FormMessage>{{ errorMessage }}</FormMessage>

                                    <p class="text-sm text-gray-500 mt-2">
                                        ({{ checkedPemilihanIds.length }} terpilih)
                                    </p>
                                </FormItem>
                            </FormField>

                            <!-- USERNAME -->
                            <FormField v-slot="{ componentField }" name="username">
                                <FormItem>
                                    <FormLabel>Username</FormLabel>
                                    <FormControl>
                                        <Input type="text" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <!-- PASSWORD -->
                            <FormField v-slot="{ componentField }" name="password">
                                <FormItem>
                                    <FormLabel>Password</FormLabel>
                                    <FormControl>
                                        <Input type="password" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <!-- PASSWORD CONFIRM -->
                            <FormField v-slot="{ componentField }" name="password_confirmation">
                                <FormItem>
                                    <FormLabel>Konfirmasi Password</FormLabel>
                                    <FormControl>
                                        <Input type="password" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <Button type="submit">Simpan</Button>
                        </form>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>