<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { Peserta, pesertaColumn } from './column';
import DataTable from './data-table.vue';


const props = defineProps<{
    pesertas: Peserta[];
}>();

onMounted(() => {
    console.log(props.pesertas);
});
</script>

<template>
    <Head title="Kehadiran" />
        <Card className="rounded-lg border-none mt-2 w-full">
            <CardContent className="p-6 w-full">
                <div
                    className="flex justify-center items-start min-h-[calc(100vh-56px-64px-20px-24px-56px-48px)] w-full"
                >
                    <div className="flex flex-col relative w-full">
                        <div className="w-full">
                            <DataTable
                                :columns="pesertaColumn"
                                :data="props.pesertas"
                            />
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
</template>
