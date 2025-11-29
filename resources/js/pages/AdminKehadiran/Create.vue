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
}).refine((data) => data.password === data.password_confirmation, {
    message: "Password tidak cocok",
    path: ["password_confirmation"],
});

type FormData = {
    nama: string;
    username: string;
    password: string;
    password_confirmation: string;
};

const formInertia = useInertiaForm<FormData>({
    nama: '',
    username: '',
    password: '',
    password_confirmation: '',
});

const { isFieldDirty, handleSubmit } = useVeeForm({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

const onSubmit = handleSubmit((values) => {
    formInertia.nama = values.nama;
    formInertia.username = values.username;
    formInertia.password = values.password;
    formInertia.password_confirmation = values.password_confirmation;

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
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full"
                >
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <form
                                class="w-2/3 space-y-6"
                                @submit.prevent="onSubmit"
                            >
                                <FormField
                                    v-slot="{ componentField }"
                                    name="nama"
                                    :validate-on-blur="!isFieldDirty"
                                >
                                    <FormItem>
                                        <FormLabel>Nama Lengkap</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="text"
                                                placeholder="Masukkan nama lengkap"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField
                                    v-slot="{ componentField }"
                                    name="username"
                                    :validate-on-blur="!isFieldDirty"
                                >
                                    <FormItem>
                                        <FormLabel>Username</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="text"
                                                placeholder="Masukkan username"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField
                                    v-slot="{ componentField }"
                                    name="password"
                                    :validate-on-blur="!isFieldDirty"
                                >
                                    <FormItem>
                                        <FormLabel>Password</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="password"
                                                placeholder="Masukkan password"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField
                                    v-slot="{ componentField }"
                                    name="password_confirmation"
                                    :validate-on-blur="!isFieldDirty"
                                >
                                    <FormItem>
                                        <FormLabel>Konfirmasi Password</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="password"
                                                placeholder="Konfirmasi password"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <Button type="submit">Simpan</Button>
                            </form>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>