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
import { Plus, Trash2 } from 'lucide-vue-next';
import { useFieldArray, useForm, Field as VeeField } from 'vee-validate'; // Use alias for VeeValidate
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { z } from 'zod'; // Corrected: z from 'zod'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Calon', href: '/calon' },
    { title: 'Buat Calon', href: '/calon/create' },
];

interface Peserta {
    id: string;
    nama: string;
    asal_pimpinan: string;
    pencalonan?: Array<{ jabatan: string }>;
}

interface CalonItem {
    peserta_id: string;
    jabatan: 'Ketua' | 'Formatur';
}

const props = defineProps<{
    pesertas: Peserta[];
    jabatanOptions: Array<'Ketua' | 'Formatur'>;
}>();

const formSchema = z.object({
    calons: z
        .array(
            z.object({
                peserta_id: z
                    .string({
                        required_error: 'Peserta harus dipilih.',
                    })
                    .min(1, 'Peserta wajib dipilih.'),
                jabatan: z.enum(['Ketua', 'Formatur'], {
                    required_error: 'Jabatan calon harus dipilih.',
                }),
            }),
        )
        .min(1, 'Minimal harus ada 1 calon untuk disimpan.'),
});

const formInertia = useInertiaForm('CalonMultipleForm', {
    calons: [] as CalonItem[],
});

const { handleSubmit, setErrors } = useForm({
    validationSchema: toTypedSchema(formSchema),
    initialValues: {
        calons: [{ peserta_id: '', jabatan: 'Ketua' }] as CalonItem[],
    },
});

const { fields, push, remove } = useFieldArray<CalonItem>('calons');

const pesertaOptions = computed(() => {
    return props.pesertas
        .filter(
            (p) =>
                !p.pencalonan?.some((c) =>
                    props.jabatanOptions.includes(c.jabatan as any),
                ),
        )
        .map((p) => ({
            value: p.id,
            label: `${p.nama} (${p.asal_pimpinan})`,
        }));
});

const jabatanSelectOptions = computed(() => {
    return props.jabatanOptions.map((j) => ({
        value: j,
        label: j,
    }));
});

const addCalonForm = () => {
    push({ peserta_id: '', jabatan: 'Ketua' });
};

const onSubmit = handleSubmit(async (values) => {
    formInertia.calons = values.calons;

    setErrors({});

    const submissionPromise = new Promise<{ message: any }>(
        (resolve, reject) => {
            formInertia.post('/calon', {
                onSuccess: () => {
                    resolve({
                        message: 'Data Calon berhasil disimpan!',
                    });

                    fields.value.forEach((_, index) => remove(index));
                    push({ peserta_id: '', jabatan: 'Ketua' });
                },

                onError: (errors) => {
                    setErrors(errors);

                    const errorKey = Object.keys(errors)[0];
                    const firstError = errors[errorKey] as string;

                    reject(
                        firstError || 'Terjadi kesalahan saat menyimpan data.',
                    );
                },
            });
        },
    );

    toast.promise(submissionPromise, {
        loading: 'Sedang memproses dan menyimpan data...',
        success: (data: { message: any }) => {
            return `Berhasil: ${data.message}`;
        },
        error: (errorMsg: any) => {
            return `Gagal: ${errorMsg}`;
        },
    });
});
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
                                class="w-full space-y-8"
                                @submit.prevent="onSubmit"
                            >
                                <div
                                    v-for="(field, index) in fields"
                                    :key="field.key"
                                    class="relative space-y-4 rounded-lg border bg-gray-50/50 p-4"
                                >
                                    <h4
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Calon #{{ index + 1 }}
                                    </h4>

                                    <Button
                                        v-if="fields.length > 1"
                                        type="button"
                                        variant="destructive"
                                        size="icon"
                                        class="absolute top-4 right-4 h-8 w-8"
                                        @click="remove(index)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>

                                    <FormField
                                        :name="`calons.${index}.peserta_id`"
                                    >
                                        <FormItem>
                                            <FormLabel>Pilih Peserta</FormLabel>
                                            <VeeField
                                                :name="`calons.${index}.peserta_id`"
                                                v-slot="{ field }"
                                            >
                                                <Select
                                                    v-bind="field"
                                                    @update:model-value="
                                                        field.onChange
                                                    "
                                                >
                                                    <FormControl>
                                                        <SelectTrigger>
                                                            <SelectValue
                                                                placeholder="Pilih Peserta..."
                                                            />
                                                        </SelectTrigger>
                                                    </FormControl>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectItem
                                                                v-for="peserta in pesertaOptions"
                                                                :key="
                                                                    peserta.value
                                                                "
                                                                :value="
                                                                    peserta.value
                                                                "
                                                            >
                                                                {{
                                                                    peserta.label
                                                                }}
                                                            </SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </VeeField>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>

                                    <FormField
                                        :name="`calons.${index}.jabatan`"
                                    >
                                        <FormItem>
                                            <FormLabel>Jabatan Calon</FormLabel>
                                            <VeeField
                                                :name="`calons.${index}.jabatan`"
                                                v-slot="{ field }"
                                            >
                                                <Select
                                                    v-bind="field"
                                                    @update:model-value="
                                                        field.onChange
                                                    "
                                                >
                                                    <FormControl>
                                                        <SelectTrigger>
                                                            <SelectValue
                                                                placeholder="Pilih Jabatan..."
                                                            />
                                                        </SelectTrigger>
                                                    </FormControl>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectItem
                                                                v-for="jabatan in jabatanSelectOptions"
                                                                :key="
                                                                    jabatan.value
                                                                "
                                                                :value="
                                                                    jabatan.value
                                                                "
                                                            >
                                                                {{
                                                                    jabatan.label
                                                                }}
                                                            </SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </VeeField>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>

                                <div
                                    class="flex flex-col justify-between gap-4 pt-4 sm:flex-row"
                                >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="addCalonForm"
                                        :disabled="formInertia.processing"
                                    >
                                        <Plus class="mr-2 h-4 w-4" /> Tambah
                                        Calon Lagi
                                    </Button>

                                    <div class="flex gap-2">
                                        <Button
                                            type="submit"
                                            :disabled="formInertia.processing"
                                        >
                                            Simpan Semua Calon ({{
                                                fields.length
                                            }})
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            @click="$inertia.visit('/calon')"
                                        >
                                            Batal
                                        </Button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>
