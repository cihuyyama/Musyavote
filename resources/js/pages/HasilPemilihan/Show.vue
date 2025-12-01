<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import { Users, BarChart3, Download, ArrowLeft, Trophy, Target } from 'lucide-vue-next';

const props = defineProps<{
    pemilihan: any;
    hasil: any[];
    statistik: any;
}>();

const exportLoading = ref(false);

const handleExportPDF = async () => {
    exportLoading.value = true;
    try {
        // Download PDF langsung
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
        name: item.calon.peserta.nama,
        votes: item.jumlah_suara,
        percentage: item.persentase
    }));
});

// Calon terpilih (formatur)
const calonTerpilih = computed(() => {
    const jumlahFormatur = props.pemilihan.jumlah_formatur_terpilih;
    return props.hasil.slice(0, jumlahFormatur);
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
</script>

<template>

    <Head :title="`Hasil - ${pemilihan.nama_pemilihan}`" />
    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <Link :href="`/hasil-pemilihan`">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Kembali
                        </Button>
                        </Link>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ pemilihan.nama_pemilihan }}</h1>
                            <p class="text-gray-600">Hasil akhir pemilihan formatur</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" @click="handleExportPDF" :disabled="exportLoading">
                            <Download class="w-4 h-4 mr-2" />
                            {{ exportLoading ? 'Mengekspor...' : 'Export PDF' }}
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Panel Kiri: Statistik -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Statistik Utama -->
                        <Card class="shadow-lg">
                            <CardContent class="pt-6">
                                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <BarChart3 class="w-5 h-5" />
                                    Statistik Pemilihan
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                        <span class="text-blue-800 font-medium">Total Peserta</span>
                                        <span class="text-blue-800 font-bold">{{ statistik.total_peserta }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                        <span class="text-green-800 font-medium">Sudah Memilih</span>
                                        <span class="text-green-800 font-bold">{{ statistik.memilih }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                        <span class="text-yellow-800 font-medium">Tidak Memilih</span>
                                        <span class="text-yellow-800 font-bold">{{ statistik.tidak_memilih }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                        <span class="text-red-800 font-medium">Belum Memilih</span>
                                        <span class="text-red-800 font-bold">{{ statistik.belum_memilih }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                        <span class="text-purple-800 font-medium">Partisipasi</span>
                                        <span class="text-purple-800 font-bold">{{ statistik.persentase_partisipasi
                                            }}%</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Formatur Terpilih -->
                        <Card class="shadow-lg" v-if="calonTerpilih.length > 0">
                            <CardContent class="pt-6">
                                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <Trophy class="w-5 h-5 text-yellow-600" />
                                    Formatur Terpilih
                                </h3>
                                <div class="space-y-3">
                                    <div v-for="(item, index) in calonTerpilih" :key="item.calon.id"
                                        class="flex items-center gap-3 p-3 bg-green-50 rounded-lg border border-green-200">
                                        <div
                                            class="shrink-0 w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">{{ index + 1 }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-green-900 text-sm truncate">
                                                {{ item.calon.peserta.nama }}
                                            </h4>
                                            <p class="text-green-700 text-xs">
                                                {{ item.jumlah_suara }} suara ({{ formatPersentase(item.persentase) }})
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Panel Kanan: Hasil Detail -->
                    <div class="lg:col-span-2">
                        <!-- Daftar Hasil Lengkap -->
                        <Card class="shadow-lg">
                            <CardContent class="pt-6">
                                <h3 class="text-xl font-semibold text-gray-900 mb-6">
                                    Hasil Lengkap Pemilihan
                                </h3>

                                <div class="space-y-4">
                                    <div v-for="item in hasil" :key="item.calon.id"
                                        class="border rounded-lg p-4 transition-all duration-200 hover:shadow-md"
                                        :class="item.peringkat <= pemilihan.jumlah_formatur_terpilih ? 'border-green-300 bg-green-50' : 'border-gray-200'">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4 flex-1">
                                                <!-- Peringkat -->
                                                <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold"
                                                    :class="getRankColor(item.peringkat)">
                                                    {{ item.peringkat }}
                                                </div>

                                                <!-- Foto & Info Calon -->
                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                    <div
                                                        class="shrink-0 w-12 h-12 rounded-full border border-gray-300 overflow-hidden bg-gray-200">
                                                        <img v-if="item.calon.peserta.foto"
                                                            :src="item.calon.peserta.foto.startsWith('http') ? item.calon.peserta.foto : `/storage/${item.calon.peserta.foto}`"
                                                            alt="Foto Calon" class="w-full h-full object-cover" />
                                                        <div v-else
                                                            class="w-full h-full flex items-center justify-center bg-gray-300">
                                                            <Users class="w-6 h-6 text-gray-500" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="font-semibold text-gray-900 truncate">
                                                            {{ item.calon.peserta.nama }}
                                                        </h4>
                                                        <p class="text-gray-600 text-sm truncate">
                                                            {{ item.calon.peserta.asal_pimpinan }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Statistik Suara -->
                                            <div class="text-right shrink-0">
                                                <div class="text-2xl font-bold text-blue-600">
                                                    {{ item.jumlah_suara }}
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    {{ formatPersentase(item.persentase) }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="mt-3">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-xs text-gray-600">Persentase Suara</span>
                                                <span class="text-xs font-medium text-gray-600">
                                                    {{ item.jumlah_suara }} suara
                                                </span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                                                    :class="item.peringkat <= pemilihan.jumlah_formatur_terpilih ? 'bg-green-600' : 'bg-blue-600'"
                                                    :style="{ width: `${item.persentase}%` }"></div>
                                            </div>
                                        </div>

                                        <!-- Badge Formatur Terpilih -->
                                        <div v-if="item.peringkat <= pemilihan.jumlah_formatur_terpilih"
                                            class="mt-2 flex justify-end">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                                <Target class="w-3 h-3" />
                                                Formatur Terpilih
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary -->
                                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-blue-900">Total Suara Sah</h4>
                                            <p class="text-blue-700 text-sm">
                                                {{ statistik.memilih }} suara dari {{ statistik.total_peserta }} peserta
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold text-blue-600">
                                                {{ statistik.persentase_partisipasi }}%
                                            </div>
                                            <div class="text-blue-700 text-sm">Tingkat Partisipasi</div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Chart Visualization (Placeholder) -->
                        <Card class="mt-6 shadow-lg">
                            <CardContent class="pt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Visualisasi Hasil</h3>
                                <div class="bg-gray-100 rounded-lg p-8 text-center">
                                    <BarChart3 class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                    <p class="text-gray-600">Chart visualization akan ditampilkan di sini</p>
                                    <p class="text-gray-500 text-sm mt-2">
                                        (Integrasi dengan library chart seperti Chart.js atau ApexCharts)
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>