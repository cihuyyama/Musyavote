[file name]: Create.vue
[file content begin]
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
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { z } from 'zod'; // Corrected: z from 'zod'
import CustomMultiSelect from './CustomMultiSelect.vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pemilihan', href: '/pemilihan' },
    { title: 'Buat Pemilihan', href: '/pemilihan/create' },
];

// Ganti Peserta[] dengan Calon[] karena sekarang langsung menggunakan model Calon
const props = defineProps<{
    calons: Array<{
        id: string;
        nama: string;
        jabatan: 'Ketua' | 'Formatur';
        nomor_urut: number;
        asal_pimpinan?: string;
        jenis_kelamin?: string;
        foto?: string;
    }>;
    jabatanOptions: Array<'Ketua' | 'Formatur'>;
}>();

// Tabs state
const activeTab = ref<'Ketua' | 'Formatur'>('Ketua');

// Form schema untuk masing-masing tab
const formSchema = z.object({
    nama_pemilihan: z.string({
        required_error: 'Nama Pemilihan wajib diisi',
    }),
    minimal_kehadiran: z.coerce.number({
        required_error: 'Syarat Kehadiran wajib diisi',
    }),
    // Hanya untuk Ketua
    boleh_tidak_memilih: z.boolean().optional(),
    // Hanya untuk Formatur
    jumlah_formatur_terpilih: z.coerce.number().min(1, 'Minimal 1 formatur terpilih').optional(),
    calons: z.array(z.string()).min(1, 'Pilih minimal 1 calon'),
});

type FormData = {
    nama_pemilihan: string;
    minimal_kehadiran: number;
    boleh_tidak_memilih?: boolean;
    jumlah_formatur_terpilih?: number;
    calons: string[];
};

const formInertia = useInertiaForm<FormData>({
    nama_pemilihan: '',
    minimal_kehadiran: 0,
    boleh_tidak_memilih: false,
    jumlah_formatur_terpilih: 1, // Default 1 untuk formatur
    calons: [],
});

const { handleSubmit, setFieldValue, values, resetForm } = useVeeForm<
    z.infer<typeof formSchema>
>({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

// Watch untuk reset jumlah_formatur_terpilih ke 1 saat tab Formatur aktif
watch(activeTab, (newTab) => {
    if (newTab === 'Formatur') {
        setFieldValue('jumlah_formatur_terpilih', 1);
    }
});

const minimalKehadiranOptions = [
    { value: 0, label: '0 Pleno' },
    { value: 1, label: '1 Pleno' },
    { value: 2, label: '2 Pleno' },
    { value: 3, label: '3 Pleno' },
    { value: 4, label: '4 Pleno' },
];

// Filter calon berdasarkan jabatan aktif
const filteredCalons = computed(() => {
    return props.calons.filter(calon => calon.jabatan === activeTab.value);
});

// Opsi untuk multi-select berdasarkan jabatan aktif
const calonOptions = computed(() => {
    return filteredCalons.value.map((calon) => ({
        value: calon.id,
        label: `${calon.nama} (${calon.jabatan}) - No. ${calon.nomor_urut}`,
        jabatan: calon.jabatan,
        nomorUrut: calon.nomor_urut,
    }));
});

// Fungsi untuk pilih semua calon berdasarkan tab aktif
const selectAllCalons = () => {
    const ids = filteredCalons.value.map(calon => calon.id);
    setFieldValue('calons', ids);
};

// Fungsi untuk beralih tab
const switchTab = (tab: 'Ketua' | 'Formatur') => {
    activeTab.value = tab;
    // Reset pilihan calon saat ganti tab
    setFieldValue('calons', []);

    // Reset field yang spesifik tab
    if (tab === 'Ketua') {
        setFieldValue('jumlah_formatur_terpilih', undefined);
        setFieldValue('boleh_tidak_memilih', false);
    } else {
        setFieldValue('boleh_tidak_memilih', undefined);
        // Set default 1 untuk formatur
        setFieldValue('jumlah_formatur_terpilih', 1);
    }
};

const onSubmit = handleSubmit((values) => {
    // Validasi tambahan berdasarkan tab aktif
    if (activeTab.value === 'Formatur') {
        if (!values.jumlah_formatur_terpilih || values.jumlah_formatur_terpilih < 1) {
            toast.error('Jumlah formatur terpilih minimal 1');
            return;
        }

        // Validasi: jumlah formatur terpilih tidak boleh lebih dari jumlah calon yang dipilih
        if (values.jumlah_formatur_terpilih > values.calons.length) {
            toast.error(`Jumlah formatur terpilih (${values.jumlah_formatur_terpilih}) tidak boleh lebih dari jumlah calon yang dipilih (${values.calons.length})`);
            return;
        }
    }

    // Validasi: Pastikan calon yang dipilih sesuai dengan jabatan tab aktif
    const selectedCalons = props.calons.filter(calon => values.calons.includes(calon.id));
    const hasWrongJabatan = selectedCalons.some(calon => calon.jabatan !== activeTab.value);

    if (hasWrongJabatan) {
        toast.error(`Hanya boleh memilih calon dengan jabatan ${activeTab.value}`);
        return;
    }

    formInertia.nama_pemilihan = values.nama_pemilihan;
    formInertia.minimal_kehadiran = values.minimal_kehadiran;
    formInertia.boleh_tidak_memilih = values.boleh_tidak_memilih || false;
    formInertia.jumlah_formatur_terpilih = values.jumlah_formatur_terpilih || 0;
    formInertia.calons = values.calons;

    const submissionPromise = new Promise<{ message: any }>(
        (resolve, reject) => {
            formInertia.post('/pemilihan', {
                onSuccess: () => {
                    resolve({
                        message: 'Data Bilik berhasil disimpan!',
                    });
                    formInertia.reset();
                    resetForm();
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
            return `Gagal: ${errorMsg}`;
        },
    });
});
</script>

<template>

    <Head title="Pemilihan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full">
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <form @submit.prevent="onSubmit" class="space-y-6">
                                <FormField v-slot="{ componentField }" name="nama_pemilihan">
                                    <FormItem>
                                        <FormLabel>Nama Pemilihan</FormLabel>
                                        <FormControl>
                                            <Input type="text" :placeholder="activeTab === 'Ketua'
                                                ? 'Contoh: Pemilihan Ketua Umum Periode 2026'
                                                : 'Contoh: Pemilihan Formatur Periode 2026'"
                                                v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField, errorMessage }" name="minimal_kehadiran">
                                    <FormItem>
                                        <FormLabel>Syarat Minimal Kehadiran
                                            (Pleno)</FormLabel>

                                        <Select :model-value="componentField.modelValue !==
                                                undefined
                                                ? String(
                                                    componentField.modelValue,
                                                )
                                                : undefined
                                            " @update:model-value="
                                                (val) =>
                                                    componentField.onChange(
                                                        Number(val),
                                                    )
                                            ">
                                            <FormControl>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Pilih syarat minimal..." />
                                                </SelectTrigger>
                                            </FormControl>

                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem v-for="option in minimalKehadiranOptions"
                                                        :key="option.value" :value="String(option.value)
                                                            " class="cursor-pointer">
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

                                <!-- Tabs untuk memilih jenis pemilihan -->
                                <div class="border-b">
                                    <nav class="-mb-px flex space-x-8">
                                        <button type="button" @click="switchTab('Ketua')" :class="[
                                            activeTab === 'Ketua'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors',
                                        ]">
                                            Pemilihan Ketua
                                        </button>
                                        <button type="button" @click="switchTab('Formatur')" :class="[
                                            activeTab === 'Formatur'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors',
                                        ]">
                                            Pemilihan Formatur
                                        </button>
                                    </nav>
                                </div>

                                <!-- Konten untuk Pemilihan Ketua -->
                                <div v-if="activeTab === 'Ketua'" class="space-y-6">
                                    <FormField v-slot="{ value, handleChange }" name="boleh_tidak_memilih">
                                        <FormItem
                                            class="flex flex-row items-center space-y-0 space-x-3 rounded-md border p-3">
                                            <FormControl>
                                                <Checkbox :checked="value" @update:checked="handleChange" />
                                            </FormControl>
                                            <div class="space-y-1 leading-none">
                                                <FormLabel class="text-base font-medium">
                                                    Izinkan Abstain/Tidak Memilih
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
                                </div>

                                <!-- Konten untuk Pemilihan Formatur -->
                                <div v-if="activeTab === 'Formatur'" class="space-y-6">
                                    <FormField v-slot="{ componentField, errorMessage }"
                                        name="jumlah_formatur_terpilih">
                                        <FormItem>
                                            <FormLabel>Jumlah Formatur Terpilih</FormLabel>
                                            <div class="space-y-2">
                                                <FormControl>
                                                    <Input type="number" min="1" placeholder="Contoh: 12"
                                                        v-bind="componentField" />
                                                </FormControl>
                                            </div>
                                            <FormMessage>{{
                                                errorMessage
                                                }}</FormMessage>
                                        </FormItem>
                                    </FormField>
                                </div>

                                <h3 class="flex items-center gap-2 border-b pb-2 text-base font-semibold">
                                    <UserPlus class="h-4 w-4" /> Daftar Calon {{ activeTab }}
                                </h3>

                                <div class="space-y-4">
                                    <div class="flex space-x-2">
                                        <Button type="button" variant="secondary" @click="selectAllCalons">
                                            Pilih Semua Calon {{ activeTab }} ({{
                                                filteredCalons.length
                                            }})
                                        </Button>
                                    </div>

                                    <FormField v-slot="{ componentField, errorMessage }" name="calons">
                                        <FormItem>
                                            <FormLabel>Pilih Calon {{ activeTab }} yang
                                                Berpartisipasi</FormLabel>
                                            <div v-if="activeTab === 'Formatur'" class="mb-2">
                                                <p class="text-sm text-gray-500">
                                                    Anda memilih {{ values.calons?.length || 0 }} calon formatur.
                                                    Jumlah formatur terpilih: {{ values.jumlah_formatur_terpilih || 1
                                                    }}.
                                                </p>
                                            </div>
                                            <FormControl>
                                                <CustomMultiSelect :options="calonOptions"
                                                    :placeholder="`Pilih satu atau lebih calon ${activeTab}`"
                                                    :model-value="componentField[
                                                        'modelValue'
                                                        ]
                                                        " @update:model-value="
                                                        componentField.onChange
                                                    " :show-tags="true" />
                                            </FormControl>
                                            <FormMessage>{{ errorMessage }}</FormMessage>
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="flex space-x-4">
                                    <Button type="submit" :disabled="formInertia.processing"
                                        class="!bg-[#A81B2C] !text-white hover:!bg-[#8C1624] focus-visible:!ring-[#A81B2C]">
                                        Buat Pemilihan {{ activeTab }}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="switchTab(activeTab === 'Ketua' ? 'Formatur' : 'Ketua')"
                                    >
                                        Ganti ke Pemilihan {{ activeTab === 'Ketua' ? 'Formatur' : 'Ketua' }}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>
[file content end]