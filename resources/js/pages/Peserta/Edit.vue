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
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import z from 'zod';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Peserta',
        href: '/peserta',
    },
    {
        title: 'Edit Peserta',
        href: '/peserta/edit',
    },
];

const props = defineProps<{
    peserta: {
        id: string;
        nama: string;
        foto: string | null;
        asal_pimpinan: string;
        jenis_kelamin: 'L' | 'P';
        file: string | null;
        status?: 'Aktif' | 'Tidak Aktif';
    };
}>();

const photoPreviewUrl = ref<string | null>(
    props.peserta.foto ? `/storage/${props.peserta.foto}` : null,
);
const fileInputRef = ref<HTMLInputElement | null>(null);

const formSchema = z.object({
    nama: z.string({
        required_error: 'Nama lengkap wajib diisi',
    }),
    asal_pimpinan: z.string({
        required_error: 'Asal pimpinan wajib diisi',
    }),
    jenis_kelamin: z.enum(['L', 'P'], {
        required_error: 'Jenis kelamin wajib diisi',
    }),
    file: z.instanceof(File).optional().nullable(),
    status: z.enum(['Aktif', 'Tidak Aktif']).optional(),
});

type FormData = {
    nama: string;
    asal_pimpinan: string;
    jenis_kelamin: 'L' | 'P';
    file: File | null;
    status: 'Aktif' | 'Tidak Aktif';
};
const formInertia = useInertiaForm<FormData>({
    nama: props.peserta.nama,
    asal_pimpinan: props.peserta.asal_pimpinan,
    jenis_kelamin: props.peserta.jenis_kelamin,
    file: null,
    status: props.peserta.status || 'Aktif',
});

const { isFieldDirty, handleSubmit, setFieldValue } = useForm({
    validationSchema: toTypedSchema(formSchema),
    initialValues: {
        nama: props.peserta.nama,
        asal_pimpinan: props.peserta.asal_pimpinan,
        jenis_kelamin: props.peserta.jenis_kelamin,
        status: props.peserta.status,
    },
});

const onSubmit = handleSubmit((values) => {
    const submissionPromise = new Promise<{ message: any }>(
        (resolve, reject) => {
            router.post(
                `/peserta/${props.peserta.id}`,
                {
                    ...values,
                    file: formInertia.file || null,
                    _method: 'put',
                },
                {
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
                        reject(
                            firstError || 'Terjadi kesalahan saat validasi.',
                        );
                    },
                },
            );
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
                                        <div class="flex-shrink-0">
                                            <div v-if="photoPreviewUrl">
                                                <img
                                                    :src="photoPreviewUrl"
                                                    alt="Preview"
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

                                        <div class="flex-grow">
                                            <FormField
                                                v-slot="{ errorMessage }"
                                                name="file"
                                            >
                                                <FormItem>
                                                    <FormLabel
                                                        >Foto Peserta</FormLabel
                                                    >
                                                    <FormControl>
                                                        <Input
                                                            type="file"
                                                            placeholder="Pilih file"
                                                            ref="fileInputRef"
                                                            @change="
                                                                handleFileChange
                                                            "
                                                            :class="{
                                                                'border-red-500':
                                                                    errorMessage,
                                                            }"
                                                        />
                                                    </FormControl>
                                                    <FormMessage>{{
                                                        errorMessage
                                                    }}</FormMessage>
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
                                        <FormLabel> Nama Lengkap </FormLabel>
                                        <FormControl>
                                            <Input
                                                type="text"
                                                placeholder="shadcn"
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
                                        <FormLabel> Asal Pimpinan </FormLabel>
                                        <FormControl>
                                            <Input
                                                type="text"
                                                placeholder="shadcn"
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
                                        <FormLabel> Jenis Kelamin </FormLabel>
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

                                <FormField
                                    v-slot="{ componentField }"
                                    name="status"
                                    :validate-on-blur="!isFieldDirty"
                                >
                                    <FormItem>
                                        <FormLabel>
                                            Status (optional)
                                        </FormLabel>
                                        <FormControl>
                                            <RadioGroup
                                                class="flex flex-row space-x-2"
                                                v-bind="componentField"
                                                defaultValue="Aktif"
                                            >
                                                <FormItem
                                                    class="flex items-center space-y-0 gap-x-1"
                                                >
                                                    <FormControl>
                                                        <RadioGroupItem
                                                            value="Aktif"
                                                        />
                                                    </FormControl>
                                                    <FormLabel
                                                        class="font-normal"
                                                    >
                                                        Aktif
                                                    </FormLabel>
                                                </FormItem>
                                                <FormItem
                                                    class="flex items-center space-y-0 gap-x-1"
                                                >
                                                    <FormControl>
                                                        <RadioGroupItem
                                                            value="Tidak Aktif"
                                                        />
                                                    </FormControl>
                                                    <FormLabel
                                                        class="font-normal"
                                                    >
                                                        Tidak Aktif
                                                    </FormLabel>
                                                </FormItem>
                                            </RadioGroup>
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <Button type="submit"> Simpan </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>
