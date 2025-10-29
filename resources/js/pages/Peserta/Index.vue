<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Peserta, pesertaColumn } from './column';
import { onMounted } from 'vue';
import DataTable from './data-table.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import { Plus } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Peserta',
        href: '/peserta',
    },
];

const props = defineProps<{
    pesertas: Peserta[]
}>()

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
                            <Link href="/peserta/create">
                                <Button class="mb-4 cursor-pointer bg-[#e7000b] text-white hover:bg-white hover:text-black hover:border" variant="default">
                                    <Plus /> Tambah Peserta
                                </Button>
                            </Link>
                            <DataTable :columns="pesertaColumn" :data="props.pesertas" />
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>