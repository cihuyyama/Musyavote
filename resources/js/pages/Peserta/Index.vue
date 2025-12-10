<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Plus, Upload, Search, MoreVertical } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { Peserta, pesertaColumn } from './column';
import DataTable from './data-table.vue';
import ImportModal from './ImportModal.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Peserta',
        href: '/peserta',
    },
];

const props = defineProps<{
    pesertas: Peserta[];
}>();

const showImportModal = ref(false);
const searchQuery = ref('');

// Fungsi untuk handle search
const handleSearch = (value: string) => {
    searchQuery.value = value;
};

const handleImportSuccess = () => {
    showImportModal.value = false;
    router.reload({ only: ['pesertas'] });
};

const exportExcel = () => {
    window.location.href = '/pesertas/export';
};

const exportQr = () => {
    window.location.href = '/kartu-peserta/export/all';
};

// Fungsi untuk refresh data setelah update
const refreshData = () => {
    router.reload({ only: ['pesertas'] });
};

// Expose refreshData ke child components jika diperlukan
defineExpose({ refreshData });

onMounted(() => {
    console.log(props.pesertas);
});
</script>

<template>
    <Head title="Peserta" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-xl border-none w-full shadow-sm">
            <CardContent className="p-6 w-full">
                <!-- Header Section -->
                <div class="flex flex-col space-y-4 mb-8">
                    <!-- Search and Actions Row -->
                    <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                        <!-- Search Input di kiri -->
                        <div class="relative flex-1 max-w-md">
                            <Search
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                            <Input
                                class="w-full pl-10 pr-4 py-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-[#a81b2c] focus:border-transparent"
                                placeholder="Cari nama peserta..." :model-value="searchQuery"
                                @update:model-value="handleSearch" />
                        </div>

                        <!-- Action Buttons di kanan -->
                        <div class="flex items-center gap-2 w-full md:w-auto justify-between md:justify-end">
                            <!-- Desktop Actions (Import/Export) -->
                            <div class="hidden md:flex gap-2">
                                <Button @click="showImportModal = true"
                                    class="bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:border-gray-400 px-4 py-2 rounded-lg transition-colors">
                                    <Upload class="mr-2 h-4 w-4" /> Import
                                </Button>
                                <Button @click="exportExcel"
                                    class="bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:border-gray-400 px-4 py-2 rounded-lg transition-colors">
                                    <Download class="mr-2 h-4 w-4" /> Excel
                                </Button>
                                <Button @click="exportQr"
                                    class="bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:border-gray-400 px-4 py-2 rounded-lg transition-colors">
                                    <Download class="mr-2 h-4 w-4" /> Akun
                                </Button>
                            </div>

                            <!-- Tombol Tambah Peserta (selalu visible) -->
                            <Link href="/peserta/create">
                                <Button
                                    class="cursor-pointer bg-[#a81b2c] hover:bg-[#8c1523] text-white px-4 py-2 rounded-lg shadow-sm transition-all duration-200">
                                    <Plus class="mr-2 h-4 w-4" /> <span class="hidden sm:inline">Tambah
                                        Peserta</span>
                                    <span class="sm:hidden">Tambah</span>
                                </Button>
                            </Link>

                            <!-- Mobile Dropdown untuk Import/Export saja -->
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="md:hidden border-gray-300 rounded-lg p-2">
                                        <MoreVertical class="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-48">
                                    <DropdownMenuItem @click="showImportModal = true" class="cursor-pointer">
                                        <Download class="mr-2 h-4 w-4" />
                                        Import Data
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="exportExcel" class="cursor-pointer">
                                        <Upload class="mr-2 h-4 w-4" />
                                        Export Excel
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="exportQr" class="cursor-pointer">
                                        <Upload class="mr-2 h-4 w-4" />
                                        Export QR
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <!-- Import Modal -->
                    <ImportModal :show="showImportModal" :import-url="`pesertas/import`"
                        @close="showImportModal = false" @success="handleImportSuccess"
                        title="Unggah Data Peserta Baru" />

                    <!-- Data Table -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <DataTable :columns="pesertaColumn" :data="props.pesertas" :search-query="searchQuery" />
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>

<style scoped>
/* Animations for buttons */
.button-enter-active,
.button-leave-active {
    transition: all 0.2s ease;
}

.button-enter-from,
.button-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>