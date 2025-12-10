<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import { Check, User, LogOut, Clock, Vote } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps<{
    peserta: any;
    pemilihans: any[]; // Array of pemilihan
    bilik: any;
    pemilihanStatus: any[];
}>();

console.log(props);

// State untuk setiap pemilihan
const selectedCalon = ref<{ [pemilihanId: string]: string[] }>({});
const tidakMemilih = ref<{ [pemilihanId: string]: boolean }>({});
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

// Nama calon yang dipilih untuk setiap pemilihan
const selectedCalonNames = computed(() => {
    const names: { [pemilihanId: string]: string[] } = {};

    props.pemilihans.forEach(pemilihan => {
        const calonIds = selectedCalon.value[pemilihan.id] || [];
        names[pemilihan.id] = pemilihan.calon
            .filter(calon => calonIds.includes(calon.id))
            .map(calon => calon.peserta.nama);
    });

    return names;
});

// Pemilihan yang memiliki pilihan (untuk menghindari v-if dengan v-for)
const pemilihanWithChoices = computed(() => {
    return props.pemilihans.filter(pemilihan => {
        return selectedCalonNames.value[pemilihan.id]?.length > 0 || tidakMemilih.value[pemilihan.id];
    });
});

// Inisialisasi state untuk setiap pemilihan
const initializePemilihanState = () => {
    props.pemilihans.forEach(pemilihan => {
        if (!selectedCalon.value[pemilihan.id]) {
            selectedCalon.value[pemilihan.id] = [];
        }
        if (tidakMemilih.value[pemilihan.id] === undefined) {
            tidakMemilih.value[pemilihan.id] = false;
        }
    });
};

const toggleCalon = (pemilihanId: string, calonId: string) => {
    if (!selectedCalon.value[pemilihanId]) {
        selectedCalon.value[pemilihanId] = [];
    }

    const currentSelection = selectedCalon.value[pemilihanId];
    const pemilihan = props.pemilihans.find(p => p.id === pemilihanId);

    if (currentSelection.includes(calonId)) {
        selectedCalon.value[pemilihanId] = currentSelection.filter(id => id !== calonId);
    } else {
        if (currentSelection.length < pemilihan.jumlah_formatur_terpilih) {
            selectedCalon.value[pemilihanId].push(calonId);
            // Jika memilih calon, set tidak memilih menjadi false
            tidakMemilih.value[pemilihanId] = false;
        } else {
            toast.error(`Maksimal memilih ${pemilihan.jumlah_formatur_terpilih} calon untuk ${pemilihan.nama_pemilihan}`);
        }
    }
};

const toggleTidakMemilih = (pemilihanId: string) => {
    const newValue = !tidakMemilih.value[pemilihanId];
    tidakMemilih.value[pemilihanId] = newValue;

    // Jika memilih tidak memilih, clear semua pilihan calon
    if (newValue) {
        selectedCalon.value[pemilihanId] = [];
    }
};

const submitVoting = async () => {
    // Validasi untuk setiap pemilihan
    for (const pemilihan of props.pemilihans) {
        const hasSelection = (selectedCalon.value[pemilihan.id]?.length || 0) > 0;
        const isTidakMemilih = tidakMemilih.value[pemilihan.id] || false;

        if (!hasSelection && !isTidakMemilih) {
            toast.error(`Silakan pilih calon atau pilih tidak memilih untuk ${pemilihan.nama_pemilihan}`);
            return;
        }

        if (isTidakMemilih && !pemilihan.boleh_tidak_memilih) {
            toast.error(`Tidak memilih tidak diizinkan untuk ${pemilihan.nama_pemilihan}`);
            return;
        }
    }

    isLoading.value = true;

    try {
        // Format data untuk semua pemilihan
        const pilihanData = props.pemilihans.map(pemilihan => ({
            pemilihan_id: pemilihan.id,
            calon_ids: selectedCalon.value[pemilihan.id] || [],
            tidak_memilih: tidakMemilih.value[pemilihan.id] || false
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
            if (currentProgress.value > 0) {
                // submitVoting();
                toast.error('Waktu habis! Silakan submit pilihan Anda atau anda akan diculik prabowo.');
            }
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

                            <!-- Logout Button -->
                            <Button @click="logout" variant="outline" size="sm">
                                <LogOut class="w-4 h-4 mr-2" />
                                Keluar
                            </Button>
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
                                <p class="text-sm"><span class="font-medium">Kode Unik:</span> {{ peserta.asal_pimpinan
                                    }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pilihan Anda untuk Setiap Pemilihan -->
                    <Card v-for="pemilihan in pemilihanWithChoices" :key="pemilihan.id" class="shadow-lg">
                        <CardContent class="pt-6">
                            <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <Vote class="w-4 h-4" />
                                {{ pemilihan.nama_pemilihan }}
                            </h3>

                            <div v-if="tidakMemilih[pemilihan.id]"
                                class="flex items-center gap-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <span class="text-yellow-800 font-medium">Tidak Memilih</span>
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="(calonName, index) in selectedCalonNames[pemilihan.id]" :key="index"
                                    class="flex items-center gap-3 p-2 bg-green-50 rounded-lg border border-green-200">
                                    <Check class="w-4 h-4 text-green-600 shrink-0" />
                                    <span class="text-green-800 text-sm">{{ calonName }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit Button -->
                    <Card class="shadow-lg sticky top-6">
                        <CardContent class="pt-6">
                            <Button @click="submitVoting" class="w-full" size="lg"
                                :disabled="isLoading || currentProgress === 0" :class="{
                                    'bg-green-600 hover:bg-green-700': currentProgress > 0,
                                    'bg-blue-600 hover:bg-blue-700': currentProgress === 0
                                }">
                                <span v-if="isLoading">Menyimpan...</span>
                                <span v-else>
                                    {{ currentProgress > 0 ? 'Konfirmasi Semua Pilihan' : 'Submit Voting' }}
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
                                            Pilih maksimal {{ pemilihan.jumlah_formatur_terpilih }} calon
                                            <span v-if="pemilihan.boleh_tidak_memilih" class="text-green-600 ml-2">•
                                                Boleh tidak memilih</span>
                                            <span v-else class="text-red-600 ml-2">• Wajib memilih</span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-600">
                                            Terpilih: {{ selectedCalon[pemilihan.id]?.length || 0 }}/{{
                                            pemilihan.jumlah_formatur_terpilih }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Opsi Tidak Memilih -->
                                <div class="mb-4 p-4 bg-gray-50 rounded-lg" v-if="pemilihan.boleh_tidak_memilih">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" :checked="tidakMemilih[pemilihan.id]"
                                            @change="toggleTidakMemilih(pemilihan.id)"
                                            class="w-5 h-5 text-blue-600 rounded mt-1"
                                            :disabled="(selectedCalon[pemilihan.id]?.length || 0) > 0" />
                                        <div>
                                            <span class="text-gray-700 font-medium block">Saya memilih untuk TIDAK
                                                MEMILIH</span>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Jika memilih opsi ini, Anda tidak akan memilih siapapun dalam pemilihan
                                                ini.
                                            </p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Daftar Calon -->
                                <div v-if="!tidakMemilih[pemilihan.id]"
                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="calon in pemilihan.calon" :key="calon.id"
                                        class="border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 hover:shadow-md"
                                        :class="{
                                            'border-green-500 bg-green-50 shadow-md': selectedCalon[pemilihan.id]?.includes(calon.id),
                                            'border-gray-200 bg-white hover:border-gray-300': !selectedCalon[pemilihan.id]?.includes(calon.id)
                                        }" @click="toggleCalon(pemilihan.id, calon.id)">
                                        <div class="flex items-center gap-3">
                                            <!-- Foto Calon -->
                                            <div class="shrink-0">
                                                <div
                                                    class="w-12 h-12 rounded-full border-2 border-gray-300 overflow-hidden bg-gray-200 flex items-center justify-center">
                                                    <User v-if="!calon.peserta.foto" class="w-6 h-6 text-gray-500" />
                                                    <img v-else
                                                        :src="calon.peserta.foto.startsWith('http') ? calon.peserta.foto : `/storage/${calon.peserta.foto}`"
                                                        alt="Foto Calon" class="w-full h-full object-cover" />
                                                </div>
                                            </div>

                                            <!-- Nama Calon -->
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-gray-900 text-sm truncate">{{
                                                    calon.peserta.nama }}</h3>
                                                <p class="text-xs text-gray-500 truncate">{{ calon.peserta.asal_pimpinan
                                                    || 'Calon Formatur' }}</p>
                                            </div>

                                            <!-- Check Indicator -->
                                            <div v-if="selectedCalon[pemilihan.id]?.includes(calon.id)"
                                                class="shrink-0 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                                <Check class="w-3 h-3 text-white" />
                                            </div>
                                            <div v-else class="shrink-0 w-6 h-6 border-2 border-gray-300 rounded-full">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Indicator per Pemilihan -->
                                <div class="mt-4 p-3 bg-blue-50 rounded-lg" v-if="!tidakMemilih[pemilihan.id]">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-medium text-blue-900">Progress {{
                                            pemilihan.nama_pemilihan }}</span>
                                        <span class="text-xs font-medium text-blue-900">
                                            {{ selectedCalon[pemilihan.id]?.length || 0 }}/{{
                                            pemilihan.jumlah_formatur_terpilih }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-blue-200 rounded-full h-1.5">
                                        <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                                            :style="{ width: `${((selectedCalon[pemilihan.id]?.length || 0) / pemilihan.jumlah_formatur_terpilih) * 100}%` }">
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Total Progress Indicator -->
                    <Card class="mt-6 shadow-lg">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-blue-900">Total Progress Semua Pemilihan</span>
                                <span class="text-sm font-medium text-blue-900">{{ currentProgress }}/{{ totalProgress
                                    }}</span>
                            </div>
                            <div class="w-full bg-blue-200 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300"
                                    :style="{ width: `${(currentProgress / totalProgress) * 100}%` }"></div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>