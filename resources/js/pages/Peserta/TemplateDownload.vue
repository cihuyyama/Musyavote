<!-- ExcelTemplateDownload.vue -->
<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Download } from 'lucide-vue-next';

const downloadExcelTemplate = async () => {
    try {
        // Data template
        const templateData = [
            {
                nama: 'Ahmad Rizki',
                asal_pimpinan: 'DPD Jakarta Selatan',
                jenis_kelamin: 'L',
                status: 'Aktif',
            },
            {
                nama: 'Siti Nurhaliza',
                asal_pimpinan: 'DPC Bandung',
                jenis_kelamin: 'P',
                status: 'Aktif',
            },
            {
                nama: 'Budi Santoso',
                asal_pimpinan: 'DPD Surabaya',
                jenis_kelamin: 'L',
                status: 'Tidak Aktif',
            },
            {
                nama: 'Maya Sari',
                asal_pimpinan: 'DPC Yogyakarta',
                jenis_kelamin: 'P',
                status: 'Aktif',
            },
            {
                nama: 'Rizki Pratama',
                asal_pimpinan: 'DPD Medan',
                jenis_kelamin: 'L',
                status: 'Aktif',
            },
        ];

        // Import library xlsx secara dinamis
        const XLSX = await import('xlsx');

        // Buat workbook baru
        const workbook = XLSX.utils.book_new();

        // Siapkan data dengan header
        const worksheetData = [
            // Header
            ['nama', 'asal_pimpinan', 'jenis_kelamin', 'status'],
            // Data
            ...templateData.map((item) => [
                item.nama,
                item.asal_pimpinan,
                item.jenis_kelamin,
                item.status,
            ]),
        ];

        // Buat worksheet dari data
        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);

        // Style untuk header (opsional)
        if (!worksheet['!cols']) worksheet['!cols'] = [];
        worksheet['!cols'] = [
            { width: 20 }, // nama
            { width: 25 }, // asal_pimpinan
            { width: 15 }, // jenis_kelamin
            { width: 15 }, // status
        ];

        // Tambahkan worksheet ke workbook
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Data Peserta');

        // Buat file Excel
        XLSX.writeFile(workbook, 'template-import-peserta.xlsx');
    } catch (error) {
        console.error('Error generating Excel template:', error);
        // Fallback ke CSV jika xlsx gagal
        downloadCSVTemplate();
    }
};

const downloadCSVTemplate = () => {
    const templateData = [
        ['nama', 'asal_pimpinan', 'jenis_kelamin', 'status'],
        ['Ahmad Rizki', 'DPD Jakarta Selatan', 'L', 'Aktif'],
        ['Siti Nurhaliza', 'DPC Bandung', 'P', 'Aktif'],
        ['Budi Santoso', 'DPD Surabaya', 'L', 'Non Aktif'],
        ['Maya Sari', 'DPC Yogyakarta', 'P', 'Aktif'],
        ['Rizki Pratama', 'DPD Medan', 'L', 'Aktif'],
    ];

    const csvContent = templateData
        .map((row) => row.map((field) => `"${field}"`).join(','))
        .join('\n');

    const blob = new Blob(['\uFEFF' + csvContent], {
        type: 'text/csv;charset=utf-8;',
    });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', 'template-import-peserta.csv');
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Button
        type="button"
        variant="outline"
        @click="downloadExcelTemplate"
        class="flex items-center gap-2"
    >
        <Download class="h-4 w-4" />
        Download Template Excel
    </Button>
</template>
