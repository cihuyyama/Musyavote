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
import { Head, router, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { UserPlus } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { z } from 'zod';
import CustomMultiSelect from './CustomMultiSelect.vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pemilihan', href: '/pemilihan' },
    { title: 'Edit Pemilihan', href: '/pemilihan/edit' },
];

const props = defineProps<{
    pemilihan: {
        id: string;
        nama_pemilihan: string;
        minimal_kehadiran: number;
        boleh_tidak_memilih: number; // 0 atau 1
        jumlah_formatur_terpilih: number | null;
        calon: Array<{ // Perhatikan: 'calon' bukan 'calons'
            id: string;
            nama: string;
            jabatan: 'Ketua' | 'Formatur';
            nomor_urut: number;
            asal_pimpinan: string;
            jenis_kelamin: string;
            foto: string | null;
            foto_url: string;
        }>;
    };
}>();

console.log(props);

// Tentukan jenis pemilihan berdasarkan jabatan calon yang dipilih
const activeTab = ref<'Ketua' | 'Formatur'>(() => {
    // Cek jabatan dari calon yang terpilih
    if (props.pemilihan.calon.length > 0) {
        const firstJabatan = props.pemilihan.calon[0].jabatan;
        return firstJabatan;
    }
    // Jika tidak ada calon, default ke Ketua (karena sudah ada calon Ketua di contoh)
    return 'Ketua';
});

// Form schema untuk edit pemilihan
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

// Convert boleh_tidak_memilih dari number ke boolean
const initialBolehTidakMemilih = props.pemilihan.boleh_tidak_memilih === 1 ? true : false;

const formInertia = useInertiaForm<FormData>({
    nama_pemilihan: props.pemilihan.nama_pemilihan,
    minimal_kehadiran: props.pemilihan.minimal_kehadiran,
    boleh_tidak_memilih: initialBolehTidakMemilih,
    jumlah_formatur_terpilih: props.pemilihan.jumlah_formatur_terpilih || 1,
    calons: props.pemilihan.calon.map(calon => calon.id),
});

const { handleSubmit, setFieldValue, values, resetForm } = useVeeForm<
    z.infer<typeof formSchema>
>({
    validationSchema: toTypedSchema(formSchema),
    initialValues: {
        nama_pemilihan: props.pemilihan.nama_pemilihan,
        minimal_kehadiran: props.pemilihan.minimal_kehadiran,
        boleh_tidak_memilih: initialBolehTidakMemilih,
        jumlah_formatur_terpilih: props.pemilihan.jumlah_formatur_terpilih || 1,
        calons: props.pemilihan.calon.map(calon => calon.id),
    },
});

// Watch untuk reset jumlah_formatur_terpilih saat tab Formatur aktif
watch(activeTab, (newTab) => {
    if (newTab === 'Formatur') {
        // Jika belum ada nilai, set ke 1
        if (!values.jumlah_formatur_terpilih || values.jumlah_formatur_terpilih < 1) {
            setFieldValue('jumlah_formatur_terpilih', 1);
        }
    } else {
        // Jika beralih ke Ketua, hapus jumlah_formatur_terpilih
        setFieldValue('jumlah_formatur_terpilih', undefined);
    }

    // Reset pilihan calon saat ganti tab
    setFieldValue('calons', []);
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
    return props.pemilihan.calon.filter(calon => calon.jabatan === activeTab.value);
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

// Fungsi untuk beralih tab (dengan validasi)
const switchTab = (tab: 'Ketua' | 'Formatur') => {
    // Validasi: jangan izinkan ganti tab jika sudah ada calon dengan jabatan berbeda
    if (props.pemilihan.calon.length > 0) {
        const currentJabatan = props.pemilihan.calon[0].jabatan;
        if (currentJabatan !== tab) {
            toast.error(`Tidak dapat mengubah jenis pemilihan dari ${currentJabatan} ke ${tab}. Hapus semua calon terlebih dahulu.`);
            return;
        }
    }

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

// Initialize berdasarkan data yang ada
onMounted(() => {
    // Jika ada jumlah_formatur_terpilih dan calon dengan jabatan Formatur, berarti pemilihan formatur
    if (props.pemilihan.jumlah_formatur_terpilih &&
        props.pemilihan.calon.some(calon => calon.jabatan === 'Formatur')) {
        activeTab.value = 'Formatur';
    } else {
        // Default ke Ketua
        activeTab.value = 'Ketua';
        // Set jumlah_formatur_terpilih ke undefined untuk Ketua
        if (values.jumlah_formatur_terpilih) {
            setFieldValue('jumlah_formatur_terpilih', undefined);
        }
    }

    // Set nilai awal untuk calons berdasarkan tab aktif
    const currentCalonIds = props.pemilihan.calon
        .filter(calon => calon.jabatan === activeTab.value)
        .map(calon => calon.id);

    setFieldValue('calons', currentCalonIds);
});

const onSubmit = handleSubmit((values) => {
    // Validasi: Pastikan semua calon yang dipilih memiliki jabatan yang sama dengan tab aktif
    const selectedCalons = props.pemilihan.calon.filter(calon => values.calons.includes(calon.id));
    const hasWrongJabatan = selectedCalons.some(calon => calon.jabatan !== activeTab.value);

    if (hasWrongJabatan) {
        toast.error(`Semua calon harus memiliki jabatan ${activeTab.value}`);
        return;
    }

    // Validasi tambahan untuk Formatur
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
    } else {
        // Untuk Ketua, set jumlah_formatur_terpilih ke null
        values.jumlah_formatur_terpilih = undefined;
    }

    // Update form inertia
    formInertia.nama_pemilihan = values.nama_pemilihan;
    formInertia.minimal_kehadiran = values.minimal_kehadiran;
    formInertia.boleh_tidak_memilih = values.boleh_tidak_memilih || false;
    formInertia.jumlah_formatur_terpilih = values.jumlah_formatur_terpilih || null;
    formInertia.calons = values.calons;

    const submissionPromise = new Promise<{ message: any }>(
        (resolve, reject) => {
            // Gunakan method PUT dengan post dan _method
            router.post(
                `/pemilihan/${props.pemilihan.id}`,
                {
                    _method: 'put',
                    nama_pemilihan: values.nama_pemilihan,
                    minimal_kehadiran: values.minimal_kehadiran,
                    boleh_tidak_memilih: values.boleh_tidak_memilih || false,
                    jumlah_formatur_terpilih: values.jumlah_formatur_terpilih || null,
                    calons: values.calons,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        resolve({
                            message: 'Data Pemilihan berhasil diperbarui!',
                        });
                    },
                    onError: (errors) => {
                        const firstError = Object.values(errors)[0] as string;
                        reject(firstError || 'Terjadi kesalahan saat validasi.');
                    },
                },
            );
        },
    );

    toast.promise(submissionPromise, {
        loading: 'Sedang memproses perubahan...',
        success: (data: { message: any }) => {
            return `Data berhasil diperbarui! ${data.message}`;
        },
        error: (errorMsg: any) => {
            return `Gagal: ${errorMsg}`;
        },
    });
});

// Fungsi untuk mendapatkan label pemilihan
const getPemilihanTypeLabel = computed(() => {
    if (props.pemilihan.calon.length > 0) {
        const firstCalon = props.pemilihan.calon[0];
        return `Pemilihan ${firstCalon.jabatan}`;
    }
    return 'Pemilihan';
});
</script>

<template>

    <Head :title="`Edit ${getPemilihanTypeLabel}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full">
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <div class="mb-6">
                                <h1 class="text-2xl font-bold text-gray-900">Edit {{ getPemilihanTypeLabel }}</h1>
                                <p class="text-gray-600 mt-1">
                                    ID: {{ pemilihan.id }} • Dibuat: {{ new
                                        Date(pemilihan.created_at).toLocaleDateString('id-ID') }}
                                </p>
                                <p class="text-sm text-gray-500 mt-2">
                                    Jenis pemilihan: <span class="font-medium">{{ activeTab }}</span>
                                    • Jumlah calon: {{ pemilihan.calon.length }}
                                    • Jumlah bilik: {{ pemilihan.biliks?.length || 0 }}
                                </p>
                            </div>

                            <form @submit.prevent="onSubmit" class="space-y-6">
                                <FormField v-slot="{ componentField }" name="nama_pemilihan">
                                    <FormItem>
                                        <FormLabel>Nama Pemilihan</FormLabel>
                                        <FormControl>
                                            <Input type="text" :placeholder="activeTab === 'Ketua'
                                                ? 'Contoh: Pemilihan Ketua Umum Periode 2026'
                                                : 'Contoh: Pemilihan Formatur Periode 2026'" v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField, errorMessage }" name="minimal_kehadiran">
                                    <FormItem>
                                        <FormLabel>Syarat Minimal Kehadiran (Pleno)</FormLabel>
                                        <Select :model-value="String(componentField.modelValue)"
                                            @update:model-value="(val) => componentField.onChange(Number(val))">
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

                                <!-- Tabs untuk jenis pemilihan (disabled jika sudah ada calon) -->
                                <div class="border-b">
                                    <nav class="-mb-px flex space-x-8">
                                        <button type="button" @click="switchTab('Ketua')" :class="[
                                            activeTab === 'Ketua'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors',
                                            pemilihan.calon.length > 0 && pemilihan.calon[0].jabatan === 'Formatur'
                                                ? 'opacity-50 cursor-not-allowed'
                                                : ''
                                        ]"
                                            :disabled="pemilihan.calon.length > 0 && pemilihan.calon[0].jabatan === 'Formatur'">
                                            Pemilihan Ketua
                                            <span
                                                v-if="pemilihan.calon.length > 0 && pemilihan.calon[0].jabatan === 'Ketua'"
                                                class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full">
                                                aktif
                                            </span>
                                        </button>
                                        <button type="button" @click="switchTab('Formatur')" :class="[
                                            activeTab === 'Formatur'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors',
                                            pemilihan.calon.length > 0 && pemilihan.calon[0].jabatan === 'Ketua'
                                                ? 'opacity-50 cursor-not-allowed'
                                                : ''
                                        ]"
                                            :disabled="pemilihan.calon.length > 0 && pemilihan.calon[0].jabatan === 'Ketua'">
                                            Pemilihan Formatur
                                            <span
                                                v-if="pemilihan.calon.length > 0 && pemilihan.calon[0].jabatan === 'Formatur'"
                                                class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full">
                                                aktif
                                            </span>
                                        </button>
                                    </nav>
                                </div>

                                <!-- Info jenis pemilihan saat ini -->
                                <div v-if="pemilihan.calon.length > 0" class="p-3 bg-blue-50 rounded-md">
                                    <p class="text-sm text-blue-700">
                                        <span class="font-medium">Jenis pemilihan saat ini:</span> {{
                                        pemilihan.calon[0].jabatan }}.
                                        {{ pemilihan.calon.length }} calon {{ pemilihan.calon[0].jabatan.toLowerCase()
                                        }} terpilih.
                                    </p>
                                    <p v-if="pemilihan.calon[0].jabatan === 'Formatur' && pemilihan.jumlah_formatur_terpilih"
                                        class="text-sm text-blue-700 mt-1">
                                        Jumlah formatur terpilih: {{ pemilihan.jumlah_formatur_terpilih }}
                                    </p>
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
                                                <p class="text-sm text-gray-500">
                                                    Tentukan berapa banyak calon formatur yang akan terpilih dari daftar
                                                    calon.
                                                </p>
                                            </div>
                                            <FormMessage>{{ errorMessage }}</FormMessage>
                                        </FormItem>
                                    </FormField>
                                </div>

                                <h3 class="flex items-center gap-2 border-b pb-2 text-base font-semibold">
                                    <UserPlus class="h-4 w-4" /> Daftar Calon {{ activeTab }}
                                    <span class="text-sm font-normal text-gray-500 ml-2">
                                        ({{ values.calons?.length || 0 }} dipilih dari {{ filteredCalons.length }}
                                        tersedia)
                                    </span>
                                </h3>

                                <div class="space-y-4">
                                    <div class="flex space-x-2">
                                        <Button type="button" variant="secondary" @click="selectAllCalons">
                                            Pilih Semua Calon {{ activeTab }} ({{
                                                filteredCalons.length
                                            }})
                                        </Button>
                                        <Button type="button" variant="outline" @click="setFieldValue('calons', [])"
                                            v-if="values.calons?.length > 0">
                                            Batalkan Semua
                                        </Button>
                                    </div>

                                    <FormField v-slot="{ componentField, errorMessage }" name="calons">
                                        <FormItem>
                                            <FormLabel>Pilih Calon {{ activeTab }} yang Berpartisipasi</FormLabel>
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
                                                    v-model="values.calons" :show-tags="true" />
                                            </FormControl>
                                            <FormMessage>{{ errorMessage }}</FormMessage>
                                        </FormItem>
                                    </FormField>
                                </div>

                                <div class="flex space-x-4 pt-4 border-t">
                                    <Button type="submit" :disabled="formInertia.processing"
                                        class="!bg-[#A81B2C] !text-white hover:!bg-[#8C1624] focus-visible:!ring-[#A81B2C]">
                                        {{ formInertia.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </Button>
                                    <Button type="button" variant="outline" @click="router.visit('/pemilihan')">
                                        Batal
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