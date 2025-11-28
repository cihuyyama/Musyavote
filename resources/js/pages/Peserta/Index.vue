<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Peserta, pesertaColumn } from './column';
import { onMounted, ref } from 'vue';
import DataTable from './data-table.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import { Plus, Upload } from 'lucide-vue-next';
import ImportModal from './ImportModal.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Peserta',
        href: '/peserta',
    },
];

const props = defineProps<{
    pesertas: Peserta[]
}>()

const showImportModal = ref(false);

const handleImportSuccess = () => {
    showImportModal.value = false;
    // PENTING: Lakukan refresh Inertia untuk memuat data baru
    router.reload({ only: ['pesertas'] });
};

onMounted(() => {
    // console.log(props.pesertas);
});
</script>

<template>

    <Head title="Peserta" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full">
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <div class="w-full flex flex-row gap-3">
                                <Link href="/peserta/create">
                                <Button
                                    class="mb-4 cursor-pointer bg-[#a81b2c] text-white hover:bg-white hover:text-black hover:border"
                                    variant="default">
                                    <Plus /> Tambah Peserta
                                </Button>
                                </Link>
                                <div class="flex justify-end mb-4 space-x-2">
                                    <Button @click="showImportModal = true" class="bg-green-600 hover:bg-green-700">
                                        <Upload class="w-4 h-4 mr-2" /> Import Excel
                                    </Button>
                                </div>
                            </div>

                            <ImportModal :show="showImportModal" :import-url="`pesertas/import`"
                                @close="showImportModal = false" @success="handleImportSuccess"
                                title="Unggah Data Peserta Baru" />
                            <DataTable :columns="pesertaColumn" :data="props.pesertas" />
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>