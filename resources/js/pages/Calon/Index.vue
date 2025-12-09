<!-- File: resources/js/Pages/Calon/Index.vue -->
<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Upload, Download } from 'lucide-vue-next';
import { ref } from 'vue';
import DataTable from './DataTable.vue';
import { calonColumn } from './column';
import ImportModal from './ImportModal.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Calon',
        href: '/calon',
    },
];

// PERBAIKAN: props.calons bukan props.pesertas
const props = defineProps<{
    calons: Array<any>; // <-- DIUBAH dari 'pesertas' ke 'calons'
    jabatanOptions: Array<string>;
}>();

console.log(props.calons); 

const showImportModal = ref(false);

const handleImportSuccess = () => {
    showImportModal.value = false;
    router.reload({ only: ['calons'] }); // <-- DIUBAH dari 'pesertas' ke 'calons'
};

const exportExcel = () => {
    window.location.href = '/calon/export';
};

const downloadTemplate = () => {
    window.location.href = '/calon/template/download';
};
</script>

<template>
    <Head title="Calon" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    class="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full">
                    <div class="flex flex-col relative w-full">
                        <div class="w-full">
                            <!-- TOMBOL TAMBAH DAN IMPORT -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">                               
                                <div class="flex ml-auto gap-2">
                                    
                                    <Button 
                                        @click="showImportModal = true" 
                                        class="bg-blue-600 hover:bg-blue-700"
                                    >
                                        <Upload class="mr-2 h-4 w-4" /> Import Excel
                                    </Button>
                                                                        <Link href="/calon/create">
                                        <Button
                                            class="cursor-pointer bg-[#a81b2c] text-white hover:border hover:bg-white hover:text-black"
                                            variant="default">
                                            <Plus /> Tambah Calon
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                            
                            <!-- Tabel Data -->
                            <!-- PERBAIKAN: props.calons bukan props.pesertas -->
                            <DataTable :columns="calonColumn" :data="props.calons" />
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Modal Import -->
        <ImportModal
            v-if="showImportModal"
            :show="showImportModal"
            :import-url="`calon/import`"
            title="Import Data Calon"
            :format-info="'Pastikan file Excel memiliki kolom: nama, asal_pimpinan, jenis_kelamin (L/P), nomor_urut, jabatan (Ketua/Formatur)'"
            @close="showImportModal = false"
            @success="handleImportSuccess"
        />
    </AppLayout>
</template>