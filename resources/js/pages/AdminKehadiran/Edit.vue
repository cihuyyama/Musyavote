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
import { Head, router, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import { toast } from 'vue-sonner';
import { z } from 'zod';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Kehadiran',
        href: '/admin-presensi',
    },
    {
        title: 'Edit Admin',
        href: '/admin-presensi/edit',
    },
];

const props = defineProps<{
    admin: {
        id: string;
        nama: string;
        username: string;
    };
}>();

const formSchema = z.object({
    nama: z.string({
        required_error: 'Nama lengkap wajib diisi',
    }),
    username: z.string({
        required_error: 'Username wajib diisi',
    }),
    password: z.string().min(6, 'Password minimal 6 karakter').optional().or(z.literal('')),
    password_confirmation: z.string().optional().or(z.literal('')),
}).refine((data) => {
    if (data.password && data.password !== data.password_confirmation) {
        return false;
    }
    return true;
}, {
    message: "Password tidak cocok",
    path: ["password_confirmation"],
});

type FormData = {
    nama: string;
    username: string;
    password?: string;
    password_confirmation?: string;
};

const formInertia = useInertiaForm<FormData>({
    nama: props.admin.nama,
    username: props.admin.username,
    password: '',
    password_confirmation: '',
});

const { isFieldDirty, handleSubmit } = useForm({
    validationSchema: toTypedSchema(formSchema),
    initialValues: {
        nama: props.admin.nama,
        username: props.admin.username,
        password: '',
        password_confirmation: '',
    },
});

const onSubmit = handleSubmit((values) => {
    const submissionPromise = new Promise<{ message: any }>(
        (resolve, reject) => {
            router.post(
                `/admin-presensi/${props.admin.id}`,
                {
                    ...values,
                    _method: 'put',
                },
                {
                    onSuccess: () => {
                        resolve({
                            message: 'Data Admin berhasil diperbarui!',
                        });
                        formInertia.reset();
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
        loading: 'Sedang memproses data...',
        success: (data: { message: any }) => {
            return `Data berhasil diperbarui! ${data.message}`;
        },
        error: (errorMsg: any) => {
            return `Gagal: ${errorMsg}`;
        },
    });
});
</script>

<template>
    <Head title="Edit Admin Kehadiran" />
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
                                        <FormLabel>Password (Kosongkan jika tidak ingin mengubah)</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="password"
                                                placeholder="Masukkan password baru"
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
                                                placeholder="Konfirmasi password baru"
                                                v-bind="componentField"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <Button type="submit">Perbarui</Button>
                            </form>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>