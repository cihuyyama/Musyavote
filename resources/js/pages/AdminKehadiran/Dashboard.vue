<script setup lang="ts">
import QRScanner from '@/components/QRScanner.vue';
import { Button } from '@/components/ui/button';
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

import {
    User,
    Key,
    LogOut,
    Users,
    BarChart3,
    QrCode,
    Check,
    X,
    Camera,
    Circle,
    Lock,
    RotateCcw,
    Loader2
} from 'lucide-vue-next';

const props = defineProps<{
    admin: {
        nama: string;
        username: string;
        pleno_akses: number[]; // Tambahkan pleno_akses
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
const isProcessingPresensi = ref(false);
const isScanning = ref(false);

// Get pleno akses untuk admin
const getPlenoAksesText = () => {
    if (!props.admin.pleno_akses || props.admin.pleno_akses.length === 0) {
        return 'Tidak ada akses';
    }
    return props.admin.pleno_akses.map(p => `Pleno ${p}`).join(', ');
};

// Handle QR Code Scan
const handleQRScan = async (
    qrData: string,
): Promise<{ success: boolean; message: string; data?: any }> => {
    try {
        isScanning.value = true;

        let parsedData: QRData;

        try {
            parsedData = JSON.parse(qrData);
        } catch (e) {
            console.error('Error parsing QR data:', e);
            return {
                success: false,
                message: 'Format QR Code tidak valid',
            };
        }

        if (!parsedData.kode_unik || !parsedData.nama) {
            return {
                success: false,
                message: 'Data peserta tidak lengkap dalam QR Code'
            };
        }

        scannedPeserta.value = parsedData;
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
});

type FormData = {
    kode_unik: string;
};

const formInertia = useInertiaForm<FormData>({
    kode_unik: '',
});

const { handleSubmit } = useVeeForm({
    validationSchema: toTypedSchema(formSchema),
    initialValues: formInertia.data(),
});

// Handle Presensi
const handlePresensi = handleSubmit(() => {
    if (!scannedPeserta.value) {
        scanResult.value = {
            success: false,
            message: 'Data peserta tidak valid'
        };
        return;
    }

    // Cek apakah admin memiliki akses pleno
    if (!props.admin.pleno_akses || props.admin.pleno_akses.length === 0) {
        scanResult.value = {
            success: false,
            message: 'Admin tidak memiliki akses ke pleno manapun'
        };
        return;
    }

    isProcessingPresensi.value = true;

    try {
        formInertia.kode_unik = scannedPeserta.value.kode_unik;
        formInertia.post('/admin-kehadiran/presensi', {
            onSuccess: (response) => {
                scanResult.value = {
                    success: true,
                    message: 'Presensi berhasil dicatat untuk ' + scannedPeserta.value?.nama
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
// Di dalam script
const handleResetAndScan = () => {
    // Tutup dialog
    showPresensiDialog.value = false;

    // Reset data
    scannedPeserta.value = null;
    scanResult.value = null;

    // Tunggu sedikit agar dialog tertutup sempurna, lalu buka scanner
    setTimeout(() => {
        showQRScanner.value = true;
    }, 100);
};
// Reset scan
const resetScan = () => {
    showQRScanner.value = false;
    scannedPeserta.value = null;
    scanResult.value = null;
    showPresensiDialog.value = false;
};

// State tambahan untuk tracking apakah sudah konfirmasi
const isConfirmed = ref(false);


// <!-- Di dalam script -->
// Reset scan dan langsung buka scanner
// const resetScan = () => {
//     scannedPeserta.value = null;
//     scanResult.value = null;
//     showPresensiDialog.value = false;
//     // Tambahkan baris di bawah ini untuk langsung membuka scanner
//     showQRScanner.value = true;
// };

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

// Fungsi untuk mendapatkan URL foto berdasarkan kode unik
const getFotoByKodeUrl = (kode_unik: string) => {
    return `/images/kode/${kode_unik}`;
};

// Handle error saat gambar tidak ditemukan
const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/default-avatar.png'; // Fallback ke gambar default
    img.onerror = null; // Prevent infinite loop
};
</script>

<template>

    <Head title="Dashboard Admin Kehadiran" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header - White Design with Logo -->
        <header class="border-b bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-4">
                    <!-- Logo & Title Section -->
                    <div class="flex items-center gap-4">
                        <!-- Logo Area -->
                        <div class="h-16 flex items-center justify-center">
                            <!-- Placeholder untuk logo - bisa diganti dengan img tag -->
                            <img src="https://immdiy.or.id/wp-content/uploads/2020/07/new-logo-imm-large.png" alt="Logo"
                                class="h-full w-full object-cover" style="object-position: center;" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">
                                Selamat Datang, {{ props.admin.nama }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-3 mt-1">
                                <span
                                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                    <User class="h-3 w-3 mr-1.5" />
                                    Admin Kehadiran
                                </span>
                                <span
                                    class="inline-flex items-center rounded-full bg-[#A81B2C]/10 px-3 py-1 text-xs font-medium text-[#A81B2C]">
                                    <Key class="h-3 w-3 mr-1.5" />
                                    Akses: {{ getPlenoAksesText() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <Button variant="outline" @click="logoutAction"
                        class="border-gray-300 hover:bg-gray-50 hover:text-gray-900">
                        <LogOut class="h-4 w-4 mr-2" />
                        Logout
                    </Button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 max-w-4xl mx-auto">
                <!-- Total Peserta Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all hover:shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium mb-1">Total Peserta Terdaftar</p>
                            <h3 class="text-4xl font-bold text-gray-900">{{ props.peserta.length }}</h3>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-[#A81B2C]/10 flex items-center justify-center">
                            <Users class="h-6 w-6 text-[#A81B2C]" />
                        </div>
                    </div>
                </div>

                <!-- Kehadiran Stats Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all hover:shadow-xl">
                    <div class="flex items-center gap-2 mb-4">
                        <BarChart3 class="h-5 w-5 text-gray-500" />
                        <p class="text-gray-500 text-sm font-medium">Statistik Kehadiran</p>
                    </div>
                    <div class="grid grid-cols-4 gap-4">
                        <div v-for="pleno in [1, 2, 3, 4]" :key="pleno"
                            class="text-center p-3 rounded-xl transition-all" :class="props.admin.pleno_akses?.includes(pleno)
                                ? 'bg-[#A81B2C]/5 border border-[#A81B2C]/20'
                                : 'bg-gray-50 border border-gray-100 opacity-40'">
                            <p class="text-xs text-gray-500 mb-1">Pleno {{ pleno }}</p>
                            <p class="text-2xl font-bold"
                                :class="props.admin.pleno_akses?.includes(pleno) ? 'text-[#A81B2C]' : 'text-gray-400'">
                                {{ props.kehadiranStats[`pleno_${pleno}`] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Scanner Section -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-[#A81B2C]/10 flex items-center justify-center">
                                <QrCode class="h-5 w-5 text-[#A81B2C]" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Scan QR Code Kehadiran</h2>
                                <p class="text-sm text-gray-600">Gunakan kamera untuk memindai QR Code peserta</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Scanner Placeholder/Active Scanner -->
                        <div class="mb-6">
                            <div v-if="showQRScanner" class="space-y-4">
                                <QRScanner :on-scan="handleQRScan" />

                                <!-- Scan Status -->
                                <div v-if="scanResult" class="mt-4">
                                    <div class="rounded-xl p-4" :class="scanResult.success
                                        ? 'bg-green-50 border border-green-200'
                                        : 'bg-red-50 border border-red-200'">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full flex items-center justify-center"
                                                :class="scanResult.success ? 'bg-green-100' : 'bg-red-100'">
                                                <Check v-if="scanResult.success" class="h-5 w-5 text-green-600" />
                                                <X v-else class="h-5 w-5 text-red-600" />
                                            </div>
                                            <span class="font-medium"
                                                :class="scanResult.success ? 'text-green-800' : 'text-red-800'">
                                                {{ scanResult.message }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-12">
                                <div
                                    class="mx-auto mb-6 h-24 w-24 rounded-full bg-linear-to-br from-[#A81B2C]/10 to-[#A81B2C]/5 flex items-center justify-center">
                                    <QrCode class="h-12 w-12 text-[#A81B2C]" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Siap untuk Scan</h3>
                                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                                    Klik tombol di bawah untuk mengaktifkan kamera dan mulai scan QR Code peserta
                                </p>
                                <div class="inline-flex items-center gap-2 rounded-full bg-[#A81B2C]/10 px-4 py-2">
                                    <Circle class="h-2 w-2 fill-[#A81B2C]" />
                                    <span class="text-sm font-medium text-[#A81B2C]">
                                        Akses Pleno: {{ getPlenoAksesText() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Scanner Toggle Button -->
                        <Button @click="toggleQRScanner"
                            :disabled="isScanning || !props.admin.pleno_akses || props.admin.pleno_akses.length === 0"
                            class="w-full py-6 text-base font-medium rounded-xl"
                            :class="showQRScanner
                                ? 'bg-linear-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white'
                                : 'bg-linear-to-r from-[#A81B2C] to-[#8C1624] hover:from-[#8C1624] hover:to-[#6D121C] text-white'">
                            <Camera v-if="!showQRScanner" class="h-5 w-5 mr-2" />
                            <X v-else class="h-5 w-5 mr-2" />
                            {{ showQRScanner ? 'Stop Scanning' : 'Mulai Scan QR Code' }}
                        </Button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Dialog Presensi Responsive -->
        <Dialog v-model:open="showPresensiDialog" class="flex flex-col">
            <DialogContent class="sm:max-w-xl lg:max-w-2xl p-0 rounded-2xl flex flex-col max-h-[90vh] overflow-hidden">
                <DialogHeader class="p-6 pb-4 border-b border-gray-100">
                    <DialogTitle class="text-xl font-bold text-gray-900">Konfirmasi Presensi</DialogTitle>
                    <DialogDescription class="text-gray-600">
                        Verifikasi data peserta sebelum melakukan presensi
                    </DialogDescription>
                </DialogHeader>

                <div v-if="scannedPeserta" class="space-y-6 p-6 overflow-y-auto">

                    <!-- Foto Responsive -->
                    <div class="flex flex-col items-center">
                        <div class="relative mb-6">
                            <div class="w-40 h-40 sm:w-56 sm:h-56 lg:w-64 lg:h-64 
                                rounded-2xl border-4 border-white shadow-xl overflow-hidden bg-gray-200">
                                <img v-if="scannedPeserta.kode_unik" :src="getFotoByKodeUrl(scannedPeserta.kode_unik)"
                                    :alt="scannedPeserta.nama" class="w-full h-full object-cover"
                                    @error="handleImageError" />
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <User class="h-16 w-16 text-gray-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Peserta -->
                        <div class="text-center max-w-xs sm:max-w-md">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ scannedPeserta.nama }}</h3>

                            <div class="inline-flex items-center gap-2 mb-4">
                                <span class="text-sm font-medium text-[#A81B2C] bg-[#A81B2C]/10 px-3 py-1 rounded-full">
                                    {{ scannedPeserta.kode_unik }}
                                </span>
                                <Circle class="h-1 w-1 fill-gray-600" />
                                <span class="text-sm text-gray-600">
                                    {{ getJenisKelaminText(scannedPeserta.jenis_kelamin) }}
                                </span>
                            </div>
                            <p class="text-gray-700 mb-6">{{ scannedPeserta.asal_pimpinan }}</p>
                        </div>
                    </div>

                    <!-- Grid Akses Pleno -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <Lock class="h-5 w-5 text-gray-500" />
                            <h4 class="font-semibold text-gray-900">Akses Pleno Anda</h4>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">

                            <div v-for="pleno in [1, 2, 3, 4]" :key="pleno"
                                class="p-4 rounded-xl text-center transition-all transform hover:scale-[1.02]" :class="props.admin.pleno_akses?.includes(pleno)
                                    ? 'bg-[#A81B2C]/5 border border-[#A81B2C]/20'
                                    : 'bg-gray-50 border border-gray-100 opacity-50'">
                                <div class="h-10 w-10 mx-auto mb-2 rounded-full flex items-center justify-center"
                                    :class="props.admin.pleno_akses?.includes(pleno)
                                        ? 'bg-[#A81B2C]/10 text-[#A81B2C]'
                                        : 'bg-gray-100 text-gray-400'">
                                    <Check v-if="props.admin.pleno_akses?.includes(pleno)" class="h-5 w-5" />
                                    <X v-else class="h-5 w-5" />
                                </div>

                                <div class="font-semibold"
                                    :class="props.admin.pleno_akses?.includes(pleno) ? 'text-[#A81B2C]' : 'text-gray-400'">
                                    Pleno {{ pleno }}
                                </div>
                                <div class="text-xs mt-1"
                                    :class="props.admin.pleno_akses?.includes(pleno) ? 'text-gray-600' : 'text-gray-400'">
                                    {{ props.admin.pleno_akses?.includes(pleno) ? 'Aktif' : 'Tidak ada akses' }}
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Status Presensi -->
                    <div v-if="scanResult" class="rounded-xl p-4 border-2" :class="scanResult.success
                        ? 'border-green-300 bg-green-50'
                        : 'border-red-300 bg-red-50'">
                        <div class="flex items-start gap-3">
                            <div class="h-8 w-8 rounded-full flex items-center justify-center shrink-0"
                                :class="scanResult.success ? 'bg-green-100' : 'bg-red-100'">
                                <Check v-if="scanResult.success" class="h-5 w-5 text-green-600" />
                                <X v-else class="h-5 w-5 text-red-600" />
                            </div>
                            <div>
                                <span class="font-semibold block mb-1"
                                    :class="scanResult.success ? 'text-green-800' : 'text-red-800'">
                                    {{ scanResult.success ? 'Presensi Berhasil!' : 'Presensi Gagal' }}
                                </span>
                                <span class="text-sm" :class="scanResult.success ? 'text-green-700' : 'text-red-700'">
                                    {{ scanResult.message }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3 pt-4">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <Button @click="handleResetAndScan" variant="outline"
                                class="flex-1 py-3 rounded-xl border-gray-300 hover:bg-gray-50">
                                <RotateCcw class="h-4 w-4 mr-2" />
                                Scan Peserta Lain
                            </Button>

                            <Button @click="handlePresensi"
                                :disabled="isProcessingPresensi || !props.admin.pleno_akses || props.admin.pleno_akses.length === 0"
                                class="flex-1 py-3 rounded-xl bg-[#A81B2C] text-white font-medium hover:bg-[#8C1624]">
                                <Loader2 v-if="isProcessingPresensi" class="mr-2 h-4 w-4 animate-spin" />
                                <Check v-else class="mr-2 h-4 w-4" />
                                {{ isProcessingPresensi ? 'Memproses...' : 'Konfirmasi Presensi' }}
                            </Button>
                        </div>

                        <Button @click="resetScan" variant="outline"
                            class="w-full py-3 rounded-xl border-gray-300 hover:bg-gray-50">
                            Selesai
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

    </div>
</template>