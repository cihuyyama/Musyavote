<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { Checkbox } from '@/components/ui/checkbox';
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import Input from '@/components/ui/input/Input.vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { UserPlus } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate'; // Use alias for VeeValidate
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { z } from 'zod'; // Corrected: z from 'zod'
import { Peserta } from '../Calon/column';
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
        required_error: 'Nama Pemilihan wajib diisi',
    }),
    minimal_kehadiran: z.coerce.number({
        required_error: 'Syarat Kehadiran wajib diisi',
    }),
    boleh_tidak_memilih: z.boolean({
        required_error: 'Aturan Abstain wajib diisi',
    }),
    jumlah_formatur_terpilih: z.coerce.number().optional(),
    calons: z.array(z.string()).optional(),
});

type FormData = {
    nama_pemilihan: string;
    minimal_kehadiran: number;
    boleh_tidak_memilih: boolean;
    jumlah_formatur_terpilih?: number;
    calons: string[];
};
const formInertia = useInertiaForm<FormData>({
    nama_pemilihan: '',
    minimal_kehadiran: 0,
    boleh_tidak_memilih: false,
    jumlah_formatur_terpilih: 0,
    calons: [],
});

const { handleSubmit, setFieldValue, values } = useVeeForm<
    z.infer<typeof formSchema>
>({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

const minimalKehadiranOptions = [
    { value: 0, label: '0 Pleno' },
    { value: 1, label: '1 Pleno' },
    { value: 2, label: '2 Pleno' },
    { value: 3, label: '3 Pleno' },
    { value: 4, label: '4 Pleno' },
];

// 1. Ubah data calon menjadi format yang mudah digunakan oleh multi-select
const calonOptions = computed(() => {
    // Filter hanya peserta yang sudah memiliki relasi calon (c.calon != null)
    return props.pesertas
        .filter((c) => c.calon)
        .map((c) => ({
            // PENTING: Value HARUS menggunakan ID dari objek Calon
            value: c.calon!.id,
            label: `${c.nama} (${c.calon!.jabatan})`,
            jabatan: c.calon!.jabatan,
        }));
});

// 2. Fungsi untuk memuat ID calon berdasarkan jabatan
const loadCalonsByJabatan = (jabatanFilter: 'Ketua' | 'Formatur' | 'Semua') => {
    let ids: string[];

    if (jabatanFilter === 'Semua') {
        // Ambil ID dari SEMUA Peserta yang merupakan Calon
        ids = props.pesertas
            .filter((c) => c.calon) // Hanya yang punya relasi Calon
            .map((c) => c.calon!.id); // <-- Ambil Calon.id
    } else {
        // Filter berdasarkan Peserta yang memiliki relasi Calon DENGAN Jabatan yang sesuai
        ids = props.pesertas
            .filter((c) => c.calon && c.calon.jabatan === jabatanFilter)
            .map((c) => c.calon!.id); // <-- Ambil Calon.id
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
        .filter((option) => selectedIds.includes(option.value))
        .map((option) => option.label);
});

const onSubmit = handleSubmit((values) => {
    formInertia.nama_pemilihan = values.nama_pemilihan;
    formInertia.minimal_kehadiran = values.minimal_kehadiran;
    formInertia.boleh_tidak_memilih = values.boleh_tidak_memilih;
    formInertia.jumlah_formatur_terpilih = values.jumlah_formatur_terpilih;
    formInertia.calons = values.calons || [];
    const submissionPromise = new Promise<{ message: any }>(
        (resolve, reject) => {
            formInertia.post('/pemilihan', {
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
        },
    );

    toast.promise(submissionPromise, {
        loading: 'Sedang memproses dan mengunggah data...',
        success: (data: { message: any }) => {
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
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full"
                >
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <form @submit.prevent="onSubmit" class="space-y-6">
                                <h3 class="border-b pb-2 text-lg font-semibold">
                                    Aturan Dasar
                                </h3>

                                <FormField
                                    v-slot="{ componentField }"
                                    name="nama_pemilihan"
                                >
                                    <FormItem>
                                        <FormLabel>Nama Pemilihan</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="text"
                                                placeholder="Contoh: Pemilihan Periode 2026"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField
                                    v-slot="{ componentField, errorMessage }"
                                    name="minimal_kehadiran"
                                >
                                    <FormItem>
                                        <FormLabel
                                            >Syarat Minimal Kehadiran
                                            (Pleno)</FormLabel
                                        >

                                        <Select
                                            :model-value="
                                                componentField.modelValue !==
                                                undefined
                                                    ? String(
                                                          componentField.modelValue,
                                                      )
                                                    : undefined
                                            "
                                            @update:model-value="
                                                (val) =>
                                                    componentField.onChange(
                                                        Number(val),
                                                    )
                                            "
                                        >
                                            <FormControl>
                                                <SelectTrigger>
                                                    <SelectValue
                                                        placeholder="Pilih syarat minimal..."
                                                    />
                                                </SelectTrigger>
                                            </FormControl>

                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem
                                                        v-for="option in minimalKehadiranOptions"
                                                        :key="option.value"
                                                        :value="
                                                            String(option.value)
                                                        "
                                                        class="cursor-pointer"
                                                    >
                                                        {{ option.label }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                        <FormMessage>{{
                                            errorMessage
                                        }}</FormMessage>
                                    </FormItem>
                                </FormField>

                                <FormField
                                    v-slot="{ componentField, errorMessage }"
                                    name="jumlah_formatur_terpilih"
                                >
                                    <FormItem>
                                        <FormLabel
                                            >Jumlah Formatur Terpilih</FormLabel
                                        >
                                        <FormControl>
                                            <Input
                                                type="number"
                                                min="0"
                                                placeholder="Contoh: 12"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage>{{
                                            errorMessage
                                        }}</FormMessage>
                                    </FormItem>
                                </FormField>

                                <FormField
                                    v-slot="{ value, handleChange }"
                                    name="boleh_tidak_memilih"
                                >
                                    <FormItem
                                        class="flex flex-row items-center space-y-0 space-x-3 rounded-md border p-3"
                                    >
                                        <FormControl>
                                            <Checkbox
                                                :checked="value"
                                                @update:checked="handleChange"
                                            />
                                        </FormControl>
                                        <div class="space-y-1 leading-none">
                                            <FormLabel
                                                class="text-base font-medium"
                                            >
                                                Izinkan Abstain/Tidak Memilih
                                                Calon (Ketua Umum)
                                            </FormLabel>
                                            <p class="text-sm text-gray-500">
                                                Jika dicentang, peserta yang sah
                                                boleh meninggalkan surat suara
                                                kosong.
                                            </p>
                                        </div>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <h3
                                    class="flex items-center gap-2 border-b pb-2 text-lg font-semibold"
                                >
                                    <UserPlus class="h-5 w-5" /> Daftar Calon
                                </h3>

                                <div class="space-y-4">
                                    <div class="flex space-x-2">
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            @click="
                                                loadCalonsByJabatan('Ketua')
                                            "
                                        >
                                            Pilih Semua Calon Ketua ({{
                                                calonOptions.filter(
                                                    (c) =>
                                                        c.jabatan === 'Ketua',
                                                ).length
                                            }})
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            @click="
                                                loadCalonsByJabatan('Formatur')
                                            "
                                        >
                                            Pilih Semua Calon Formatur ({{
                                                calonOptions.filter(
                                                    (c) =>
                                                        c.jabatan ===
                                                        'Formatur',
                                                ).length
                                            }})
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            @click="
                                                loadCalonsByJabatan('Semua')
                                            "
                                        >
                                            Pilih Semua Calon
                                        </Button>
                                    </div>

                                    <FormField
                                        v-slot="{ componentField }"
                                        name="calons"
                                    >
                                        <FormItem>
                                            <FormLabel
                                                >Pilih Manual Calon yang
                                                Berpartisipasi</FormLabel
                                            >
                                            <FormControl>
                                                <CustomMultiSelect
                                                    :options="calonOptions"
                                                    :placeholder="'Pilih satu atau lebih calon'"
                                                    :model-value="
                                                        componentField[
                                                            'modelValue'
                                                        ]
                                                    "
                                                    @update:model-value="
                                                        componentField.onChange
                                                    "
                                                    :show-tags="true"
                                                />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>

                                <Button
                                    type="submit"
                                    :disabled="formInertia.processing"
                                >
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
