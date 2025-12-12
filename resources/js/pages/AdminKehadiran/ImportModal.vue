<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

// --- PROPS & EMITS ---
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
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

    form.post('/admin-presensi/import', {
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
        ];

        if (
            !validTypes.includes(file.type) &&
            !file.name.match(/\.(xlsx|xls)$/)
        ) {
            form.setError(
                'file_excel',
                'File harus berformat Excel (.xlsx atau .xls)',
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

const downloadExcelTemplate = async () => {
    try {
        // Data template untuk admin kehadiran
        const templateData = [
            {
                nama: 'Admin 1',
                username: 'admin1',
                password: '123456',
                pleno_akses: '1',
                status: 'active'
            },
            {
                nama: 'Admin 2',
                username: 'admin2',
                password: '654321',
                pleno_akses: '3',
                status: 'active'
            },
            {
                nama: 'Admin 3',
                username: 'admin3',
                password: '789012',
                pleno_akses: '2',
                status: 'inactive'
            },
        ];

        // Import library xlsx
        const XLSX = await import('xlsx');

        // Buat workbook dan worksheet
        const workbook = XLSX.utils.book_new();

        // Siapkan data dengan header
        const worksheetData = [
            ['nama', 'username', 'password', 'pleno_akses', 'status'], // HEADER
            ...templateData.map((item) => [
                item.nama,
                item.username,
                item.password,
                item.pleno_akses,
                item.status,
            ]),
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);

        // Set column widths
        worksheet['!cols'] = [
            { width: 25 }, // nama
            { width: 20 }, // username
            { width: 20 }, // password
            { width: 25 }, // pleno_akses
            { width: 15 }, // status
        ];

        // Tambahkan worksheet
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Data Admin');

        // Buat sheet panduan
        const guideData = [
            ['PANDUAN IMPORT DATA ADMIN KEHADIRAN'],
            [''],
            ['FORMAT KOLOM:'],
            ['nama', 'Nama lengkap admin (text, wajib)'],
            ['username', 'Username untuk login (unik, text, wajib)'],
            ['password', 'Password minimal 6 karakter (text, wajib)'],
            ['pleno_akses', 'Nomor pleno yang bisa diakses, pisahkan dengan koma: 1,2,3,4 (contoh: "1,2" atau "1,3,4")'],
            ['status', 'active (aktif) atau inactive (nonaktif)'],
            [''],
            ['CATATAN:'],
            ['Username harus unik, tidak boleh sama dengan admin lain'],
            ['Pleno akses harus angka 1-4, pisahkan dengan koma tanpa spasi'],
            ['Password akan di-hash secara otomatis'],
            [''],
            ['CONTOH DATA:'],
            ...worksheetData,
        ];

        const guideSheet = XLSX.utils.aoa_to_sheet(guideData);
        XLSX.utils.book_append_sheet(workbook, guideSheet, 'Panduan');

        // Download file
        XLSX.writeFile(workbook, 'template-import-admin-kehadiran.xlsx');
    } catch (error) {
        console.error('Error generating Excel template:', error);
        alert('Error generating template. Please try again.');
    }
};
</script>

<template>
    <Dialog :open="show" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Upload class="h-5 w-5" />
                    Import Admin Kehadiran
                </DialogTitle>
                <DialogDescription>
                    Unggah file Excel berisi data admin kehadiran baru
                </DialogDescription>
            </DialogHeader>

            <!-- Success Message -->
            <Alert
                v-if="page.props.flash?.success"
                class="mb-4 border-green-200 bg-green-50"
            >
                <AlertCircle class="mr-2 h-4 w-4 text-green-600" />
                <AlertDescription class="text-green-800">
                    {{ page.props.flash.success }}
                </AlertDescription>
            </Alert>

            <!-- Error Message -->
            <Alert
                v-if="page.props.flash?.error"
                variant="destructive"
                class="mb-4"
            >
                <AlertCircle class="mr-2 h-4 w-4" />
                <AlertDescription>
                    {{ page.props.flash.error }}
                </AlertDescription>
            </Alert>

            <div
                class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <FileSpreadsheet class="h-4 w-4 text-green-600" />
                    Format file: .xlsx atau .xls
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

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="file_excel" class="text-sm font-medium">
                        Pilih File Excel
                    </Label>

                    <div class="flex items-center gap-4">
                        <input
                            id="file_excel"
                            ref="fileInput"
                            type="file"
                            @change="handleFileChange"
                            class="hidden"
                            accept=".xlsx, .xls, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                            :disabled="isProcessing"
                        />

                        <Button
                            type="button"
                            variant="outline"
                            @click="fileInput?.click()"
                            class="flex-1 justify-start"
                            :disabled="isProcessing"
                        >
                            <Upload class="mr-2 h-4 w-4" />
                            {{ fileName ? 'Ganti File' : 'Pilih File Excel' }}
                        </Button>
                    </div>

                    <Card
                        v-if="fileName"
                        class="mt-2 border-green-200 bg-green-50"
                    >
                        <CardContent class="flex items-center gap-2 p-3">
                            <FileSpreadsheet class="h-4 w-4 text-green-600" />
                            <span class="truncate text-sm font-medium">{{
                                fileName
                            }}</span>
                        </CardContent>
                    </Card>

                    <InputError
                        class="mt-1"
                        :message="form.errors.file_excel"
                    />
                </div>

                <Alert class="border-blue-200 bg-blue-50">
                    <AlertCircle class="mr-2 h-4 w-4 text-blue-600" />
                    <AlertDescription class="text-sm text-blue-800">
                        <strong>Format yang benar:</strong> File Excel dengan 5 kolom: nama, username, password, pleno_akses, status
                    </AlertDescription>
                </Alert>

                <!-- Processing State -->
                <div
                    v-if="isProcessing"
                    class="flex items-center justify-center rounded-lg bg-blue-50 p-4"
                >
                    <Loader2 class="mr-2 h-5 w-5 animate-spin text-blue-600" />
                    <span class="text-blue-700"
                        >Sedang memproses file Excel...</span
                    >
                </div>

                <div class="flex justify-end gap-2 pt-2">
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
                        class="bg-[#a81b2c] hover:bg-[#8c1523] text-white"
                    >
                        <Upload v-if="!isProcessing" class="mr-2 h-4 w-4" />
                        <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
                        {{ isProcessing ? 'Memproses...' : 'Import Excel' }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>