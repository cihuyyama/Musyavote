<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import { toTypedSchema } from '@vee-validate/zod';
import { z } from 'zod'; // Corrected: z from 'zod'
import { useForm as useVeeForm } from 'vee-validate'; // Use alias for VeeValidate
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/components/ui/form"
import {
    Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import Input from '@/components/ui/input/Input.vue';
import { toast } from 'vue-sonner';
import { computed } from 'vue';
import { Peserta } from '../Calon/column';
import { Checkbox } from '@/components/ui/checkbox';
import { UserPlus } from 'lucide-vue-next';
import CustomMultiSelect from './CustomMultiSelect.vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pemilihan', href: '/pemilihan' },
    { title: 'Buat Pemilihan', href: '/pemilihan/create' },
];

const props = defineProps<{
    pesertas: Peserta[];
    jabatanOptions: Array<'Ketua' | 'Formatur'>;
}>();

const formSchema = z.object({
    nama_pemilihan: z.string({
        required_error: "Nama Pemilihan wajib diisi",
    }),
    minimal_kehadiran: z.coerce.number({
        required_error: "Syarat Kehadiran wajib diisi",
    }),
    boleh_tidak_memilih: z.boolean({
        required_error: "Aturan Abstain wajib diisi",
    }),
    calons: z.array(z.string()).optional(),
})

type FormData = {
    nama_pemilihan: string;
    minimal_kehadiran: number;
    boleh_tidak_memilih: boolean;
    calons: string[];
};
const formInertia = useInertiaForm<FormData>({
    nama_pemilihan: '',
    minimal_kehadiran: 0,
    boleh_tidak_memilih: false,
    calons: [],
});

const { handleSubmit, setFieldValue, values } = useVeeForm<z.infer<typeof formSchema>>({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

const minimalKehadiranOptions = [
    { value: 0, label: '0 Pleno' },
    { value: 1, label: '1 Pleno' },
    { value: 2, label: '2 Pleno' },
    { value: 3, label: '3 Pleno (Syarat Umum)' },
    { value: 4, label: '4 Pleno (Wajib Hadir Semua)' },
];

// 1. Ubah data calon menjadi format yang mudah digunakan oleh multi-select
const calonOptions = computed(() => {
    // Filter hanya peserta yang sudah memiliki relasi calon (c.calon != null)
    return props.pesertas
        .filter(c => c.calon)
        .map(c => ({
            // PENTING: Value HARUS menggunakan ID dari objek Calon
            value: c.calon!.id,
            label: `${c.nama} (${c.calon!.jabatan})`,
            jabatan: c.calon!.jabatan
        }));
});

// 2. Fungsi untuk memuat ID calon berdasarkan jabatan
const loadCalonsByJabatan = (jabatanFilter: 'Ketua' | 'Formatur' | 'Semua') => {
    let ids: string[];

    if (jabatanFilter === 'Semua') {
        // Ambil ID dari SEMUA Peserta yang merupakan Calon
        ids = props.pesertas
            .filter(c => c.calon) // Hanya yang punya relasi Calon
            .map(c => c.calon!.id); // <-- Ambil Calon.id
    } else {
        // Filter berdasarkan Peserta yang memiliki relasi Calon DENGAN Jabatan yang sesuai
        ids = props.pesertas
            .filter(c => c.calon && c.calon.jabatan === jabatanFilter)
            .map(c => c.calon!.id); // <-- Ambil Calon.id
    }

    // Menggunakan setFieldValue untuk memperbarui nilai di VeeValidate form
    setFieldValue('calons', ids);
};

const selectedCalonNames = computed(() => {
    // Ambil nilai calons, default ke array kosong jika null/undefined
    const selectedIds = values.calons ?? []; 
    
    if (selectedIds.length === 0) {
        return [];
    }

    // Cocokkan ID yang terpilih dengan opsi lengkap
    return calonOptions.value
        .filter(option => selectedIds.includes(option.value))
        .map(option => option.label);
});

const onSubmit = handleSubmit((values) => {
    console.log(values);
    formInertia.nama_pemilihan = values.nama_pemilihan;
    formInertia.minimal_kehadiran = values.minimal_kehadiran;
    formInertia.boleh_tidak_memilih = values.boleh_tidak_memilih;
    formInertia.calons = values.calons || [];
    const submissionPromise = new Promise<{ message: any }>((resolve, reject) => {
        formInertia.post(("/pemilihan"), {
            onSuccess: () => {
                resolve({
                    message: 'Data Bilik berhasil disimpan!',
                });

                formInertia.reset();
            },

            onError: (errors) => {
                const firstError = Object.values(errors)[0] as string;
                reject(firstError || 'Terjadi kesalahan saat validasi.');
            },
        });
    });

    toast.promise(submissionPromise, {
        loading: 'Sedang memproses dan mengunggah data...',
        success: (data: { message: any; }) => {
            return `Data berhasil disimpan! ${data.message}`;
        },
        error: (errorMsg: any) => {
            // The error message comes from the Promise rejection above
            return `Gagal: ${errorMsg}`;
        },
    });
});

</script>

<template>

    <Head title="Pemilihan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full">
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <form @submit.prevent="onSubmit" class="space-y-6">

                                <h3 class="text-lg font-semibold border-b pb-2">Aturan Dasar</h3>

                                <FormField v-slot="{ componentField }" name="nama_pemilihan">
                                    <FormItem>
                                        <FormLabel>Nama Pemilihan</FormLabel>
                                        <FormControl>
                                            <Input type="text" placeholder="Contoh: Pemilihan Periode 2026"
                                                v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField, errorMessage }" name="minimal_kehadiran">
                                    <FormItem>
                                        <FormLabel>Syarat Minimal Kehadiran (Pleno)</FormLabel>

                                        <Select
                                            :model-value="componentField.modelValue !== undefined ? String(componentField.modelValue) : undefined"
                                            @update:model-value="val => componentField.onChange(Number(val))">
                                            <FormControl>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Pilih syarat minimal..." />
                                                </SelectTrigger>
                                            </FormControl>

                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem v-for="option in minimalKehadiranOptions"
                                                        :key="option.value" :value="String(option.value)"
                                                        class="cursor-pointer">
                                                        {{ option.label }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                        <FormMessage>{{ errorMessage }}</FormMessage>
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ value, handleChange }" name="boleh_tidak_memilih">
                                    <FormItem
                                        class="flex flex-row items-center space-x-3 space-y-0 p-3 border rounded-md ">
                                        <FormControl>
                                            <Checkbox :checked="value" @update:checked="handleChange" />
                                        </FormControl>
                                        <div class="space-y-1 leading-none">
                                            <FormLabel class="text-base font-medium">
                                                Izinkan Abstain/Tidak Memilih Calon
                                            </FormLabel>
                                            <p class="text-sm text-gray-500">Jika dicentang, peserta yang sah boleh
                                                meninggalkan surat suara kosong.</p>
                                        </div>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <h3 class="text-lg font-semibold border-b pb-2 flex items-center gap-2">
                                    <UserPlus class="h-5 w-5" /> Daftar Calon
                                </h3>

                                <div class="space-y-4">
                                    <div class="flex space-x-2">
                                        <Button type="button" variant="secondary" @click="loadCalonsByJabatan('Ketua')">
                                            Pilih Semua Calon Ketua ({{calonOptions.filter(c => c.jabatan ===
                                                'Ketua').length}})
                                        </Button>
                                        <Button type="button" variant="secondary"
                                            @click="loadCalonsByJabatan('Formatur')">
                                            Pilih Semua Calon Formatur ({{calonOptions.filter(c => c.jabatan ===
                                                'Formatur').length}})
                                        </Button>
                                        <Button type="button" variant="secondary" @click="loadCalonsByJabatan('Semua')">
                                            Pilih Semua Calon
                                        </Button>
                                    </div>

                                    <FormField v-slot="{ componentField }" name="calons">
                                        <FormItem>
                                            <FormLabel>Pilih Manual Calon yang Berpartisipasi</FormLabel>
                                            <FormControl>
                                                <CustomMultiSelect :options="calonOptions"
                                                    :placeholder="'Pilih satu atau lebih calon'"
                                                    :model-value="componentField['modelValue']"
                                                    @update:model-value="componentField.onChange" />
                                            </FormControl>
                                            <FormMessage />
                                            <p v-if="componentField.modelValue && componentField.modelValue.length > 0"
                                                class="text-sm text-green-600">
                                                Total {{ componentField.modelValue.length }} calon terpilih.
                                            </p>
                                        </FormItem>
                                    </FormField>
                                    <div v-if="selectedCalonNames.length > 0" class="border p-3 rounded-md bg-gray-50">
                                        <p class="font-medium text-sm mb-2 text-gray-700">
                                            Calon Terpilih ({{ selectedCalonNames.length }}/{{ calonOptions.length }})
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="name in selectedCalonNames" :key="name"
                                                class="px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full shadow-sm">
                                                {{ name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <Button type="submit" :disabled="formInertia.processing">
                                    Buat Pemilihan & Tetapkan Calon
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>