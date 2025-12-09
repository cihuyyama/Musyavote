<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { Vote, Users, BarChart3, Download } from 'lucide-vue-next';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Hasil Pemilihan',
        href: '/hasil-pemilihan',
    },
];

const props = defineProps<{
    pemilihans: any[];
}>();
</script>

<template>
    <Head title="Hasil Pemilihan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-white py-8">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Daftar Pemilihan -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card 
                        v-for="pemilihan in pemilihans" 
                        :key="pemilihan.id"
                        class="shadow-lg hover:shadow-xl transition-shadow duration-200"
                    >
                        <CardContent class="pt-6">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <Vote class="w-6 h-6 text-blue-600" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ pemilihan.nama_pemilihan }}
                                    </h3>
                                    <div class="space-y-2 text-sm text-gray-600">
                                        <div class="flex items-center gap-2">
                                            <Users class="w-4 h-4" />
                                            <span>{{ pemilihan.calon_count }} Calon</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <BarChart3 class="w-4 h-4" />
                                            <span>{{ pemilihan.voting_records_count }} Suara</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <Link 
                                    :href="`/hasil-pemilihan/${pemilihan.id}`"
                                    class="flex-1"
                                >
                                    <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                        Lihat Hasil
                                    </button>
                                </Link>
                                <Link 
                                    :href="`/hasil-pemilihan/${pemilihan.id}/export/pdf`"
                                    class="shrink-0"
                                >
                                    <button class="bg-gray-100 text-gray-700 p-2 rounded-lg hover:bg-gray-200 transition-colors">
                                        <Download class="w-4 h-4" />
                                    </button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-if="pemilihans.length === 0" class="text-center">
                    <CardContent class="pt-6">
                        <Vote class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Pemilihan</h3>
                        <p class="text-gray-600">Tidak ada data pemilihan yang tersedia.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>