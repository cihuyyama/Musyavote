<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
// import { Peserta, pesertaColumn } from './column';
import { onMounted } from 'vue';
// import DataTable from './data-table.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import { Plus } from 'lucide-vue-next';
import { calonColumn } from './column';
import DataTable from './data-table.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Calon',
        href: '/calon',
    },
];

const props = defineProps<{
    pesertas: Array<any>;
    jabatanOptions: Array<string>;
    // currentFilter: string;

}>()

// const selectedJabatan = ref(props.currentFilter || '');

// const applyFilter = () => {
//     const params = {};
//     if (selectedJabatan.value) {
//         params.jabatan = selectedJabatan.value;
//     }

//     // Menggunakan router.get untuk memperbarui URL dan mengirim request baru
//     router.get("calon.index", params, {
//         preserveState: true, // Menjaga state form
//         preserveScroll: true, // Menjaga posisi scroll
//     });
// };

onMounted(() => {
    console.log(props.pesertas);
});
</script>

<template>

    <Head title="Calon" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full">
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <Link href="/calon/create">
                            <Button
                                class="mb-4 cursor-pointer bg-[#a81b2c] text-white hover:bg-white hover:text-black hover:border"
                                variant="default">
                                <Plus /> Tambah Calon
                            </Button>
                            </Link>
                            <DataTable :columns="calonColumn" :data="props.pesertas" />
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>