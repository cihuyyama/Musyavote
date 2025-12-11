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
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'; // Ganti checkbox dengan radio
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm as useVeeForm } from 'vee-validate';
import { toast } from 'vue-sonner';
import { z } from 'zod';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Kehadiran', href: '/admin-presensi' },
    { title: 'Buat Admin', href: '/admin-presensi/create' },
];

const formSchema = z.object({
    nama: z.string({
        required_error: 'Nama lengkap wajib diisi',
    }),
    username: z.string({
        required_error: 'Username wajib diisi',
    }),
    password: z.string({
        required_error: 'Password wajib diisi',
    }).min(6, 'Password minimal 6 karakter'),
    password_confirmation: z.string({
        required_error: 'Konfirmasi password wajib diisi',
    }),
    pleno_akses: z.array(z.number()).length(1, 'Harus memilih satu pleno'), // Ubah validasi
}).refine((data) => data.password === data.password_confirmation, {
    message: "Password tidak cocok",
    path: ["password_confirmation"],
});

type FormData = {
    nama: string;
    username: string;
    password: string;
    password_confirmation: string;
    pleno_akses: number[];
};

// Fungsi untuk mengonversi array ke single value dan sebaliknya
const getSelectedPleno = (plenoArray: number[]): number | null => {
    return plenoArray.length > 0 ? plenoArray[0] : null;
};

const setSelectedPleno = (pleno: number | null): number[] => {
    return pleno ? [pleno] : [];
};

const formInertia = useInertiaForm<FormData>({
    nama: '',
    username: '',
    password: '',
    password_confirmation: '',
    pleno_akses: [],
});

const { isFieldDirty, handleSubmit } = useVeeForm({
    validationSchema: toTypedSchema(formSchema),
    initialValues: {
        nama: '',
        username: '',
        password: '',
        password_confirmation: '',
        pleno_akses: [],
    },
});

const onSubmit = handleSubmit((values) => {
    formInertia.nama = values.nama;
    formInertia.username = values.username;
    formInertia.password = values.password;
    formInertia.password_confirmation = values.password_confirmation;
    formInertia.pleno_akses = values.pleno_akses;

    console.log(formInertia);

    const submissionPromise = new Promise<{ message: any }>(
        (resolve, reject) => {
            formInertia.post('/admin-presensi', {
                onSuccess: () => {
                    resolve({
                        message: 'Data Admin berhasil disimpan!',
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
        loading: 'Sedang memproses data...',
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

    <Head title="Buat Admin Kehadiran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full">
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <form class="w-2/3 space-y-6" @submit.prevent="onSubmit">
                                <FormField v-slot="{ componentField }" name="nama" :validate-on-blur="!isFieldDirty">
                                    <FormItem>
                                        <FormLabel>Nama Lengkap</FormLabel>
                                        <FormControl>
                                            <Input type="text" placeholder="Masukkan nama lengkap"
                                                v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField }" name="username"
                                    :validate-on-blur="!isFieldDirty">
                                    <FormItem>
                                        <FormLabel>Username</FormLabel>
                                        <FormControl>
                                            <Input type="text" placeholder="Masukkan username"
                                                v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <!-- Field Akses Pleno - Radio Button (Single Selection) -->
                                <FormField name="pleno_akses" v-slot="{ value, handleChange }">
                                    <FormItem>
                                        <div class="space-y-4">
                                            <FormLabel>Akses Pleno</FormLabel>

                                            <RadioGroup :model-value="getSelectedPleno(value)"
                                                @update:model-value="(newValue) => handleChange(setSelectedPleno(Number(newValue)))"
                                                class="grid grid-cols-2 gap-3">
                                                <div v-for="pleno in [1, 2, 3, 4]" :key="pleno"
                                                    class="flex items-center space-x-2 border rounded-lg p-3 hover:bg-gray-50 cursor-pointer">
                                                    <RadioGroupItem :id="`pleno-${pleno}`" :value="pleno"
                                                        class="mt-1" />

                                                    <FormLabel :for="`pleno-${pleno}`"
                                                        class="text-sm font-medium leading-none cursor-pointer flex-1">
                                                        <div class="font-semibold">Pleno {{ pleno }}</div>
                                                        <div class="text-xs text-gray-500">Sesi {{ pleno }}</div>
                                                    </FormLabel>
                                                </div>
                                            </RadioGroup>

                                            <p class="text-sm text-gray-500">
                                                Pilih satu pleno yang dapat diakses oleh admin ini
                                            </p>
                                        </div>

                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField }" name="password"
                                    :validate-on-blur="!isFieldDirty">
                                    <FormItem>
                                        <FormLabel>Password</FormLabel>
                                        <FormControl>
                                            <Input type="password" placeholder="Masukkan password"
                                                v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField v-slot="{ componentField }" name="password_confirmation"
                                    :validate-on-blur="!isFieldDirty">
                                    <FormItem>
                                        <FormLabel>Konfirmasi Password</FormLabel>
                                        <FormControl>
                                            <Input type="password" placeholder="Konfirmasi password"
                                                v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <Button type="submit"
                                    class="!bg-[#A81B2C] !text-white hover:!bg-[#8C1624] focus-visible:!ring-[#A81B2C]">
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