
<script setup lang="ts">
import { defineProps, defineEmits, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Upload, Download, AlertCircle, FileSpreadsheet } from 'lucide-vue-next';
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
    default: 'Unggah File Import'
  },
  show: {
    type: Boolean,
    default: false
  },
  formatInfo: {
    type: String,
    default: 'Pastikan file Excel memiliki header kolom: nama, asal_pimpinan, jenis_kelamin (L/P), status (Aktif/Non Aktif).'
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

const submit = () => {
  if (!form.file_excel) return;

  form.post(props.importUrl, {
    forceFormData: true,
    onError: () => {
      resetForm();
    },
    onSuccess: () => {
      resetForm();
      emit('success');
    },
  });
};

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  form.file_excel = target.files ? target.files[0] : null;
  fileName.value = form.file_excel ? form.file_excel.name : '';
};

const resetForm = () => {
  form.reset('file_excel');
  fileName.value = '';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const close = () => {
  resetForm();
  form.clearErrors();
  emit('close');
};

const handleOpenChange = (open: boolean) => {
  if (!open) {
    close();
  }
};

const downloadExcelTemplate = async () => {
  try {
    // Data template
    const templateData = [
      {
        nama: 'Ahmad Rizki',
        asal_pimpinan: 'DPD Jakarta Selatan', 
        jenis_kelamin: 'L',
        status: 'Aktif'
      },
      {
        nama: 'Siti Nurhaliza',
        asal_pimpinan: 'DPC Bandung',
        jenis_kelamin: 'P', 
        status: 'Aktif'
      },
      {
        nama: 'Budi Santoso',
        asal_pimpinan: 'DPD Surabaya',
        jenis_kelamin: 'L',
        status: 'Non Aktif'
      }
    ];

    // Import library xlsx
    const XLSX = await import('xlsx');

    // Buat workbook dan worksheet
    const workbook = XLSX.utils.book_new();
    
    // Siapkan data dengan header
    const worksheetData = [
      ['nama', 'asal_pimpinan', 'jenis_kelamin', 'status'],
      ...templateData.map(item => [
        item.nama,
        item.asal_pimpinan,
        item.jenis_kelamin,
        item.status
      ])
    ];

    const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);

    // Set column widths
    worksheet['!cols'] = [
      { width: 25 }, // nama
      { width: 30 }, // asal_pimpinan  
      { width: 15 }, // jenis_kelamin
      { width: 15 }  // status
    ];

    // Tambahkan worksheet
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Data Peserta');

    // Buat sheet panduan
    const guideData = [
      ['PANDUAN IMPORT DATA PESERTA'],
      [''],
      ['FORMAT KOLOM:'],
      ['nama', 'Nama lengkap peserta (text, wajib)'],
      ['asal_pimpinan', 'Asal pimpinan/organisasi (text, wajib)'], 
      ['jenis_kelamin', 'L (Laki-laki) atau P (Perempuan)'],
      ['status', 'Aktif atau Non Aktif'],
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
    // Fallback ke metode sederhana
    downloadFallbackTemplate();
  }
};

const downloadFallbackTemplate = () => {
  // Fallback method jika xlsx gagal
  const templateData = [
    ['nama', 'asal_pimpinan', 'jenis_kelamin', 'status'],
    ['Ahmad Rizki', 'DPD Jakarta Selatan', 'L', 'Aktif'],
    ['Siti Nurhaliza', 'DPC Bandung', 'P', 'Aktif'],
    ['Budi Santoso', 'DPD Surabaya', 'L', 'Non Aktif']
  ];

  const csvContent = templateData.map(row => 
    row.map(field => `"${field}"`).join(',')
  ).join('\n');
  
  const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  link.setAttribute('href', url);
  link.setAttribute('download', 'template-import-peserta.csv');
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
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
              accept=".xlsx, .xls"
            />
            
            <Button
              type="button"
              variant="outline"
              @click="fileInput?.click()"
              class="flex-1 justify-start"
            >
              <Upload class="w-4 h-4 mr-2" />
              Pilih File Excel
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
            <strong>Tips:</strong> Download template terlebih dahulu untuk memastikan format data sudah benar.
          </AlertDescription>
        </Alert>

        <div class="flex justify-end gap-2 pt-2">
          <Button
            type="button"
            variant="outline"
            @click="close"
            :disabled="form.processing"
          >
            Batal
          </Button>
          <Button
            type="submit"
            :disabled="form.processing || !form.file_excel"
            class="bg-blue-600 hover:bg-blue-700"
          >
            <Upload class="w-4 h-4 mr-2" />
            {{ form.processing ? 'Mengimpor...' : 'Mulai Import' }}
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>