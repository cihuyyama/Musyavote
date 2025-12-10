<!-- File: resources/js/Pages/Calon/ImportModal.vue -->
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Download,
    FileSpreadsheet,
    Loader2,
    Upload,
    CheckCircle,
    XCircle,
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';

// --- PROPS & EMITS ---
const props = defineProps({
    importUrl: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: 'Import Data Calon',
    },
    show: {
        type: Boolean,
        default: false,
    },
    formatInfo: {
        type: String,
        default: 'Format file Excel untuk import data calon.',
    },
});

const emit = defineEmits(['close', 'success']);
const page = usePage<any>();

// --- FORM LOGIC ---
const form = useForm<{ file_excel: File | null }>({
    file_excel: null,
});

const fileInput = ref<HTMLInputElement | null>(null);
const fileName = ref<string>('');
const isProcessing = ref(false);

// Watch for form processing state
watch(
    () => form.processing,
    (processing) => {
        isProcessing.value = processing;
    },
);

const submit = () => {
    if (!form.file_excel) return;

    isProcessing.value = true;

    form.post(props.importUrl, {
        forceFormData: true,
        onError: (errors) => {
            console.error('Import errors:', errors);
            resetForm();
            isProcessing.value = false;
        },
        onSuccess: () => {
            resetForm();
            isProcessing.value = false;
            emit('success');
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];

        // Validasi file type
        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv',
        ];

        if (
            !validTypes.includes(file.type) &&
            !file.name.match(/\.(xlsx|xls|csv)$/)
        ) {
            form.setError(
                'file_excel',
                'File harus berformat Excel atau CSV (.xlsx, .xls, .csv)',
            );
            return;
        }

        // Validasi file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            form.setError('file_excel', 'File terlalu besar. Maksimal 10MB');
            return;
        }

        form.file_excel = file;
        fileName.value = file.name;
        form.clearErrors('file_excel');
    }
};

const resetForm = () => {
    form.reset('file_excel');
    fileName.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const close = () => {
    if (!isProcessing.value) {
        resetForm();
        form.clearErrors();
        emit('close');
    }
};

const handleOpenChange = (open: boolean) => {
    if (!open && !isProcessing.value) {
        close();
    }
};

// Template untuk calon (5 kolom)
const downloadExcelTemplate = async () => {
    try {
        // Data template untuk calon - 5 KOLOM
        const templateData = [
            {
                nama: 'Ahmad Rizki',
                asal_pimpinan: 'DPD DIY',
                jenis_kelamin: 'L',
                nomor_urut: 1,
                jabatan: 'Ketua',
            },
            {
                nama: 'Siti Nurhaliza',
                asal_pimpinan: 'DPD DIY',
                jenis_kelamin: 'P',
                nomor_urut: 2,
                jabatan: 'Formatur',
            },
            {
                nama: 'Budi Santoso',
                asal_pimpinan: 'DPD DIY',
                jenis_kelamin: 'L',
                nomor_urut: 1,
                jabatan: 'Ketua',
            },
        ];

        // Import library xlsx
        const XLSX = await import('xlsx');

        // Buat workbook dan worksheet
        const workbook = XLSX.utils.book_new();

        // Siapkan data dengan header - 5 KOLOM untuk calon
        const worksheetData = [
            ['nama', 'asal_pimpinan', 'jenis_kelamin', 'nomor_urut', 'jabatan'],
            ...templateData.map((item) => [
                item.nama,
                item.asal_pimpinan,
                item.jenis_kelamin,
                item.nomor_urut,
                item.jabatan,
            ]),
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);

        // Set column widths
        worksheet['!cols'] = [
            { width: 25 }, // nama
            { width: 30 }, // asal_pimpinan
            { width: 15 }, // jenis_kelamin
            { width: 12 }, // nomor_urut
            { width: 15 }, // jabatan
        ];

        // Format header
        const headerRange = XLSX.utils.decode_range(worksheet['!ref'] || 'A1:E1');
        for (let C = headerRange.s.c; C <= headerRange.e.c; ++C) {
            const cellAddress = XLSX.utils.encode_cell({ r: 0, c: C });
            if (!worksheet[cellAddress]) continue;
            worksheet[cellAddress].s = {
                font: { bold: true, color: { rgb: "FFFFFF" } },
                fill: { fgColor: { rgb: "4F46E5" } },
                alignment: { horizontal: "center" }
            };
        }

        // Tambahkan worksheet
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Data Calon');

        // Buat sheet panduan
        const guideData = [
            ['PANDUAN IMPORT DATA CALON'],
            [''],
            ['FORMAT KOLOM:'],
            ['nama', 'Nama lengkap calon (text, wajib)'],
            ['asal_pimpinan', 'Asal pimpinan/organisasi (text, wajib)'],
            ['jenis_kelamin', 'L (Laki-laki) atau P (Perempuan)'],
            ['nomor_urut', 'Angka positif unik per jabatan (wajib)'],
            ['jabatan', 'Ketua atau Formatur (wajib)'],
            [''],
            ['CATATAN PENTING:'],
            ['1. Nomor urut harus unik untuk setiap jabatan'],
            ['2. Contoh: Dua calon Ketua tidak boleh memiliki nomor urut yang sama'],
            ['3. Contoh: Calon Formatur dan Ketua boleh memiliki nomor urut yang sama'],
            ['4. Jenis kelamin harus "L" atau "P" (huruf besar)'],
            [''],
            ['CONTOH DATA YANG BENAR:'],
            ['nama', 'asal_pimpinan', 'jenis_kelamin', 'nomor_urut', 'jabatan'],
            ...worksheetData.slice(1),
        ];

        const guideSheet = XLSX.utils.aoa_to_sheet(guideData);
        
        // Merge cells untuk judul panduan
        guideSheet['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 4 } }
        ];
        
        XLSX.utils.book_append_sheet(workbook, guideSheet, 'Panduan');

        // Download file
        XLSX.writeFile(workbook, 'template-import-calon.xlsx');
    } catch (error) {
        console.error('Error generating Excel template:', error);
        alert('Error generating template. Please try again.');
    }
};

// Flash messages
const hasFlashMessage = computed(() => {
    return page.props.flash?.success || page.props.flash?.error || page.props.flash?.warning;
});

const flashMessage = computed(() => {
    return page.props.flash?.success || page.props.flash?.error || page.props.flash?.warning;
});

const flashType = computed(() => {
    if (page.props.flash?.success) return 'success';
    if (page.props.flash?.error) return 'error';
    if (page.props.flash?.warning) return 'warning';
    return null;
});
</script>

<template>
    <Dialog :open="show" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Upload class="h-5 w-5" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ formatInfo }}
                </DialogDescription>
            </DialogHeader>

            <!-- Flash Messages -->
            <Alert
                v-if="hasFlashMessage"
                :class="[
                    'mb-4',
                    flashType === 'success' ? 'border-green-200 bg-green-50 text-green-800' : '',
                    flashType === 'error' ? 'border-red-200 bg-red-50 text-red-800' : '',
                    flashType === 'warning' ? 'border-yellow-200 bg-yellow-50 text-yellow-800' : ''
                ]"
            >
                <component 
                    :is="flashType === 'success' ? CheckCircle : flashType === 'error' ? XCircle : AlertCircle" 
                    class="mr-2 h-4 w-4"
                    :class="[
                        flashType === 'success' ? 'text-green-600' : '',
                        flashType === 'error' ? 'text-red-600' : '',
                        flashType === 'warning' ? 'text-yellow-600' : ''
                    ]"
                />
                <AlertDescription>
                    {{ flashMessage }}
                </AlertDescription>
            </Alert>

            <!-- Template Info -->
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <FileSpreadsheet class="h-4 w-4 text-green-600" />
                        Format file: .xlsx, .xls, atau .csv
                    </div>

                    <Button
                        type="button"
                        variant="outline"
                        @click="downloadExcelTemplate"
                        class="flex items-center gap-2"
                        :disabled="isProcessing"
                    >
                        <Download class="h-4 w-4" />
                        Download Template
                    </Button>
                </div>
                
                <!-- Important Notes -->
                <Alert class="border-blue-200 bg-blue-50 mb-3">
                    <AlertCircle class="mr-2 h-4 w-4 text-blue-600" />
                    <AlertDescription class="text-sm">
                        <strong>Perhatian:</strong> Pastikan nomor urut unik untuk setiap jabatan.
                    </AlertDescription>
                </Alert>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="file_excel" class="text-sm font-medium">
                        Pilih File
                        <span class="text-red-500">*</span>
                    </Label>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <input
                            id="file_excel"
                            ref="fileInput"
                            type="file"
                            @change="handleFileChange"
                            class="hidden"
                            accept=".xlsx, .xls, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, text/csv"
                            :disabled="isProcessing"
                        />

                        <div class="flex-1">
                            <Button
                                type="button"
                                variant="outline"
                                @click="fileInput?.click()"
                                class="w-full justify-start"
                                :disabled="isProcessing"
                            >
                                <Upload class="mr-2 h-4 w-4" />
                                {{ fileName ? fileName : 'Pilih file Excel/CSV' }}
                            </Button>
                        </div>
                        
                        <Button
                            v-if="fileName"
                            type="button"
                            variant="ghost"
                            @click="resetForm"
                            class="text-red-600 hover:text-red-800"
                            :disabled="isProcessing"
                        >
                            Hapus
                        </Button>
                    </div>

                    <div v-if="fileName" class="mt-2">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <CheckCircle class="h-4 w-4 text-green-600" />
                            <span>File siap diupload: <strong>{{ fileName }}</strong></span>
                        </div>
                    </div>

                    <InputError
                        class="mt-1"
                        :message="form.errors.file_excel"
                    />
                </div>

                <!-- Processing State -->
                <div
                    v-if="isProcessing"
                    class="flex items-center justify-center rounded-lg bg-blue-50 p-4"
                >
                    <Loader2 class="mr-2 h-5 w-5 animate-spin text-blue-600" />
                    <span class="text-blue-700">
                        Sedang memproses data calon...
                    </span>
                </div>

                <!-- Required Fields Info -->
                <Alert class="border-gray-200 bg-gray-50">
                    <AlertDescription class="text-sm">
                        <p class="font-semibold mb-1">Kolom yang diperlukan:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li><code>nama</code> - Nama lengkap</li>
                            <li><code>asal_pimpinan</code> - Asal organisasi</li>
                            <li><code>jenis_kelamin</code> - L atau P</li>
                            <li><code>nomor_urut</code> - Angka unik per jabatan</li>
                            <li><code>jabatan</code> - Ketua atau Formatur</li>
                        </ul>
                    </AlertDescription>
                </Alert>

                <div class="flex justify-end gap-2 pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        @click="close"
                        :disabled="isProcessing"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="isProcessing || !form.file_excel"
                        class="bg-[#a81b2c] hover:bg-[#8c1523]"
                    >
                        <Upload v-if="!isProcessing" class="mr-2 h-4 w-4" />
                        <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
                        {{ isProcessing ? 'Memproses...' : 'Import Data' }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>