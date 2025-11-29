<script setup lang="ts">
import { Head, router, useForm as useInertiaForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import { toast } from 'vue-sonner';
import z from 'zod';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm as useVeeForm } from 'vee-validate';


const props = defineProps<{
    bilik: {
        id: number;
        nama: string;
    };
    pemilihan: {
        id: number;
        nama_pemilihan: string;
        boleh_tidak_memilih: boolean;
        jumlah_formatur_terpilih: number;
        minimal_kehadiran: number;
    };
}>();

console.log(props);

const formSchema = z.object({
    kode_unik: z.string({
        required_error: 'Kode unik wajib diisi',
    }),
    password: z.string({
        required_error: 'Password wajib diisi',
    }),
});

type FormData = {
    kode_unik: string;
    password: string;
};
const formInertia = useInertiaForm<FormData>({
    kode_unik: '',
    password: '',
});

const { handleSubmit } = useVeeForm<
    z.infer<typeof formSchema>
>({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

const isLoading = ref(false);

const onSubmit = handleSubmit((values) => {
    isLoading.value = true;
    console.log(values);
    formInertia.kode_unik = values.kode_unik;
    formInertia.password = values.password;

    formInertia.post("voting/verify", {
        onSuccess: () => {
            // Akan redirect ke halaman calon
        },
        onError: (errors) => {
            if (errors.kode_unik) {
                toast.error(errors.kode_unik);
            }
            if (errors.password) {
                toast.error(errors.password);
            }
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
});

const logoutAction = async () => {
    await router.post('/bilik/logout');
};
</script>

<template>

    <Head title="Voting - Verifikasi Peserta" />
    <div class="flex flex-col">
        <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 flex-col">
            <Card class="w-full max-w-md">
                <CardContent class="pt-6">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Voting {{ pemilihan?.nama_pemilihan }}</h1>
                        <span class="text-md font-bold text-gray-900">{{ bilik?.nama }}</span>
                        <p class="text-gray-600 mt-2">Masukkan kode unik dan password Anda</p>
                    </div>

                    <form @submit.prevent="onSubmit" class="space-y-4">
                        <FormField v-slot="{ componentField }" name="kode_unik">
                            <FormItem>
                                <FormLabel>ID</FormLabel>
                                <FormControl>
                                    <Input type="text" placeholder="Contoh: PST001" v-bind="componentField"
                                        :disabled="isLoading" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <FormField v-slot="{ componentField }" name="password">
                            <FormItem>
                                <FormLabel>Password</FormLabel>
                                <FormControl>
                                    <Input type="password" placeholder="Masukkan password" v-bind="componentField"
                                        :disabled="isLoading" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <Button type="submit" class="w-full" :disabled="isLoading">
                            <span v-if="isLoading">Memverifikasi...</span>
                            <span v-else>Verifikasi & Lanjutkan</span>
                        </Button>
                    </form>
                    <Button variant="destructive" class="flex items-center gap-1 w-full mt-3 cursor-pointer" @click="logoutAction">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>

</template>