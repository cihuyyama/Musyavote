<script setup lang="ts">
import { defineProps, defineEmits, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Upload, Download, AlertCircle, FileSpreadsheet, Loader2 } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent } from '@/components/ui/card';

// --- PROPS & EMITS ---
const props = defineProps({
  importUrl: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: 'Unggah File Excel'
  },
  show: {
    type: Boolean,
    default: false
  },
  formatInfo: {
    type: String,
    default: 'Pastikan file Excel memiliki header kolom: nama, asal_pimpinan, jenis_kelamin (L/P). Status otomatis "Aktif".'
  }
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
watch(() => form.processing, (processing) => {
  isProcessing.value = processing;
});

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
    }
  });
};

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    
    // Validasi file type
    const validTypes = [
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-excel'
    ];
    
    if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/)) {
      form.setError('file_excel', 'File harus berformat Excel (.xlsx atau .xls)');
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
    // Data template - HANYA 3 KOLOM (tanpa status)
    const templateData = [
      {
        nama: 'Ahmad Rizki',
        asal_pimpinan: 'DPD Jakarta Selatan', 
        jenis_kelamin: 'L'
      },
      {
        nama: 'Siti Nurhaliza',
        asal_pimpinan: 'DPC Bandung',
        jenis_kelamin: 'P'
      },
      {
        nama: 'Budi Santoso',
        asal_pimpinan: 'DPD Surabaya',
        jenis_kelamin: 'L'
      }
    ];

    // Import library xlsx
    const XLSX = await import('xlsx');

    // Buat workbook dan worksheet
    const workbook = XLSX.utils.book_new();
    
    // Siapkan data dengan header - HANYA 3 KOLOM
    const worksheetData = [
      ['nama', 'asal_pimpinan', 'jenis_kelamin'], // HEADER tanpa status
      ...templateData.map(item => [
        item.nama,
        item.asal_pimpinan,
        item.jenis_kelamin
      ])
    ];

    const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);

    // Set column widths
    worksheet['!cols'] = [
      { width: 25 }, // nama
      { width: 30 }, // asal_pimpinan  
      { width: 15 }, // jenis_kelamin
    ];

    // Tambahkan worksheet
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Data Peserta');

    // Buat sheet panduan - UPDATE PANDUAN
    const guideData = [
      ['PANDUAN IMPORT DATA PESERTA'],
      [''],
      ['FORMAT KOLOM:'],
      ['nama', 'Nama lengkap peserta (text, wajib)'],
      ['asal_pimpinan', 'Asal pimpinan/organisasi (text, wajib)'], 
      ['jenis_kelamin', 'L (Laki-laki) atau P (Perempuan)'],
      [''],
      ['CATATAN:'],
      ['Status otomatis "Aktif" untuk semua peserta'],
      [''],
      ['CONTOH DATA:'],
      ...worksheetData
    ];

    const guideSheet = XLSX.utils.aoa_to_sheet(guideData);
    XLSX.utils.book_append_sheet(workbook, guideSheet, 'Panduan');

    // Download file
    XLSX.writeFile(workbook, 'template-import-peserta.xlsx');

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
          <Upload class="w-5 h-5" />
          {{ title }}
        </DialogTitle>
        <DialogDescription>
          {{ formatInfo }}
        </DialogDescription>
      </DialogHeader>

      <!-- Success Message -->
      <Alert v-if="page.props.flash?.success" class="mb-4 bg-green-50 border-green-200">
        <AlertCircle class="w-4 h-4 text-green-600 mr-2" />
        <AlertDescription class="text-green-800">
          {{ page.props.flash.success }}
        </AlertDescription>
      </Alert>

      <!-- Error Message -->
      <Alert v-if="page.props.flash?.error" variant="destructive" class="mb-4">
        <AlertCircle class="w-4 h-4 mr-2" />
        <AlertDescription>
          {{ page.props.flash.error }}
        </AlertDescription>
      </Alert>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <FileSpreadsheet class="w-4 h-4 text-green-600" />
          Format file: .xlsx atau .xls
        </div>
        
        <Button 
          type="button" 
          variant="outline" 
          @click="downloadExcelTemplate"
          class="flex items-center gap-2"
          :disabled="isProcessing"
        >
          <Download class="w-4 h-4" />
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
              <Upload class="w-4 h-4 mr-2" />
              {{ fileName ? 'Ganti File' : 'Pilih File Excel' }}
            </Button>
          </div>

          <Card v-if="fileName" class="mt-2 border-green-200 bg-green-50">
            <CardContent class="p-3 flex items-center gap-2">
              <FileSpreadsheet class="w-4 h-4 text-green-600" />
              <span class="text-sm font-medium truncate">{{ fileName }}</span>
            </CardContent>
          </Card>

          <InputError class="mt-1" :message="form.errors.file_excel" />
        </div>

        <Alert class="bg-blue-50 border-blue-200">
          <AlertCircle class="w-4 h-4 text-blue-600 mr-2" />
          <AlertDescription class="text-blue-800 text-sm">
            <strong>Format yang benar:</strong> File Excel dengan 3 kolom: nama, asal_pimpinan, jenis_kelamin
          </AlertDescription>
        </Alert>

        <!-- Processing State -->
        <div v-if="isProcessing" class="flex items-center justify-center p-4 bg-blue-50 rounded-lg">
          <Loader2 class="w-5 h-5 mr-2 animate-spin text-blue-600" />
          <span class="text-blue-700">Sedang memproses file Excel...</span>
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
            class="bg-blue-600 hover:bg-blue-700"
          >
            <Upload v-if="!isProcessing" class="w-4 h-4 mr-2" />
            <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
            {{ isProcessing ? 'Memproses...' : 'Import Excel' }}
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>