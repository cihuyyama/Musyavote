<script setup lang="ts">
import { Head, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm as useVeeForm } from 'vee-validate';
import * as z from 'zod';

// Shadcn Components
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';

// --- ZOD SCHEMA ---
const formSchema = z.object({
    username: z.string().min(1, 'Username wajib diisi.'),
    password: z.string().min(6, 'Password minimal 6 karakter.'),
    remember: z.boolean().default(false).optional(),
});

type FormData = z.infer<typeof formSchema>;

// --- INERTIA FORM ---
const formInertia = useInertiaForm<FormData>({
    username: '',
    password: '',
    remember: false,
});

// --- VEE-VALIDATE FORM ---
const { handleSubmit } = useVeeForm<FormData>({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

// --- LOGIKA SUBMIT ---
const onSubmit = handleSubmit((values) => {
    // Sinkronisasi data ke Inertia form
    formInertia.username = values.username;
    formInertia.password = values.password;
    formInertia.remember = values.remember;

    // Kirim request login ke route BilikAuthController@login
    formInertia.post('login', {
        onFinish: () => {
            formInertia.reset('password');
        },
    });
});
</script>

<template>
    <Head title="Login Bilik Pemilihan" />

    <div class="flex min-h-screen items-center justify-center bg-gray-100">
        <Card class="w-full max-w-sm">
            <CardHeader class="space-y-1">
                <CardTitle class="text-2xl">Akses Bilik Pemilihan</CardTitle>
                <CardDescription>
                    Masukkan kredensial Bilik untuk memulai sesi pemilihan.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="$page.props.errors.username"
                    class="mb-4 text-sm font-medium text-red-600"
                >
                    {{ $page.props.errors.username }}
                </div>

                <form @submit.prevent="onSubmit" class="flex flex-col gap-6">
                    <FormField
                        v-slot="{ componentField, errorMessage }"
                        name="username Bilik"
                    >
                        <FormItem>
                            <FormLabel>Username</FormLabel>
                            <FormControl>
                                <Input
                                    type="text"
                                    placeholder="Masukkan username"
                                    v-bind="componentField"
                                    :class="{ 'border-red-500': errorMessage }"
                                    autocomplete="username"
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField
                        v-slot="{ componentField, errorMessage }"
                        name="password"
                    >
                        <FormItem>
                            <FormLabel>Password</FormLabel>
                            <FormControl>
                                <Input
                                    type="password"
                                    placeholder="Masukkan password"
                                    v-bind="componentField"
                                    :class="{ 'border-red-500': errorMessage }"
                                    autocomplete="current-password"
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ value, handleChange }" name="remember">
                        <FormItem
                            class="flex flex-row items-start space-y-0 space-x-3"
                        >
                            <FormControl>
                                <Checkbox
                                    :checked="value"
                                    @update:checked="handleChange"
                                />
                            </FormControl>
                            <div class="space-y-1 leading-none">
                                <FormLabel class="font-normal">
                                    Ingat saya
                                </FormLabel>
                            </div>
                        </FormItem>
                    </FormField>

                    <Button
                        type="submit"
                        :disabled="formInertia.processing"
                        class="w-full"
                    >
                        {{ formInertia.processing ? 'Memproses...' : 'Login' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
