<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { AdminPresensi, adminPresensiColumn } from './colomn';
import DataTable from './data-table.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Kehadiran',
        href: '/admin-presensi',
    },
];

const props = defineProps<{
    admins: Array<AdminPresensi>;
}>();
</script>

<template>
    <Head title="Admin Kehadiran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full"
                >
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <!-- Container dengan flex justify-between -->
                            <div class="flex w-full flex-row justify-between items-center mb-4">
                                <!-- Bagian kiri (bisa diisi dengan elemen lain jika diperlukan) -->
                                <div></div>
                                
                                <!-- Bagian kanan (tombol) -->
                                <Link href="/admin-presensi/create">
                                    <Button
                                        class="cursor-pointer bg-[#a81b2c] text-white hover:border hover:bg-white hover:text-black"
                                        variant="default"
                                    >
                                        <Plus /> Tambah Admin
                                    </Button>
                                </Link>
                            </div>

                            <DataTable
                                :columns="adminPresensiColumn"
                                :data="props.admins"
                            />
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>