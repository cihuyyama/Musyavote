<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Peserta, pesertaColumn } from './column';
import DataTable from './data-table.vue';
import { Calendar, Users, CheckCircle } from 'lucide-vue-next';

const props = defineProps<{
    pesertas: Peserta[];
}>();

// Hitung statistik
const stats = computed(() => {
    const total = props.pesertas.length;
    const pleno1 = props.pesertas.filter(p => p.kehadiran.pleno_1 === 1).length;
    const pleno2 = props.pesertas.filter(p => p.kehadiran.pleno_2 === 1).length;
    const pleno3 = props.pesertas.filter(p => p.kehadiran.pleno_3 === 1).length;
    const pleno4 = props.pesertas.filter(p => p.kehadiran.pleno_4 === 1).length;
    
    return {
        total,
        pleno1,
        pleno2,
        pleno3,
        pleno4,
        persen1: total > 0 ? ((pleno1 / total) * 100).toFixed(1) : 0,
        persen2: total > 0 ? ((pleno2 / total) * 100).toFixed(1) : 0,
        persen3: total > 0 ? ((pleno3 / total) * 100).toFixed(1) : 0,
        persen4: total > 0 ? ((pleno4 / total) * 100).toFixed(1) : 0,
    };
});
</script>

<template>
    <Head title="Daftar Kehadiran" />
    
    <div class="min-h-screen bg-gray-50">
        <!-- NAVBAR SIMPLE -->
        <nav class="bg-white border-b shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-blue-50 rounded-lg">
                            <Users class="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">
                                DAFTAR KEHADIRAN
                            </h1>
                            <p class="text-sm text-gray-500">Peserta Musyawarah</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <div class="hidden sm:flex items-center space-x-1 text-sm text-gray-600">
                            <Calendar class="h-4 w-4" />
                            <span>{{ new Date().toLocaleDateString('id-ID', { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            }) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- STATISTIK CARDS -->
            <div class="mb-6 grid grid-cols-2 md:grid-cols-6 gap-4">
                <!-- Total Peserta -->
                <div class="col-span-2 md:col-span-1 bg-white rounded-lg border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Peserta</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ stats.total }}
                            </p>
                        </div>
                        <Users class="h-8 w-8 text-blue-500" />
                    </div>
                </div>
                
                <!-- Pleno 1 -->
                <div class="bg-white rounded-lg border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pleno 1</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                {{ stats.pleno1 }}
                                <span class="text-sm font-normal text-gray-500 ml-1">
                                    ({{ stats.persen1 }}%)
                                </span>
                            </p>
                        </div>
                        <CheckCircle class="h-6 w-6 text-green-500" />
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div 
                            class="bg-green-500 h-1.5 rounded-full" 
                            :style="{ width: `${stats.persen1}%` }"
                        ></div>
                    </div>
                </div>
                
                <!-- Pleno 2 -->
                <div class="bg-white rounded-lg border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pleno 2</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                {{ stats.pleno2 }}
                                <span class="text-sm font-normal text-gray-500 ml-1">
                                    ({{ stats.persen2 }}%)
                                </span>
                            </p>
                        </div>
                        <CheckCircle class="h-6 w-6 text-green-500" />
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div 
                            class="bg-green-500 h-1.5 rounded-full" 
                            :style="{ width: `${stats.persen2}%` }"
                        ></div>
                    </div>
                </div>
                
                <!-- Pleno 3 -->
                <div class="bg-white rounded-lg border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pleno 3</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                {{ stats.pleno3 }}
                                <span class="text-sm font-normal text-gray-500 ml-1">
                                    ({{ stats.persen3 }}%)
                                </span>
                            </p>
                        </div>
                        <CheckCircle class="h-6 w-6 text-green-500" />
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div 
                            class="bg-green-500 h-1.5 rounded-full" 
                            :style="{ width: `${stats.persen3}%` }"
                        ></div>
                    </div>
                </div>
                
                <!-- Pleno 4 -->
                <div class="bg-white rounded-lg border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pleno 4</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                {{ stats.pleno4 }}
                                <span class="text-sm font-normal text-gray-500 ml-1">
                                    ({{ stats.persen4 }}%)
                                </span>
                            </p>
                        </div>
                        <CheckCircle class="h-6 w-6 text-green-500" />
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div 
                            class="bg-green-500 h-1.5 rounded-full" 
                            :style="{ width: `${stats.persen4}%` }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- TABEL -->
            <Card className="rounded-lg border shadow-sm">
                <CardContent className="p-4 md:p-6">
                    <!-- Legend Status -->
                    <div class="flex flex-wrap items-center gap-4 mb-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full bg-green-100 flex items-center justify-center mr-2">
                                <div class="w-2 h-2 rounded-full bg-green-600"></div>
                            </div>
                            <span class="text-gray-600">Hadir</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full bg-gray-100 flex items-center justify-center mr-2">
                                <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                            </div>
                            <span class="text-gray-600">Tidak Hadir</span>
                        </div>
                    </div>
                    
                    <DataTable
                        :columns="pesertaColumn"
                        :data="props.pesertas"
                    />
                </CardContent>
            </Card>
        </main>
    </div>
</template>