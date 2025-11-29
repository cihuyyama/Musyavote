<script setup lang="ts">
import QRScanner from '@/components/QRScanner.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Head, router, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm as useVeeForm } from 'vee-validate';
import { ref } from 'vue';
import z from 'zod';

const props = defineProps<{
    admin: {
        nama: string;
        username: string;
    };
    peserta: Array<{
        id: number;
        kode_unik: string;
        nama: string;
        asal_pimpinan: string;
        jenis_kelamin: string;
        status: string;
        password_plain: string;
        foto?: string;
        kehadiran: {
            pleno_1: number;
            pleno_2: number;
            pleno_3: number;
            pleno_4: number;
        };
    }>;
    totalPeserta: number;
    kehadiranStats: {
        pleno_1: number;
        pleno_2: number;
        pleno_3: number;
        pleno_4: number;
    };
}>();

console.log(props);

interface QRData {
    kode_unik: string;
    nama: string;
    asal_pimpinan: string;
    jenis_kelamin: string;
    status: string;
    password_plain: string;
    foto?: string;
    timestamp: string;
}

// State untuk QR Scanner
const showQRScanner = ref(false);
const scanResult = ref<any>(null);
const scannedPeserta = ref<QRData | null>(null);
const showPresensiDialog = ref(false);
const selectedPleno = ref<number | null>(null);
const isProcessingPresensi = ref(false);
const isScanning = ref(false);

// Handle QR Code Scan
const handleQRScan = async (
    qrData: string,
): Promise<{ success: boolean; message: string; data?: any }> => {
    try {
        isScanning.value = true;

        let parsedData: QRData;

        // Parse JSON data dari QR code
        try {
            parsedData = JSON.parse(qrData);
        } catch (e) {
            console.error('Error parsing QR data:', e);
            return {
                success: false,
                message: 'Format QR Code tidak valid',
            };
        }

        // Validasi data yang diperlukan
        if (!parsedData.kode_unik || !parsedData.nama) {
            return {
                success: false,
                message: 'Data peserta tidak lengkap dalam QR Code'
            };
        }

        // Simpan data peserta yang discan
        scannedPeserta.value = parsedData;

        // Tampilkan dialog presensi dengan kartu peserta
        showPresensiDialog.value = true;
        showQRScanner.value = false;

        return {
            success: true,
            message: 'Data peserta berhasil ditemukan',
            data: scannedPeserta.value
        };

    } catch (error) {
        console.error('Error scanning QR:', error);
        return {
            success: false,
            message: 'Terjadi kesalahan saat memproses QR Code',
        };
    } finally {
        isScanning.value = false;
    }
};

const formSchema = z.object({
    kode_unik: z.string({
        required_error: 'Kode unik diperlukan',
    }),
    pleno: z.number({
        required_error: 'Pleno diperlukan',
    }),
});

type FormData = {
    kode_unik: string;
    pleno: number;
};

const formInertia = useInertiaForm<FormData>({
    kode_unik: '',
    pleno: 0,
});

const { handleSubmit } = useVeeForm({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});


// Handle Presensi
const handlePresensi = handleSubmit(() => {
    if (!scannedPeserta.value || !selectedPleno.value) {
        scanResult.value = {
            success: false,
            message: 'Pilih pleno terlebih dahulu'
        };
        return;
    }

    isProcessingPresensi.value = true;

    try {
        formInertia.kode_unik = scannedPeserta.value.kode_unik;
        formInertia.pleno = selectedPleno.value;
        formInertia.post('/admin-kehadiran/presensi', {
            onSuccess: () => {
                scanResult.value = {
                    success: true,
                    message: 'Presensi berhasil untuk ' + scannedPeserta.value?.nama
                };
            },
            onError: (errors) => {
                scanResult.value = {
                    success: false,
                    message: 'Gagal melakukan presensi: ' + JSON.stringify(errors)
                };
            }

        });
    } catch (error) {
        console.error('Error during presensi:', error);
        scanResult.value = {
            success: false,
            message: 'Terjadi kesalahan saat melakukan presensi'
        };
    } finally {
        isProcessingPresensi.value = false;
    }
});

// Toggle QR Scanner
const toggleQRScanner = () => {
    showQRScanner.value = !showQRScanner.value;
    scanResult.value = null;
    scannedPeserta.value = null;
};

// Reset scan
const resetScan = () => {
    showQRScanner.value = false;
    scannedPeserta.value = null;
    selectedPleno.value = null;
    scanResult.value = null;
    showPresensiDialog.value = false;
};

const logoutAction = async () => {
    await router.post('/admin-kehadiran/logout');
};

// Helper untuk menampilkan jenis kelamin
const getJenisKelaminText = (jk: string) => {
    return jk === 'L' ? 'Laki-laki' : jk === 'P' ? 'Perempuan' : jk;
};

// Helper untuk menampilkan status
const getStatusText = (status: string) => {
    const statusMap: { [key: string]: string } = {
        'active': 'Aktif',
        'inactive': 'Tidak Aktif',
        'pending': 'Menunggu'
    };
    return statusMap[status] || status;
};
</script>

<template>

    <Head title="Dashboard Admin Kehadiran" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="border-b bg-white shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-center px-4 sm:px-6 lg:px-8">
                <div class="flex w-full max-w-[540px] items-center justify-between py-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            Hello! {{ props.admin.nama }}
                        </h1>
                        <p class="text-gray-600">Admin Kehadiran</p>
                    </div>
                    <div class="cursor-pointer text-right">
                        <Button variant="destructive" class="flex items-center gap-1" @click="logoutAction">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </Button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto flex max-w-7xl flex-col items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="flex w-full md:flex-row max-w-[540px] flex-col justify-between gap-2">
                <Card class="mb-4 w-full">
                    <CardContent class="flex w-full flex-col items-center gap-3">
                        <div class="w-fit">
                            <h2 class="text-lg font-medium text-gray-700">
                                Total Peserta Terdaftar
                            </h2>
                        </div>
                        <div class="w-fit text-3xl font-bold text-gray-900">
                            {{ props.peserta.length }}
                        </div>
                    </CardContent>
                </Card>
                <Card class="mb-4 w-full">
                    <CardContent class="flex w-full flex-col items-center gap-3">
                        <div class="w-fit">
                            <h2 class="text-lg font-medium text-gray-700">
                                Kehadiran Peserta
                            </h2>
                        </div>
                        <div class="w-full text-[12px] font-bold text-gray-900 flex flex-row justify-between">
                            <div class="flex flex-col items-center">
                                <div>
                                    Pleno 1
                                </div>
                                <div>
                                    {{ props.kehadiranStats.pleno_1 }}
                                </div>
                            </div>
                            <div class="flex flex-col items-center">
                                <div>
                                    Pleno 2
                                </div>
                                <div>
                                    {{ props.kehadiranStats.pleno_2 }}
                                </div>
                            </div>
                            <div class="flex flex-col items-center">
                                <div>
                                    Pleno 3
                                </div>
                                <div>
                                    {{ props.kehadiranStats.pleno_3 }}
                                </div>
                            </div>
                            <div class="flex flex-col items-center">
                                <div>
                                    Pleno 4
                                </div>
                                <div>
                                    {{ props.kehadiranStats.pleno_4 }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="flex w-full max-w-[540px] justify-center gap-8">
                <!-- QR Code Section -->
                <Card class="w-full">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            Scan QR Code Kehadiran
                        </CardTitle>
                        <CardDescription>
                            Gunakan kamera untuk memindai QR Code peserta
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="w-full max-w-[540px] space-y-4">
                        <!-- QR Scanner Component -->
                        <div v-if="showQRScanner">
                            <QRScanner :on-scan="handleQRScan" />

                            <!-- Scan Result -->
                            <div v-if="scanResult && !scanResult.success" class="mt-4 rounded-lg p-4" :class="scanResult.success
                                ? 'border border-green-200 bg-green-50'
                                : 'border border-red-200 bg-red-50'
                                ">
                                <div class="flex items-center gap-3">
                                    <svg v-if="scanResult.success" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-medium" :class="scanResult.success
                                        ? 'text-green-800'
                                        : 'text-red-800'
                                        ">
                                        {{ scanResult.message }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Scanner Placeholder -->
                        <div v-else
                            class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">
                                Scanner QR Code
                            </h3>
                            <p class="mb-4 text-gray-500">
                                Klik tombol dibawah untuk memulai scan QR Code
                                peserta
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <Button @click="toggleQRScanner" class="flex-1" :variant="showQRScanner ? 'destructive' : 'default'
                                " :disabled="isScanning">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path v-if="!showQRScanner" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{
                                    showQRScanner
                                        ? 'Stop Scanner'
                                        : 'Mulai Scan'
                                }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </main>

        <!-- Dialog Presensi dengan Kartu Peserta -->
        <Dialog v-model:open="showPresensiDialog" class="max-w-2xl h-full my-4 flex">
            <DialogContent class="sm:max-w-2xl h-full my-4 flex flex-col overflow-y-scroll">
                <DialogHeader>
                    <DialogTitle>Konfirmasi Presensi</DialogTitle>
                    <DialogDescription>
                        Verifikasi data peserta sebelum melakukan presensi
                    </DialogDescription>
                </DialogHeader>

                <!-- Kartu Peserta -->
                <div v-if="scannedPeserta" class="space-y-6 h-full">
                    <!-- Kartu Identitas Peserta -->
                    <Card class="border-2 border-blue-200 bg-blue-50">
                        <CardContent class="p-6 h-full">
                            <div class="flex items-start gap-4">
                                <!-- Foto Peserta -->
                                <div class="shrink-0">
                                    <div
                                        class="h-20 w-20 rounded-lg border-2 border-white bg-white shadow-sm overflow-hidden">
                                        <img v-if="scannedPeserta.foto" :src="scannedPeserta.foto"
                                            :alt="scannedPeserta.nama" class="h-full w-full object-cover" />
                                        <div v-else class="h-full w-full bg-gray-200 flex items-center justify-center">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Peserta -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 truncate">
                                                {{ scannedPeserta.nama }}
                                            </h3>
                                            <p class="text-sm text-blue-600 font-medium">
                                                {{ scannedPeserta.kode_unik }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-1 bg-green-100 px-2 py-1 rounded-full">
                                            <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                            <span class="text-xs font-medium text-green-800">Aktif</span>
                                        </div>
                                    </div>

                                    <!-- Grid Informasi -->
                                    <div class="flex md:flex-row md:gap-6 flex-col gap-3 text-sm">
                                        <div>
                                            <p class="text-gray-900">{{ scannedPeserta.asal_pimpinan }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-900">{{
                                                getJenisKelaminText(scannedPeserta.jenis_kelamin) }}</p>
                                        </div>
                                        <!-- <div>
                                            <span class="font-medium text-gray-600">Password:</span>
                                            <p class="text-gray-900 font-mono">{{ scannedPeserta.password_plain }}</p>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pilihan Pleno -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-900 text-lg">Pilih Sesi Pleno</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <Button v-for="pleno in [1, 2, 3, 4]" :key="pleno"
                                :variant="selectedPleno === pleno ? 'default' : 'outline'"
                                @click="selectedPleno = pleno"
                                class="flex items-center justify-center gap-2 py-3 h-auto"
                                :class="selectedPleno === pleno ? 'bg-blue-600 text-white' : 'border-blue-200 text-blue-700'">
                                <svg v-if="selectedPleno === pleno" class="h-5 w-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <div class="text-center">
                                    <div class="font-semibold">Pleno {{ pleno }}</div>
                                    <div class="text-xs opacity-80">Sesi {{ pleno }}</div>
                                </div>
                            </Button>
                        </div>
                    </div>

                    <!-- Result Presensi -->
                    <div v-if="scanResult" class="rounded-lg p-4 border-2" :class="scanResult.success
                        ? 'border-green-300 bg-green-50'
                        : 'border-red-300 bg-red-50'
                        ">
                        <div class="flex items-center gap-3">
                            <svg v-if="scanResult.success" class="h-6 w-6 text-green-600" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-semibold block" :class="scanResult.success
                                    ? 'text-green-800'
                                    : 'text-red-800'
                                    ">
                                    {{ scanResult.success ? 'Berhasil!' : 'Gagal' }}
                                </span>
                                <span class="text-sm" :class="scanResult.success
                                    ? 'text-green-700'
                                    : 'text-red-700'
                                    ">
                                    {{ scanResult.message }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <Button @click="resetScan" variant="outline" class="flex-1">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Scan Ulang
                        </Button>
                        <Button @click="handlePresensi" :disabled="!selectedPleno || isProcessingPresensi"
                            class="flex-1 bg-green-600 hover:bg-green-700">
                            <svg v-if="isProcessingPresensi" class="mr-2 h-4 w-4 animate-spin" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 2a10 10 0 100 20 10 10 0 000-20z" />
                            </svg>
                            <svg v-else class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ isProcessingPresensi ? 'Memproses...' : 'Konfirmasi Presensi' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>