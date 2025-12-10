<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import Input from '@/components/ui/input/Input.vue';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
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
import { useForm as useVeeForm } from 'vee-validate';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { z } from 'zod';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Calon', href: '/calon' },
    { title: 'Buat Calon', href: '/calon/create' },
];

const photoPreviewUrl = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const formSchema = z.object({
    nama: z.string({
        required_error: 'Nama calon wajib diisi',
    }),
    asal_pimpinan: z.string({
        required_error: 'Asal pimpinan wajib diisi',
    }),
    jenis_kelamin: z.enum(['L', 'P'], {
        required_error: 'Jenis kelamin wajib diisi',
    }),
    nomor_urut: z.coerce.number({
        required_error: 'Nomor urut wajib diisi',
    }).min(1, 'Nomor urut minimal 1'),
    jabatan: z.enum(['Ketua', 'Formatur'], {
        required_error: 'Jabatan wajib dipilih',
    }),
    file: z.instanceof(File).optional().nullable(),
});

type FormData = {
    nama: string;
    asal_pimpinan: string;
    jenis_kelamin: 'L' | 'P';
    nomor_urut: number;
    jabatan: 'Ketua' | 'Formatur';
    file: File | null;
};

const formInertia = useInertiaForm<FormData>({
    nama: '',
    asal_pimpinan: '',
    jenis_kelamin: 'L',
    nomor_urut: 1,
    jabatan: 'Formatur',
    file: null,
});

const { isFieldDirty, handleSubmit, setFieldValue } = useVeeForm<
    z.infer<typeof formSchema>
>({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

const onSubmit = handleSubmit((values) => {
    formInertia.nama = values.nama;
    formInertia.asal_pimpinan = values.asal_pimpinan;
    formInertia.jenis_kelamin = values.jenis_kelamin;
    formInertia.nomor_urut = values.nomor_urut;
    formInertia.jabatan = values.jabatan;
    
    const submissionPromise = new Promise<{ message: any }>(
        (resolve, reject) => {
            formInertia.post('/calon', {
                onSuccess: () => {
                    resolve({
                        message: 'Data Calon berhasil disimpan!',
                    });

                    formInertia.reset();
                    formInertia.nomor_urut = 1; // Reset ke default
                    formInertia.jabatan = 'Formatur'; // Reset ke default
                    photoPreviewUrl.value = null;
                    if (fileInputRef.value) {
                        fileInputRef.value.value = '';
                    }
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

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0] || null;

    if (file) {
        formInertia.file = file;
        setFieldValue('file', file);

        const reader = new FileReader();
        reader.onload = (event) => {
            photoPreviewUrl.value = event.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        formInertia.file = null;
        setFieldValue('file', null);
        photoPreviewUrl.value = null;
    }
};
</script>

<template>
    <Head title="Calon" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full"
                >
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <form
                                class="w-2/3 space-y-6"
                                @submit.prevent="onSubmit"
                            >
                                <div class="flex flex-col space-y-4">
                                    <div
                                        class="flex flex-row items-center space-x-4"
                                    >
                                        <div class="shrink-0">
                                            <div v-if="photoPreviewUrl">
                                                <img
                                                    :src="photoPreviewUrl"
                                                    alt="Preview Foto Calon"
                                                    class="h-24 w-24 rounded-full border-2 border-gray-300 object-cover"
                                                />
                                            </div>
                                            <div v-else>
                                                <div
                                                    class="flex h-24 w-24 items-center justify-center rounded-full border-2 border-dashed border-gray-400 bg-gray-200 text-gray-500"
                                                >
                                                    No Image
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grow">
                                            <FormField
                                                v-slot="{ errorMessage }"
                                                name="file"
                                            >
                                                <FormItem>
                                                    <FormLabel>Foto Calon</FormLabel>
                                                    <FormControl>
                                                        <Input
                                                            type="file"
                                                            placeholder="Pilih file foto"
                                                            ref="fileInputRef"
                                                            @change="handleFileChange"
                                                            :class="{
                                                                'border-red-500': errorMessage,
                                                            }"
                                                            accept="image/*"
                                                        />
                                                    </FormControl>
                                                    <FormMessage>{{
                                                        errorMessage
                                                    }}</FormMessage>
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        Upload foto calon (ukuran disarankan: 300x300 px)
                                                    </p>
                                                </FormItem>
                                            </FormField>
                                        </div>
                                    </div>
                                </div>

                                <FormField
                                    v-slot="{ componentField }"
                                    name="nama"
                                    :validate-on-blur="!isFieldDirty"
                                >
                                    <FormItem>
                                        <FormLabel>Nama Calon</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="text"
                                                placeholder="Masukkan nama lengkap calon"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField
                                    v-slot="{ componentField }"
                                    name="asal_pimpinan"
                                    :validate-on-blur="!isFieldDirty"
                                >
                                    <FormItem>
                                        <FormLabel>Asal Pimpinan</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="text"
                                                placeholder="Masukkan asal pimpinan"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField
                                    v-slot="{ componentField }"
                                    name="jenis_kelamin"
                                    :validate-on-blur="!isFieldDirty"
                                >
                                    <FormItem>
                                        <FormLabel>Jenis Kelamin</FormLabel>
                                        <FormControl>
                                            <RadioGroup
                                                class="flex flex-row space-x-2"
                                                v-bind="componentField"
                                            >
                                                <FormItem
                                                    class="flex items-center space-y-0 gap-x-1"
                                                >
                                                    <FormControl>
                                                        <RadioGroupItem
                                                            value="L"
                                                        />
                                                    </FormControl>
                                                    <FormLabel
                                                        class="font-normal"
                                                    >
                                                        Laki-laki
                                                    </FormLabel>
                                                </FormItem>
                                                <FormItem
                                                    class="flex items-center space-y-0 gap-x-1"
                                                >
                                                    <FormControl>
                                                        <RadioGroupItem
                                                            value="P"
                                                        />
                                                    </FormControl>
                                                    <FormLabel
                                                        class="font-normal"
                                                    >
                                                        Perempuan
                                                    </FormLabel>
                                                </FormItem>
                                            </RadioGroup>
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <div class="grid grid-cols-2 gap-4">
                                    <FormField
                                        v-slot="{ componentField }"
                                        name="nomor_urut"
                                        :validate-on-blur="!isFieldDirty"
                                    >
                                        <FormItem>
                                            <FormLabel>Nomor Urut</FormLabel>
                                            <FormControl>
                                                <Input
                                                    type="number"
                                                    min="1"
                                                    placeholder="1"
                                                    v-bind="componentField"
                                                />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>

                                    <FormField
                                        v-slot="{ componentField, errorMessage }"
                                        name="jabatan"
                                        :validate-on-blur="!isFieldDirty"
                                    >
                                        <FormItem>
                                            <FormLabel>Jabatan</FormLabel>
                                            <Select
                                                :model-value="componentField.modelValue"
                                                @update:model-value="componentField.onChange"
                                            >
                                                <FormControl>
                                                    <SelectTrigger>
                                                        <SelectValue
                                                            placeholder="Pilih jabatan"
                                                        />
                                                    </SelectTrigger>
                                                </FormControl>

                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem
                                                            value="Ketua"
                                                            class="cursor-pointer"
                                                        >
                                                            Ketua
                                                        </SelectItem>
                                                        <SelectItem
                                                            value="Formatur"
                                                            class="cursor-pointer"
                                                        >
                                                            Formatur
                                                        </SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                            <FormMessage>{{ errorMessage }}</FormMessage>
                                        </FormItem>
                                    </FormField>
                                </div>

<Button 
    type="submit"
    :disabled="formInertia.processing"
    class="bg-[#A81B2C] hover:bg-[#8c1624] text-white"
>
    {{ formInertia.processing ? 'Menyimpan...' : 'Simpan Calon' }}
</Button>
                            </form>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>