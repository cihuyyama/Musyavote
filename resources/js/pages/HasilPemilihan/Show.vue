<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import { Users, BarChart3, Download, ArrowLeft, ChartBar, ListOrdered } from 'lucide-vue-next';

const props = defineProps<{
    pemilihan: any;
    hasil: any[];
    statistik: any;
}>();

const exportLoading = ref(false);
const activeView = ref<'statistik' | 'hasil'>('statistik');

const handleExportPDF = async () => {
    exportLoading.value = true;
    try {
        window.open(`/hasil-pemilihan/${props.pemilihan.id}/export/pdf`, '_blank');
    } catch (error) {
        console.error('Export error:', error);
    } finally {
        exportLoading.value = false;
    }
};

// Chart data untuk visualisasi
const chartData = computed(() => {
    return props.hasil.map(item => ({
        name: item.calon.nama,
        votes: item.jumlah_suara,
        percentage: item.persentase
    }));
});

// Format persentase
const formatPersentase = (persentase: number) => {
    return `${persentase.toFixed(1)}%`;
};

// Warna untuk peringkat
const getRankColor = (peringkat: number) => {
    const colors = {
        1: 'bg-yellow-100 text-yellow-800 border-yellow-300',
        2: 'bg-gray-100 text-gray-800 border-gray-300',
        3: 'bg-orange-100 text-orange-800 border-orange-300',
        default: 'bg-blue-100 text-blue-800 border-blue-300'
    };

    return colors[peringkat as keyof typeof colors] || colors.default;
};

// Helper untuk mendapatkan URL foto calon
const getFotoUrl = (calon: any) => {
    return calon.foto_url || '/default-avatar.png';
};
</script>

<template>

    <Head :title="`Hasil - ${pemilihan.nama_pemilihan}`" />
    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-4 sm:py-6">
            <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <Link :href="`/hasil-pemilihan`">
                            <Button variant="outline" size="sm" class="h-8 px-3">
                                <ArrowLeft class="w-3.5 h-3.5 mr-1.5" />
                                <span class="text-sm">Kembali</span>
                            </Button>
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">
                                {{ pemilihan.nama_pemilihan }}
                            </h1>
                            <p class="text-gray-600 text-sm">Hasil akhir pemilihan formatur</p>
                        </div>
                    </div>
                    <!-- <div class="flex gap-2">
                        <Button 
                            variant="outline" 
                            @click="handleExportPDF" 
                            :disabled="exportLoading"
                            class="h-8 px-3 text-sm"
                        >
                            <Download class="w-3.5 h-3.5 mr-1.5" />
                            {{ exportLoading ? 'Mengekspor...' : 'Export PDF' }}
                        </Button>
                    </div> -->
                </div>

                <!-- Switch Toggle -->
                <div class="mb-6">
                    <div class="flex items-center justify-center">
                        <div
                            class="relative inline-flex items-center bg-white rounded-lg shadow-sm p-1 border border-gray-200">
                            <!-- Background sliding -->
                            <div class="absolute top-1 bottom-1 w-1/2 bg-gradient-to-r from-[#A81B2C] to-[#8A1524] rounded-md transition-all duration-300 ease-in-out"
                                :class="activeView === 'hasil' ? 'translate-x-full' : 'translate-x-0'"></div>

                            <!-- Statistik Button -->
                            <button @click="activeView = 'statistik'"
                                class="relative flex items-center gap-1.5 px-4 py-2 rounded-md transition-all duration-200 z-10 min-w-[140px] justify-center text-sm"
                                :class="activeView === 'statistik' ? 'text-white' : 'text-gray-600 hover:text-gray-900'">
                                <ChartBar class="w-4 h-4" />
                                <span class="font-medium">Statistik</span>
                            </button>

                            <!-- Hasil Button -->
                            <button @click="activeView = 'hasil'"
                                class="relative flex items-center gap-1.5 px-4 py-2 rounded-md transition-all duration-200 z-10 min-w-[140px] justify-center text-sm"
                                :class="activeView === 'hasil' ? 'text-white' : 'text-gray-600 hover:text-gray-900'">
                                <ListOrdered class="w-4 h-4" />
                                <span class="font-medium">Hasil Lengkap</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- View Statistik -->
                <div v-if="activeView === 'statistik'">
                    <Card class="shadow-md">
                        <CardContent class="p-4 sm:p-6">
                            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2 text-base sm:text-lg">
                                <BarChart3 class="w-4 h-4 sm:w-5 sm:h-5" />
                                Statistik Pemilihan
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div
                                    class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div
                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-blue-100 flex items-center justify-center">
                                            <Users class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="text-xs text-blue-800 font-medium">Total Peserta</p>
                                            <p class="text-xl sm:text-2xl font-bold text-blue-900">
                                                {{ statistik.total_peserta }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-blue-700 mt-1">Jumlah keseluruhan peserta pemilihan</p>
                                </div>

                                <div
                                    class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div
                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-green-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-green-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-green-800 font-medium">Sudah Memilih</p>
                                            <p class="text-xl sm:text-2xl font-bold text-green-900">
                                                {{ statistik.memilih }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-green-700 mt-1">Peserta yang telah memberikan suara</p>
                                </div>

                                <div
                                    class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div
                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-red-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-red-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-red-800 font-medium">Belum Memilih</p>
                                            <p class="text-xl sm:text-2xl font-bold text-red-900">
                                                {{ statistik.belum_memilih }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-red-700 mt-1">Peserta yang belum memberikan suara</p>
                                </div>

                                <div
                                    class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div
                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-purple-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-purple-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-purple-800 font-medium">Partisipasi</p>
                                            <p class="text-xl sm:text-2xl font-bold text-purple-900">
                                                {{ statistik.persentase_partisipasi }}%
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-purple-700 mt-1">Persentase partisipasi pemilih</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- View Hasil Lengkap -->
                <div v-else>
                    <Card class="shadow-md">
                        <CardContent class="p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">
                                Hasil Lengkap Pemilihan
                            </h3>

                            <div class="space-y-3">
                                <div v-for="item in hasil" :key="item.calon.id"
                                    class="border rounded-lg p-3 sm:p-4 transition-all duration-200 hover:shadow-sm"
                                    :class="item.peringkat <= pemilihan.jumlah_formatur_terpilih
                                        ? 'border-green-300 bg-gradient-to-r from-green-50 to-emerald-50'
                                        : 'border-gray-200 bg-white'">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <!-- Peringkat -->
                                            <div class="shrink-0 w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center border-2 font-bold text-sm shadow-sm"
                                                :class="getRankColor(item.peringkat)">
                                                {{ item.peringkat }}
                                            </div>

                                            <!-- Foto & Info Calon -->
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                <div
                                                    class="shrink-0 w-10 h-10 sm:w-11 sm:h-11 rounded-full border border-gray-300 overflow-hidden bg-gray-200 shadow-sm">
                                                    <img v-if="item.calon.foto_url || item.calon.foto"
                                                        :src="getFotoUrl(item.calon)" alt="Foto Calon"
                                                        class="w-full h-full object-cover" />
                                                    <div v-else
                                                        class="w-full h-full flex items-center justify-center bg-gray-300">
                                                        <Users class="w-5 h-5 text-gray-500" />
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4
                                                        class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                                        {{ item.calon.nama }}
                                                    </h4>
                                                    <p class="text-gray-600 text-xs sm:text-sm truncate">
                                                        {{ item.calon.asal_pimpinan }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Statistik Suara -->
                                        <div class="text-right shrink-0">
                                            <div class="text-lg sm:text-xl font-bold text-blue-600">
                                                {{ item.jumlah_suara }}
                                            </div>
                                            <div class="text-xs sm:text-sm text-gray-600">
                                                {{ formatPersentase(item.persentase) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mt-2 sm:mt-3">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs text-gray-600">Persentase Suara</span>
                                            <span class="text-xs font-medium text-gray-600">
                                                {{ item.jumlah_suara }} suara
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 sm:h-2">
                                            <div class="h-1.5 sm:h-2 rounded-full transition-all duration-500" :class="item.peringkat <= pemilihan.jumlah_formatur_terpilih
                                                ? 'bg-gradient-to-r from-green-500 to-emerald-600'
                                                : 'bg-gradient-to-r from-blue-500 to-cyan-600'"
                                                :style="{ width: `${item.persentase}%` }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div
                                class="mt-4 sm:mt-6 p-3 sm:p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-100">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <h4 class="font-semibold text-blue-900 text-sm sm:text-base">Total Suara Sah
                                        </h4>
                                        <p class="text-blue-700 text-xs sm:text-sm">
                                            {{ statistik.memilih }} suara dari {{ statistik.total_peserta }} peserta
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xl sm:text-2xl font-bold text-blue-600">
                                            {{ statistik.persentase_partisipasi }}%
                                        </div>
                                        <div class="text-blue-700 text-xs sm:text-sm">Tingkat Partisipasi</div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>