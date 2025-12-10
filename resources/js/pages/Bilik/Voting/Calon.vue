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
    <div class="min-h-screen bg-linear-to-br from-blue-50 to-indigo-100 py-8">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Header dengan Timer -->
            <Card class="mb-6 shadow-lg">
                <CardContent class="pt-6">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">Formulir Pemilihan</h1>
                            <p class="text-gray-600">{{ pemilihans.length }} pemilihan</p>
                        </div>

                        <div class="flex items-center gap-6">
                            <!-- Timer -->
                            <div class="text-center">
                                <div class="flex items-center gap-2 text-red-600 font-mono">
                                    <Clock class="w-5 h-5" />
                                    <span class="text-2xl font-bold">{{ formattedTime }}</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Waktu tersisa</p>
                            </div>

                            <!-- Progress -->
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ currentProgress }}/{{ totalProgress }}
                                </div>
                                <p class="text-sm text-gray-500">Total Progress</p>
                            </div>

                            <!-- Status Validasi -->
                            <div v-if="allPemilihanValid" class="text-center">
                                <div class="flex items-center gap-2 text-green-600">
                                    <Check class="w-5 h-5" />
                                    <span class="text-sm font-medium">Siap Submit</span>
                                </div>
                            </div>
                            <div v-else class="text-center">
                                <div class="flex items-center gap-2 text-amber-600">
                                    <AlertCircle class="w-5 h-5" />
                                    <span class="text-sm font-medium">Periksa Pilihan</span>
                                </div>
                            </div>

                            <!-- Logout Button -->
                            <!-- <Button @click="logout" variant="outline" size="sm">
                                <LogOut class="w-4 h-4 mr-2" />
                                Keluar
                            </Button> -->
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Panel Kiri: Informasi dan Pilihan -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Informasi Peserta -->
                    <Card class="shadow-lg">
                        <CardContent class="pt-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Informasi Peserta</h3>
                            <div class="space-y-2">
                                <p class="text-sm"><span class="font-medium">ID:</span> {{ peserta.kode_unik }}</p>
                                <p class="text-sm"><span class="font-medium">Nama:</span> {{ peserta.nama }}</p>
                                <p class="text-sm"><span class="font-medium">Asal Pimpinan:</span> {{ peserta.asal_pimpinan
                                    }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pilihan Anda untuk Setiap Pemilihan -->
                    <Card v-for="pemilihan in pemilihanWithChoices" :key="pemilihan.id" class="shadow-lg">
                        <CardContent class="pt-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Vote class="w-4 h-4" />
                                    {{ pemilihan.nama_pemilihan }}
                                </h3>
                                <!-- Status Validasi per Pemilihan -->
                                <div v-if="pemilihanValidity[pemilihan.id]?.valid" class="shrink-0">
                                    <Check v-if="pemilihanValidity[pemilihan.id]?.mode === 'voting'" class="w-4 h-4 text-green-500" />
                                    <Minus v-else class="w-4 h-4 text-gray-500" />
                                </div>
                                <div v-else class="shrink-0">
                                    <AlertCircle class="w-4 h-4 text-amber-500" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div v-for="(calonName, index) in selectedCalonNames[pemilihan.id]" :key="index"
                                    class="flex items-center gap-3 p-2 bg-green-50 rounded-lg border border-green-200">
                                    <Check class="w-4 h-4 text-green-600 shrink-0" />
                                    <span class="text-green-800 text-sm">{{ calonName }}</span>
                                    <button @click.stop="toggleCalon(pemilihan.id, pemilihan.calon.find(c => c.nama === calonName)?.id)"
                                        class="ml-auto text-xs text-red-600 hover:text-red-800">
                                        Hapus
                                    </button>
                                </div>
                                
                                <!-- Pesan jika belum lengkap -->
                                <div v-if="selectedCalonNames[pemilihan.id]?.length > 0 && selectedCalonNames[pemilihan.id]?.length < pemilihan.jumlah_formatur_terpilih"
                                    class="p-2 bg-amber-50 rounded-lg border border-amber-200">
                                    <p class="text-xs text-amber-800">
                                        Pilih {{ pemilihan.jumlah_formatur_terpilih - selectedCalonNames[pemilihan.id]?.length }} calon lagi
                                        <span v-if="pemilihan.boleh_tidak_memilih === 1">
                                            atau <button @click="clearAllSelections(pemilihan.id)" class="text-blue-600 hover:text-blue-800 underline">
                                                hapus semua untuk abstain
                                            </button>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit Button -->
                    <Card class="shadow-lg sticky top-6">
                        <CardContent class="pt-6">
                            <Button @click="submitVoting" class="w-full" size="lg"
                                :disabled="isLoading" 
                                :class="{
                                    'bg-green-600 hover:bg-green-700': allPemilihanValid,
                                    'bg-blue-600 hover:bg-blue-700': !allPemilihanValid
                                }">
                                <span v-if="isLoading">Menyimpan...</span>
                                <span v-else>
                                    Submit Semua Pemilihan
                                </span>
                            </Button>

                            <p class="text-xs text-gray-500 text-center mt-3">
                                Setelah submit, Anda tidak dapat mengubah pilihan lagi.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Panel Kanan: Daftar Calon untuk Semua Pemilihan -->
                <div class="lg:col-span-3">
                    <div class="space-y-6">
                        <Card v-for="pemilihan in pemilihans" :key="pemilihan.id" class="shadow-lg">
                            <CardContent class="pt-6">
                                <!-- Header Pemilihan -->
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                                            <Vote class="w-5 h-5 text-blue-600" />
                                            {{ pemilihan.nama_pemilihan }}
                                        </h2>
                                        <p class="text-gray-600">
                                            <span class="font-semibold">Pilih tepat {{ pemilihan.jumlah_formatur_terpilih }} calon</span>
                                            <!-- <span v-if="pemilihan.boleh_tidak_memilih === 1" class="text-green-600 ml-2">• Boleh abstain (tidak memilih)</span>
                                            <span v-else class="text-red-600 ml-2">• Wajib memilih (tidak boleh abstain)</span> -->
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm" :class="{
                                            'text-green-600 font-semibold': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'voting',
                                            'text-gray-600 font-semibold': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'abstain',
                                            'text-amber-600': !pemilihanValidity[pemilihan.id]?.valid
                                        }">
                                            <span v-if="selectedCalon[pemilihan.id]?.length === 0">
                                                {{ pemilihan.boleh_tidak_memilih === 1 ? 'Belum memilih' : 'Belum memilih' }}
                                            </span>
                                            <span v-else>
                                                Terpilih: {{ selectedCalon[pemilihan.id]?.length || 0 }}/{{
                                                pemilihan.jumlah_formatur_terpilih }}
                                            </span>
                                        </div>
                                        <div v-if="!pemilihanValidity[pemilihan.id]?.valid" class="text-xs text-amber-600 mt-1">
                                            <span v-if="pemilihanValidity[pemilihan.id]?.mode === 'partial'">
                                                Butuh {{ pemilihan.jumlah_formatur_terpilih - (selectedCalon[pemilihan.id]?.length || 0) }} calon lagi
                                            </span>
                                            <span v-else-if="pemilihanValidity[pemilihan.id]?.mode === 'wajib_memilih'">
                                                Wajib memilih tepat {{ pemilihan.jumlah_formatur_terpilih }} calon
                                            </span>
                                        </div>
                                        <div v-if="selectedCalon[pemilihan.id]?.length > 0 && pemilihan.boleh_tidak_memilih === 1" class="mt-1">
                                            <button @click="clearAllSelections(pemilihan.id)" 
                                                class="text-xs text-red-600 hover:text-red-800 underline">
                                                Hapus semua untuk abstain
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Daftar Calon -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="calon in pemilihan.calon" :key="calon.id"
                                        class="border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 hover:shadow-md"
                                        :class="{
                                            'border-green-500 bg-green-50 shadow-md': selectedCalon[pemilihan.id]?.includes(calon.id),
                                            'border-gray-200 bg-white hover:border-gray-300': !selectedCalon[pemilihan.id]?.includes(calon.id),
                                            'cursor-not-allowed opacity-70': selectedCalon[pemilihan.id]?.length >= pemilihan.jumlah_formatur_terpilih && !selectedCalon[pemilihan.id]?.includes(calon.id)
                                        }" 
                                        @click="toggleCalon(pemilihan.id, calon.id)"
                                        :title="selectedCalon[pemilihan.id]?.length >= pemilihan.jumlah_formatur_terpilih && !selectedCalon[pemilihan.id]?.includes(calon.id) 
                                            ? `Maksimal ${pemilihan.jumlah_formatur_terpilih} calon. Klik calon yang sudah dipilih untuk menghapus.`
                                            : ''">
                                        <div class="flex items-center gap-3">
                                            <!-- Foto Calon -->
                                            <div class="shrink-0">
                                                <div
                                                    class="w-12 h-12 rounded-full border-2 border-gray-300 overflow-hidden bg-gray-200 flex items-center justify-center">
                                                    <User v-if="!calon.foto" class="w-6 h-6 text-gray-500" />
                                                    <img v-else
                                                        :src="calon.foto.startsWith('http') ? calon.foto : `/storage/${calon.foto}`"
                                                        alt="Foto Calon" class="w-full h-full object-cover" />
                                                </div>
                                            </div>

                                            <!-- Nama Calon -->
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-gray-900 text-sm truncate">{{
                                                    calon.nama }}</h3>
                                                <p class="text-xs text-gray-500 truncate">{{ calon.asal_pimpinan
                                                    }}</p>
                                            </div>

                                            <!-- Check Indicator -->
                                            <div v-if="selectedCalon[pemilihan.id]?.includes(calon.id)"
                                                class="shrink-0 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                                <Check class="w-3 h-3 text-white" />
                                            </div>
                                            <div v-else class="shrink-0 w-6 h-6 border-2 border-gray-300 rounded-full flex items-center justify-center">
                                                <span class="text-xs text-gray-400">
                                                    {{ calon.nomor_urut || '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Pemilihan -->
                                <!-- <div class="mt-4 p-3 rounded-lg" :class="{
                                    'bg-gray-100': selectedCalon[pemilihan.id]?.length === 0,
                                    'bg-blue-50': selectedCalon[pemilihan.id]?.length > 0
                                }">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-medium" :class="{
                                            'text-gray-700': selectedCalon[pemilihan.id]?.length === 0,
                                            'text-blue-900': selectedCalon[pemilihan.id]?.length > 0
                                        }">Status {{ pemilihan.nama_pemilihan }}</span>
                                        <span class="text-xs font-medium" :class="{
                                            'text-green-600': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'voting',
                                            'text-gray-700': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'abstain',
                                            'text-amber-600': !pemilihanValidity[pemilihan.id]?.valid
                                        }">
                                            <span v-if="selectedCalon[pemilihan.id]?.length === 0">
                                                {{ pemilihan.boleh_tidak_memilih === 1 ? 'Abstain' : 'Belum memilih' }}
                                            </span>
                                            <span v-else-if="pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'voting'">
                                                Sudah tepat ({{ selectedCalon[pemilihan.id]?.length }}/{{ pemilihan.jumlah_formatur_terpilih }})
                                            </span>
                                            <span v-else>
                                                {{ selectedCalon[pemilihan.id]?.length }}/{{ pemilihan.jumlah_formatur_terpilih }}
                                            </span>
                                        </span>
                                    </div>
                                    <div v-if="selectedCalon[pemilihan.id]?.length > 0" class="w-full bg-blue-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full transition-all duration-300"
                                            :class="{
                                                'bg-green-500': pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'voting',
                                                'bg-amber-500': !pemilihanValidity[pemilihan.id]?.valid,
                                                'bg-blue-600': false
                                            }"
                                            :style="{ width: `${((selectedCalon[pemilihan.id]?.length || 0) / pemilihan.jumlah_formatur_terpilih) * 100}%` }">
                                        </div>
                                    </div>
                                    <p v-if="!pemilihanValidity[pemilihan.id]?.valid" class="text-xs text-amber-600 mt-2">
                                        <AlertCircle class="w-3 h-3 inline mr-1" />
                                        <span v-if="pemilihanValidity[pemilihan.id]?.mode === 'partial'">
                                            Pilih tepat {{ pemilihan.jumlah_formatur_terpilih }} calon
                                            <span v-if="pemilihan.boleh_tidak_memilih === 1">atau hapus semua untuk abstain</span>
                                        </span>
                                        <span v-else-if="pemilihanValidity[pemilihan.id]?.mode === 'wajib_memilih'">
                                            Anda harus memilih tepat {{ pemilihan.jumlah_formatur_terpilih }} calon (tidak boleh abstain)
                                        </span>
                                    </p>
                                    <p v-else-if="pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'voting'" class="text-xs text-green-600 mt-2">
                                        <Check class="w-3 h-3 inline mr-1" />
                                        Pilihan sudah tepat
                                    </p>
                                    <p v-else-if="pemilihanValidity[pemilihan.id]?.valid && pemilihanValidity[pemilihan.id]?.mode === 'abstain'" class="text-xs text-gray-600 mt-2">
                                        <Minus class="w-3 h-3 inline mr-1" />
                                        Anda memilih abstain (tidak memilih)
                                    </p>
                                </div> -->
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Total Progress Indicator -->
                    <Card class="mt-6 shadow-lg">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-blue-900">Status Semua Pemilihan</span>
                                <span class="text-sm font-medium" :class="{
                                    'text-green-600': allPemilihanValid,
                                    'text-blue-900': !allPemilihanValid
                                }">
                                    {{ props.pemilihans.filter(p => pemilihanValidity[p.id]?.valid).length }}/{{ props.pemilihans.length }} valid
                                </span>
                            </div>
                            <div class="w-full bg-blue-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-300"
                                    :class="{
                                        'bg-green-500': allPemilihanValid,
                                        'bg-blue-600': !allPemilihanValid
                                    }"
                                    :style="{ width: `${(props.pemilihans.filter(p => pemilihanValidity[p.id]?.valid).length / props.pemilihans.length) * 100}%` }">
                                </div>
                            </div>
                            <p v-if="!allPemilihanValid" class="text-xs text-amber-600 mt-2">
                                <AlertCircle class="w-3 h-3 inline mr-1" />
                                Beberapa pemilihan belum valid
                            </p>
                            <p v-else class="text-xs text-green-600 mt-2">
                                <Check class="w-3 h-3 inline mr-1" />
                                Semua pemilihan sudah valid. Siap submit!
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>