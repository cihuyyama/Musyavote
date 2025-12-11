<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { Vote, Users, BarChart3, Download, ChevronRight } from 'lucide-vue-next';
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
        <div class="min-h-screen bg-gray-50 py-4 sm:py-6">
            <div class="max-w-6xl mx-auto px-3 sm:px-4">
                <!-- Daftar Pemilihan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Card v-for="pemilihan in pemilihans" :key="pemilihan.id"
                        class="shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-200">
                        <CardContent class="p-4">
                            <!-- Header Card -->
                            <div class="flex items-start gap-3 mb-4">
                                <div
                                    class="shrink-0 w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center border border-red-100">
                                    <Vote class="w-5 h-5 text-red-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-gray-900 mb-1.5 line-clamp-2">
                                        {{ pemilihan.nama_pemilihan }}
                                    </h3>

                                    <!-- Metadata -->
                                    <div class="flex flex-wrap gap-x-3 gap-y-2 text-xs text-gray-600">
                                        <div class="flex items-center gap-1.5">
                                            <Users class="w-3.5 h-3.5 shrink-0" />
                                            <span>{{ pemilihan.calon_count }} Calon</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <BarChart3 class="w-3.5 h-3.5 shrink-0" />
                                            <span>{{ pemilihan.voting_records_count }} Suara</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status & Date (optional - if available) -->
                            <div v-if="pemilihan.status || pemilihan.tanggal" class="mb-4">
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span v-if="pemilihan.status"
                                        class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">
                                        {{ pemilihan.status }}
                                    </span>
                                    <span v-if="pemilihan.tanggal">
                                        {{ new Date(pemilihan.tanggal).toLocaleDateString('id-ID') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <Link :href="`/hasil-pemilihan/${pemilihan.id}`" class="flex-1">
                                    <button
                                        class="w-full bg-[#A81B2C] text-white py-2 px-3 rounded-md hover:bg-[#8A1524] transition-colors text-sm font-medium flex items-center justify-center gap-1.5">
                                        <span>Lihat Hasil</span>
                                        <ChevronRight class="w-3.5 h-3.5" />
                                    </button>
                                </Link>
                                <!-- <Link 
                                    :href="`/hasil-pemilihan/${pemilihan.id}/export/pdf`"
                                    class="shrink-0"
                                    title="Download PDF"
                                >
                                    <button class="bg-gray-50 text-gray-700 p-2 rounded-md hover:bg-gray-100 transition-colors border border-gray-200">
                                        <Download class="w-4 h-4" />
                                    </button>
                                </Link> -->
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-if="pemilihans.length === 0" class="border border-gray-200 shadow-sm">
                    <CardContent class="p-6 text-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Vote class="w-6 h-6 text-gray-400" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 mb-2">Belum Ada Hasil Pemilihan</h3>
                        <p class="text-gray-600 text-sm max-w-sm mx-auto">
                            Tidak ada data hasil pemilihan yang tersedia. Pemilihan akan muncul di sini setelah selesai.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>