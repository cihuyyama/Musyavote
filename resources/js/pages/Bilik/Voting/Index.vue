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
import { ShieldCheck, User, Lock, LogOut, ArrowRight, Vote } from 'lucide-vue-next';

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

    <Head title="Verifikasi - DPD IMM DIY" />

    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-6">
        <!-- Card Verifikasi -->
        <div class="w-full max-w-md">
            <Card class="border border-gray-200 shadow-sm">
                <CardContent class="p-">
                    <!-- Card Header -->
                    <div class="text-center mb-4">
                        <div class="inline-flex items-center justify-center mb-4">
                            <img src="https://immdiy.or.id/wp-content/uploads/2020/07/new-logo-imm-large.png"
                                alt="Logo IMM DIY" class="h-16 object-contain" />
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900">Sistem Pemilihan Suara</h2>
                        <p class="text-gray-600 mt-2">Masukkan kredensial Anda</p>

                        <!-- Info Bilik -->
                        <div
                            class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-full text-sm text-gray-700">
                            <Vote :size="14" />
                            <span>Bilik: {{ bilik?.nama }}</span>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="onSubmit" class="space-y-6">
                        <!-- ID Peserta -->
                        <FormField v-slot="{ componentField, errors }" name="kode_unik">
                            <FormItem>
                                <FormLabel class="text-gray-700 font-medium mb-2 flex items-center gap-2">
                                    <User :size="14" />
                                    ID Peserta
                                </FormLabel>
                                <FormControl>
                                    <Input type="text" placeholder="Masukkan ID peserta" v-bind="componentField"
                                        :disabled="isLoading" :class="errors.length ? 'border-red-300' : ''"
                                        class="h-11" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <!-- Password -->
                        <FormField v-slot="{ componentField, errors }" name="password">
                            <FormItem>
                                <FormLabel class="text-gray-700 font-medium mb-2 flex items-center gap-2">
                                    <Lock :size="14" />
                                    Password
                                </FormLabel>
                                <FormControl>
                                    <Input type="password" placeholder="Masukkan password" v-bind="componentField"
                                        :disabled="isLoading" :class="errors.length ? 'border-red-300' : ''"
                                        class="h-11" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <Button type="submit" :disabled="isLoading" class="w-full bg-[#A81B2C] hover:bg-[#8c1624] text-white" >
                                <template v-if="isLoading">
                                    <span class="flex items-center justify-center gap-2">
                                        <div
                                            class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin">
                                        </div>
                                        Memverifikasi...
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="flex items-center justify-center gap-2">
                                        Lanjutkan
                                        <ArrowRight :size="16" />
                                    </span>
                                </template>
                            </Button>
                        </div>
                    </form>

                    <!-- Logout Button -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <Button @click="logoutAction" variant="outline"
                            class="w-full h-10 text-gray-600 hover:text-red-600 hover:border-red-200">
                            <LogOut :size="16" class="mr-2" />
                            Keluar dari Bilik
                        </Button>
                    </div>

                    <!-- Footer Note -->
                    <div class="mt-4 text-center">
                        <p class="text-xs text-gray-500">
                            Sistem ini menjamin kerahasiaan suara Anda
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Copyright -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-500">
                    &copy; {{ new Date().getFullYear() }} DPD IMM DIY
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Tambahkan efek hover yang smooth */
button {
    transition: all 0.2s ease;
}

input:focus {
    outline: none;
    ring: 2px;
    ring-color: #3b82f6;
}
</style>