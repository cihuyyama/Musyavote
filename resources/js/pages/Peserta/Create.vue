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
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import Input from '@/components/ui/input/Input.vue';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Peserta', href: '/peserta' },
    { title: 'Buat Peserta', href: '/peserta/create' },
];

const photoPreviewUrl = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const formSchema = z.object({
    nama: z.string({
        required_error: "Nama lengkap wajib diisi",
    }),
    asal_pimpinan: z.string({
        required_error: "Asal pimpinan wajib diisi",
    }),
    jenis_kelamin: z.enum(['L', 'P'], {
        required_error: "Jenis kelamin wajib diisi",
    }),
    file: z.instanceof(File).optional().nullable(),
    status: z.enum(['Aktif', 'Tidak Aktif']).optional().default('Aktif'),
})

type FormData = {
    nama: string;
    asal_pimpinan: string;
    jenis_kelamin: 'L' | 'P';
    file: File | null;
    status: 'Aktif' | 'Tidak Aktif';
};
const formInertia = useInertiaForm<FormData>({
    nama: '',
    asal_pimpinan: '',
    jenis_kelamin: 'L',
    file: null,
    status: 'Aktif',
});

const { isFieldDirty, handleSubmit, setFieldValue } = useVeeForm<z.infer<typeof formSchema>>({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

const onSubmit = handleSubmit((values) => {
    formInertia.nama = values.nama;
    formInertia.asal_pimpinan = values.asal_pimpinan;
    formInertia.jenis_kelamin = values.jenis_kelamin;
    formInertia.status = values.status || 'Aktif';
    const submissionPromise = new Promise<{ message: any }>((resolve, reject) => {
        formInertia.post(("/peserta"), {
            onSuccess: () => {
                resolve({
                    message: 'Data Peserta berhasil disimpan!',
                });

                formInertia.reset();
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
    <Head title="Peserta" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full">
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <form class="w-2/3 space-y-6" @submit.prevent="onSubmit">

                                <div class="flex flex-col space-y-4">
                                    <div class="flex flex-row items-center space-x-4">

                                        <div class="flex-shrink-0">
                                            <div v-if="photoPreviewUrl">
                                                <img :src="photoPreviewUrl" alt="Preview"
                                                    class="h-24 w-24 rounded-full object-cover border-2 border-gray-300" />
                                            </div>
                                            <div v-else>
                                                <div
                                                    class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 border-2 border-dashed border-gray-400">
                                                    No Image
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex-grow">
                                            <FormField v-slot="{ errorMessage }" name="file">
                                                <FormItem>
                                                    <FormLabel>Foto Peserta</FormLabel>
                                                    <FormControl>
                                                        <Input type="file" placeholder="Pilih file" ref="fileInputRef"
                                                            @change="handleFileChange"
                                                            :class="{ 'border-red-500': errorMessage }" />
                                                    </FormControl>
                                                    <FormMessage>{{ errorMessage }}</FormMessage>
                                                </FormItem>
                                            </FormField>
                                        </div>
                                    </div>
                                </div>

                                <FormField v-slot="{ componentField }" name="nama" :validate-on-blur="!isFieldDirty">
                                    <FormItem>
                                        <FormLabel>
                                            Nama Lengkap
                                        </FormLabel>
                                        <FormControl>
                                            <Input type="text" placeholder="shadcn" v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField }" name="asal_pimpinan"
                                    :validate-on-blur="!isFieldDirty">
                                    <FormItem>
                                        <FormLabel>
                                            Asal Pimpinan
                                        </FormLabel>
                                        <FormControl>
                                            <Input type="text" placeholder="shadcn" v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField }" name="jenis_kelamin"
                                    :validate-on-blur="!isFieldDirty">
                                    <FormItem>
                                        <FormLabel>
                                            Jenis Kelamin
                                        </FormLabel>
                                        <FormControl>
                                            <RadioGroup class="flex flex-row space-x-2" v-bind="componentField">
                                                <FormItem class="flex items-center space-y-0 gap-x-1">
                                                    <FormControl>
                                                        <RadioGroupItem value="L" />
                                                    </FormControl>
                                                    <FormLabel class="font-normal">
                                                        Laki-laki
                                                    </FormLabel>
                                                </FormItem>
                                                <FormItem class="flex items-center space-y-0 gap-x-1">
                                                    <FormControl>
                                                        <RadioGroupItem value="P" />
                                                    </FormControl>
                                                    <FormLabel class="font-normal">
                                                        Perempuan
                                                    </FormLabel>
                                                </FormItem>
                                            </RadioGroup>
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField }" name="status" :validate-on-blur="!isFieldDirty">
                                    <FormItem>
                                        <FormLabel>
                                            Status (optional)
                                        </FormLabel>
                                        <FormControl>
                                            <RadioGroup class="flex flex-row space-x-2" v-bind="componentField"
                                                defaultValue="Aktif">
                                                <FormItem class="flex items-center space-y-0 gap-x-1">
                                                    <FormControl>
                                                        <RadioGroupItem value="Aktif" />
                                                    </FormControl>
                                                    <FormLabel class="font-normal">
                                                        Aktif
                                                    </FormLabel>
                                                </FormItem>
                                                <FormItem class="flex items-center space-y-0 gap-x-1">
                                                    <FormControl>
                                                        <RadioGroupItem value="Tidak Aktif" />
                                                    </FormControl>
                                                    <FormLabel class="font-normal">
                                                        Tidak Aktif
                                                    </FormLabel>
                                                </FormItem>
                                            </RadioGroup>
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <Button type="submit">
                                    Simpan
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>