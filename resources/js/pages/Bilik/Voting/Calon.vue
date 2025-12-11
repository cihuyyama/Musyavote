<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import { Check, User, LogOut, Clock, Vote, AlertCircle, Minus } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps<{
    peserta: any;
    pemilihans: any[]; // Array of pemilihan
    bilik: any;
    pemilihanStatus: any[];
}>();

console.log("Data props:", props);

// State untuk setiap pemilihan
const selectedCalon = ref<{ [pemilihanId: string]: string[] }>({});
const isLoading = ref(false);

// Timer countdown
const timeLeft = ref(5 * 60); // 10 menit untuk multiple pemilihan
const timer = ref<NodeJS.Timeout | null>(null);

// Format waktu countdown
const formattedTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes.toString().padStart(2, '0')} : ${seconds.toString().padStart(2, '0')}`;
});

// Total progress across all pemilihan
const totalProgress = computed(() => {
    return props.pemilihans.reduce((total, pemilihan) => {
        return total + pemilihan.jumlah_formatur_terpilih;
    }, 0);
});

const currentProgress = computed(() => {
    return Object.values(selectedCalon.value).reduce((total, calonIds) => {
        return total + calonIds.length;
    }, 0);
});

// Cek apakah semua pemilihan sudah valid
const allPemilihanValid = computed(() => {
    return props.pemilihans.every(pemilihan => {
        const selectedCount = selectedCalon.value[pemilihan.id]?.length || 0;
        const requiredCount = pemilihan.jumlah_formatur_terpilih;
        const bolehAbstain = pemilihan.boleh_tidak_memilih === 1;

        if (bolehAbstain) {
            // Jika boleh abstain: valid jika abstain ATAU memilih tepat jumlah
            return selectedCount === 0 || selectedCount === requiredCount;
        } else {
            // Jika tidak boleh abstain: HARUS memilih tepat jumlah
            return selectedCount === requiredCount;
        }
    });
});

// Cek setiap pemilihan secara individual
const pemilihanValidity = computed(() => {
    const validity: { [pemilihanId: string]: { valid: boolean, mode: 'abstain' | 'voting' | 'partial' | 'wajib_memilih' } } = {};

    props.pemilihans.forEach(pemilihan => {
        const selectedCount = selectedCalon.value[pemilihan.id]?.length || 0;
        const requiredCount = pemilihan.jumlah_formatur_terpilih;
        const bolehAbstain = pemilihan.boleh_tidak_memilih === 1;

        if (bolehAbstain) {
            // Pemilihan yang boleh abstain
            if (selectedCount === 0) {
                // Tidak memilih sama sekali = abstain
                validity[pemilihan.id] = { valid: true, mode: 'abstain' };
            } else if (selectedCount === requiredCount) {
                // Memilih tepat jumlahnya
                validity[pemilihan.id] = { valid: true, mode: 'voting' };
            } else {
                // Memilih sebagian (belum lengkap)
                validity[pemilihan.id] = { valid: false, mode: 'partial' };
            }
        } else {
            // Pemilihan yang TIDAK boleh abstain (wajib memilih)
            if (selectedCount === requiredCount) {
                // Memilih tepat jumlahnya
                validity[pemilihan.id] = { valid: true, mode: 'voting' };
            } else {
                // Belum memilih tepat jumlah
                validity[pemilihan.id] = { valid: false, mode: 'wajib_memilih' };
            }
        }
    });

    return validity;
});

// Nama calon yang dipilih untuk setiap pemilihan
const selectedCalonNames = computed(() => {
    const names: { [pemilihanId: string]: string[] } = {};

    props.pemilihans.forEach(pemilihan => {
        const calonIds = selectedCalon.value[pemilihan.id] || [];
        names[pemilihan.id] = pemilihan.calon
            .filter(calon => calonIds.includes(calon.id))
            .map(calon => calon.nama);
    });

    return names;
});

// Pemilihan yang memiliki pilihan (untuk menghindari v-if dengan v-for)
const pemilihanWithChoices = computed(() => {
    return props.pemilihans.filter(pemilihan => {
        return selectedCalonNames.value[pemilihan.id]?.length > 0;
    });
});

// Inisialisasi state untuk setiap pemilihan
const initializePemilihanState = () => {
    props.pemilihans.forEach(pemilihan => {
        if (!selectedCalon.value[pemilihan.id]) {
            selectedCalon.value[pemilihan.id] = [];
        }
    });
};

const toggleCalon = (pemilihanId: string, calonId: string) => {
    if (!selectedCalon.value[pemilihanId]) {
        selectedCalon.value[pemilihanId] = [];
    }

    const currentSelection = selectedCalon.value[pemilihanId];
    const pemilihan = props.pemilihans.find(p => p.id === pemilihanId);
    const requiredCount = pemilihan.jumlah_formatur_terpilih;

    // Toggle: jika sudah dipilih, hapus; jika belum, tambah
    if (currentSelection.includes(calonId)) {
        // Hapus calon yang dipilih
        selectedCalon.value[pemilihanId] = currentSelection.filter(id => id !== calonId);
    } else {
        // Cek apakah sudah mencapai batas maksimum
        if (currentSelection.length < requiredCount) {
            selectedCalon.value[pemilihanId].push(calonId);

            // Jika sudah mencapai jumlah yang dibutuhkan, beri feedback
            if (currentSelection.length + 1 === requiredCount) {
                toast.success(`Anda telah memilih tepat ${requiredCount} calon untuk ${pemilihan.nama_pemilihan}`);
            }
        } else {
            toast.error(`Maksimal memilih ${requiredCount} calon untuk ${pemilihan.nama_pemilihan}.`);
        }
    }
};

const clearAllSelections = (pemilihanId: string) => {
    const pemilihan = props.pemilihans.find(p => p.id === pemilihanId);

    if (pemilihan.boleh_tidak_memilih === 1) {
        selectedCalon.value[pemilihanId] = [];
        toast.info(`Semua pilihan untuk ${pemilihan.nama_pemilihan} telah dihapus. Anda sekarang abstain.`);
    } else {
        toast.error(`${pemilihan.nama_pemilihan} tidak mengizinkan abstain. Anda harus memilih tepat ${pemilihan.jumlah_formatur_terpilih} calon.`);
    }
};

const submitVoting = async () => {
    // Validasi untuk setiap pemilihan
    for (const pemilihan of props.pemilihans) {
        const selectedCount = selectedCalon.value[pemilihan.id]?.length || 0;
        const requiredCount = pemilihan.jumlah_formatur_terpilih;
        const bolehAbstain = pemilihan.boleh_tidak_memilih === 1;

        if (bolehAbstain) {
            // Boleh abstain: harus 0 (abstain) ATAU tepat requiredCount
            if (selectedCount > 0 && selectedCount !== requiredCount) {
                toast.error(`Untuk ${pemilihan.nama_pemilihan}: Pilih tepat ${requiredCount} calon atau tidak memilih sama sekali (abstain). Saat ini Anda memilih ${selectedCount} calon.`);
                return;
            }
        } else {
            // Tidak boleh abstain: HARUS tepat requiredCount
            if (selectedCount !== requiredCount) {
                toast.error(`Untuk ${pemilihan.nama_pemilihan}: Anda harus memilih tepat ${requiredCount} calon. Saat ini Anda memilih ${selectedCount} calon.`);
                return;
            }
        }
    }

    // Validasi tambahan: pastikan tidak ada calon yang duplikat
    for (const pemilihan of props.pemilihans) {
        const calonIds = selectedCalon.value[pemilihan.id] || [];
        const uniqueIds = [...new Set(calonIds)];

        if (uniqueIds.length !== calonIds.length) {
            toast.error(`Terdapat calon yang dipilih duplikat untuk ${pemilihan.nama_pemilihan}`);
            return;
        }
    }

    isLoading.value = true;

    try {
        // Format data untuk semua pemilihan
        const pilihanData = props.pemilihans.map(pemilihan => ({
            pemilihan_id: pemilihan.id,
            calon_ids: selectedCalon.value[pemilihan.id] || [],
            tidak_memilih: selectedCalon.value[pemilihan.id]?.length === 0 // true jika abstain
        }));

        const response = await router.post("/bilik/voting/submit", {
            pilihan: pilihanData
        });

        if (response.data.success) {
            toast.success('Semua voting berhasil disimpan!');
            router.post("/bilik/voting/logout");
        } else {
            toast.error('Beberapa pemilihan gagal disimpan');
            if (response.data.errors) {
                response.data.errors.forEach((error: string) => {
                    toast.error(error);
                });
            }
        }

    } catch (error: any) {
        if (error.response?.data?.errors) {
            error.response.data.errors.forEach((err: string) => {
                toast.error(err);
            });
        } else {
            toast.error('Terjadi kesalahan saat menyimpan voting');
        }
    } finally {
        isLoading.value = false;
    }
};

const logout = () => {
    router.post("/bilik/voting/logout");
};

// Timer logic
onMounted(() => {
    initializePemilihanState();

    timer.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            // Auto submit ketika waktu habis
            if (timer.value) {
                clearInterval(timer.value);
            }
            toast.error('Waktu habis! Silakan submit pilihan Anda.');
        }
    }, 1000);
});

onUnmounted(() => {
    if (timer.value) {
        clearInterval(timer.value);
    }
});
</script>

<template>

    <Head title="Voting - Semua Pemilihan" />
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Floating Header dengan Timer -->
            <div class="sticky top-4 z-10 mb-6">
                <Card class="shadow-xl border border-gray-200 backdrop-blur-sm bg-white/90">
                    <CardContent class=" px-6">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="text-center md:text-left">
                                <h1 class="text-2xl font-bold text-gray-900 font-poppins">Formulir Pemilihan</h1>
                                <p class="text-gray-600 text-sm font-light">{{ pemilihans.length }} pemilihan tersedia
                                </p>
                            </div>

                            <div class="flex items-center gap-4 md:gap-8">
                                <!-- Timer Compact -->
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center">
                                            <Clock class="w-4 h-4 text-red-600" />
                                        </div>
                                        <span class="text-xl font-bold text-gray-900 font-mono tracking-tight">
                                            {{ formattedTime }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Waktu tersisa</p>
                                </div>

                                <!-- Progress Compact -->
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center border border-blue-100">
                                        <span class="text-sm font-bold text-blue-600">{{ currentProgress }}/{{
                                            totalProgress }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Progress</p>
                                </div>

                                <!-- Status Validasi Compact -->
                                <div v-if="allPemilihanValid" class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <Check class="w-4 h-4 text-green-600" />
                                    </div>
                                    <p class="text-xs text-green-600 mt-1">Siap</p>
                                </div>
                                <div v-else class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                                        <AlertCircle class="w-4 h-4 text-amber-600" />
                                    </div>
                                    <p class="text-xs text-amber-600 mt-1">Periksa</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Panel Kiri: Informasi dan Pilihan -->
                <div class="lg:col-span-1 space-y-5">
                    <!-- Informasi Peserta Card -->
                    <Card class="border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="text-base px-6 font-semibold text-gray-900 font-poppins">
                                Informasi Peserta
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="pt-0">
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                    <span class="text-sm font-medium text-gray-700">ID:</span>
                                    <span class="text-sm text-gray-900 font-medium ml-auto">{{ peserta.kode_unik
                                        }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                    <span class="text-sm font-medium text-gray-700">Nama:</span>
                                    <span class="text-sm text-gray-900 font-medium ml-auto">{{ peserta.nama }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                    <span class="text-sm font-medium text-gray-700">Asal:</span>
                                    <span class="text-sm text-gray-900 font-medium ml-auto">{{ peserta.asal_pimpinan
                                        }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pilihan Anda untuk Setiap Pemilihan -->
                    <Card v-for="pemilihan in pemilihanWithChoices" :key="pemilihan.id"
                        class="border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle
                                    class="text-base font-semibold text-gray-900 font-poppins flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full"
                                        :class="pemilihanValidity[pemilihan.id]?.valid ? 'bg-green-500' : 'bg-amber-500'">
                                    </div>
                                    {{ pemilihan.nama_pemilihan }}
                                </CardTitle>
                                <div v-if="pemilihanValidity[pemilihan.id]?.valid" class="shrink-0">
                                    <Check v-if="pemilihanValidity[pemilihan.id]?.mode === 'voting'"
                                        class="w-4 h-4 text-green-500" />
                                    <Minus v-else class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-0">
                            <div class="space-y-2">

                                <div v-for="(calonId, index) in selectedCalon[pemilihan.id]" :key="index"
                                    class="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                                    <!-- Cari calon berdasarkan ID -->
                                    <div v-if="pemilihan.calon.find(c => c.id === calonId)"
                                        class="flex items-center gap-3 w-full">
                                        <!-- Badge nomor urut -->
                                        <div
                                            class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center shrink-0 border border-green-300">
                                            <span class="text-xs font-bold text-green-700">
                                                {{pemilihan.calon.find(c => c.id === calonId).nomor_urut}}
                                            </span>
                                        </div>

                                        <!-- Check icon -->
                                        <Check class="w-4 h-4 text-green-600 shrink-0" />

                                        <!-- Nama calon -->
                                        <div class="flex-1 min-w-0">
                                            <span class="text-green-800 text-sm font-medium block">{{
                                                pemilihan.calon.find(c => c.id === calonId).nama}}</span>
                                            <span class="text-green-600 text-xs block mt-0.5">
                                                {{pemilihan.calon.find(c => c.id === calonId).asal_pimpinan}}
                                            </span>
                                        </div>

                                        <button @click.stop="toggleCalon(pemilihan.id, calonId)"
                                            class="text-xs text-red-600 hover:text-red-800 font-medium transition-colors px-2 py-1 hover:bg-red-50 rounded">
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                <!-- Status pemilihan -->
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Status:</span>
                                        <span :class="{
                                            'text-green-600 font-semibold': pemilihanValidity[pemilihan.id]?.valid,
                                            'text-amber-600 font-semibold': !pemilihanValidity[pemilihan.id]?.valid
                                        }">
                                            {{ selectedCalonNames[pemilihan.id]?.length || 0 }}/{{
                                                pemilihan.jumlah_formatur_terpilih }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit Button Card -->
                    <div class="sticky top-20">
                        <Card class="border border-gray-200 shadow-lg bg-gradient-to-br from-white to-gray-50">
                            <CardContent class="pt-2">
                                <Button @click="submitVoting"
                                    class="w-full h-12 text-base font-semibold font-poppins shadow-md hover:shadow-lg transition-all duration-200"
                                    :disabled="isLoading" :class="{
                                        'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700': allPemilihanValid,
                                        'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800': !allPemilihanValid
                                    }">
                                    <span v-if="isLoading" class="flex items-center gap-2">
                                        <div
                                            class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin">
                                        </div>
                                        Menyimpan...
                                    </span>
                                    <span v-else class="flex items-center gap-2">
                                        <Check class="w-5 h-5" />
                                        Submit Semua Pemilihan
                                    </span>
                                </Button>
                                <p class="text-xs text-gray-500 text-center mt-3 font-light">
                                    Setelah submit, Anda tidak dapat mengubah pilihan
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Panel Kanan: Daftar Calon untuk Semua Pemilihan -->
                <div class="lg:col-span-3">
                    <div class="space-y-6">
                        <Card v-for="pemilihan in pemilihans" :key="pemilihan.id"
                            class="border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <CardHeader class="pb-3">
                                <div
                                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div>
                                        <CardTitle
                                            class="text-lg font-bold px-6 text-gray-900 font-poppins flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                            {{ pemilihan.nama_pemilihan }}
                                        </CardTitle>
                                        <p class="text-sm px-6 text-gray-600 mt-1">
                                            Pilih tepat
                                            <span class="font-semibold text-red-600">{{
                                                pemilihan.jumlah_formatur_terpilih }}</span>
                                            calon
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-right px-6">
                                            <div class="text-sm font-medium" :class="{
                                                'text-green-600': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'voting',
                                                'text-gray-600': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'abstain',
                                                'text-amber-600': !pemilihanValidity[pemilihan.id]?.valid
                                            }">
                                                {{ selectedCalon[pemilihan.id]?.length || 0 }}/{{
                                                    pemilihan.jumlah_formatur_terpilih }}
                                            </div>
                                            <div v-if="selectedCalon[pemilihan.id]?.length > 0 && pemilihan.boleh_tidak_memilih === 1"
                                                class="mt-1">
                                                <button @click="clearAllSelections(pemilihan.id)"
                                                    class="text-xs text-red-600 hover:text-red-800 underline font-medium">
                                                    Hapus untuk abstain
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="pt-0">
                                <!-- Daftar Calon Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="calon in pemilihan.calon" :key="calon.id"
                                        class="group relative border rounded-xl p-4 cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                                        :class="{
                                            'border-red-300 bg-gradient-to-br from-red-50 to-white shadow-md ring-2 ring-red-100': selectedCalon[pemilihan.id]?.includes(calon.id),
                                            'border-gray-200 bg-white hover:border-red-200': !selectedCalon[pemilihan.id]?.includes(calon.id),
                                            'cursor-not-allowed opacity-50': selectedCalon[pemilihan.id]?.length >= pemilihan.jumlah_formatur_terpilih && !selectedCalon[pemilihan.id]?.includes(calon.id)
                                        }" @click="toggleCalon(pemilihan.id, calon.id)">

                                        <!-- Selected Badge -->
                                        <div v-if="selectedCalon[pemilihan.id]?.includes(calon.id)"
                                            class="absolute -top-2 -right-2 w-6 h-6 bg-red-600 rounded-full flex items-center justify-center shadow-md">
                                            <Check class="w-3 h-3 text-white" />
                                        </div>

                                        <!-- Nomor Urut Badge -->
                                        <div class="absolute -top-2 -left-2 w-6 h-6 rounded-full flex items-center justify-center shadow-sm"
                                            :class="selectedCalon[pemilihan.id]?.includes(calon.id)
                                                ? 'bg-white border border-red-200'
                                                : 'bg-gray-100'">
                                            <span class="text-xs font-bold" :class="selectedCalon[pemilihan.id]?.includes(calon.id)
                                                ? 'text-red-600'
                                                : 'text-gray-600'">
                                                {{ calon.nomor_urut || '' }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <!-- Foto Calon -->
                                            <div class="shrink-0">
                                                <div class="w-14 h-14 rounded-full border-2 overflow-hidden" :class="selectedCalon[pemilihan.id]?.includes(calon.id)
                                                    ? 'border-red-300 ring-2 ring-red-100'
                                                    : 'border-gray-300 group-hover:border-red-200'">
                                                    <div v-if="!calon.foto"
                                                        class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                        <User class="w-6 h-6 text-gray-500" />
                                                    </div>
                                                    <img v-else
                                                        :src="calon.foto.startsWith('http') ? calon.foto : `/storage/${calon.foto}`"
                                                        alt="Foto Calon" class="w-full h-full object-cover" />
                                                </div>
                                            </div>

                                            <!-- Info Calon -->
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-gray-900 font-poppins text-sm mb-1">
                                                    {{ calon.nama }}
                                                </h3>
                                                <p class="text-xs text-gray-500 truncate">
                                                    {{ calon.asal_pimpinan }}
                                                </p>
                                                <div v-if="selectedCalon[pemilihan.id]?.includes(calon.id)"
                                                    class="mt-2">
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                                        <Check class="w-3 h-3" />
                                                        Terpilih
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Bar untuk Pemilihan -->
                                <div class="mt-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Progress Pemilihan</span>
                                        <span class="text-sm font-medium" :class="{
                                            'text-green-600': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'voting',
                                            'text-gray-600': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'abstain',
                                            'text-red-600': !pemilihanValidity[pemilihan.id]?.valid
                                        }">
                                            {{ selectedCalon[pemilihan.id]?.length || 0 }}/{{
                                                pemilihan.jumlah_formatur_terpilih }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500" :class="{
                                            'bg-gradient-to-r from-green-500 to-emerald-500': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'voting',
                                            'bg-gradient-to-r from-red-500 to-red-600': !pemilihanValidity[pemilihan.id]?.valid,
                                            'bg-gradient-to-r from-gray-400 to-gray-500': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'abstain'
                                        }"
                                            :style="{ width: `${((selectedCalon[pemilihan.id]?.length || 0) / pemilihan.jumlah_formatur_terpilih) * 100}%` }">
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Total Progress Indicator -->
                    <Card class="mt-6 border border-gray-200 shadow-sm">
                        <CardHeader>
                            <CardTitle class="text-base font-semibold p-6 text-gray-900 font-poppins">
                                Status Semua Pemilihan
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Progress Keseluruhan</span>
                                    <span class="text-lg font-bold font-poppins" :class="{
                                        'text-green-600': allPemilihanValid,
                                        'text-red-600': !allPemilihanValid
                                    }">
                                        {{props.pemilihans.filter(p => pemilihanValidity[p.id]?.valid).length}}/{{
                                            props.pemilihans.length }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700" :class="{
                                        'bg-gradient-to-r from-green-500 to-emerald-500': allPemilihanValid,
                                        'bg-gradient-to-r from-red-500 to-red-600': !allPemilihanValid
                                    }"
                                        :style="{ width: `${(props.pemilihans.filter(p => pemilihanValidity[p.id]?.valid).length / props.pemilihans.length) * 100}%` }">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-sm" :class="{
                                    'text-green-600': allPemilihanValid,
                                    'text-red-600': !allPemilihanValid
                                }">
                                    <div v-if="allPemilihanValid" class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center">
                                            <Check class="w-3 h-3 text-green-600" />
                                        </div>
                                        <span class="font-medium">Semua pemilihan sudah valid</span>
                                    </div>
                                    <div v-else class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
                                            <AlertCircle class="w-3 h-3 text-red-600" />
                                        </div>
                                        <span class="font-medium">Beberapa pemilihan belum valid</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>