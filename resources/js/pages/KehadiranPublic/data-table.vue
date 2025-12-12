<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, ColumnFiltersState } from '@tanstack/vue-table';
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    useVueTable,
} from '@tanstack/vue-table';

import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { valueUpdater } from '@/lib/utils';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
}>();

const columnFilters = ref<ColumnFiltersState>([]);

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    onColumnFiltersChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnFilters),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
        get columnFilters() {
            return columnFilters.value;
        },
    },
});
</script>

<template>
    <div class="space-y-4">
        <!-- Mobile Search -->
        <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
                class="pl-10 w-full"
                placeholder="Cari peserta..."
                :model-value="
                    table.getColumn('nama')?.getFilterValue() as string
                "
                @update:model-value="
                    table.getColumn('nama')?.setFilterValue($event)
                "
            />
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block border rounded-lg overflow-hidden">
            <Table>
                <TableHeader class="bg-gray-50">
                    <TableRow>
                        <TableHead
                            v-for="header in table.getFlatHeaders()"
                            :key="header.id"
                            class="font-medium text-gray-700 py-3"
                        >
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            class="hover:bg-gray-50"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                class="py-3"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell
                                :colspan="columns.length"
                                class="h-32 text-center text-gray-500"
                            >
                                <div class="flex flex-col items-center justify-center">
                                    <Search class="h-8 w-8 text-gray-300 mb-2" />
                                    <p>Tidak ada peserta yang ditemukan</p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3">
            <div 
                v-for="row in table.getRowModel().rows"
                :key="row.id"
                class="bg-white border rounded-lg p-4 shadow-sm"
            >
                <!-- Header Card -->
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="font-medium text-gray-900">
                            {{ row.getValue('nama') }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ row.getValue('asal_pimpinan') }}
                        </div>
                    </div>
                    <div class="text-xs font-mono text-gray-400">
                        #{{ row.getValue('kode_unik') }}
                    </div>
                </div>

                <!-- Kehadiran Row -->
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <div class="text-center">
                        <div class="text-xs text-gray-500 mb-1">Pleno 1</div>
                        <div class="flex justify-center">
                            <div v-if="row.original.kehadiran.pleno_1 === 1" 
                                 class="h-6 w-6 rounded-full bg-green-400 flex items-center justify-center">
                                <Check class="h-4 w-4 text-green-600" />
                            </div>
                            <div v-else 
                                 class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center">
                                <Minus class="h-4 w-4 text-gray-400" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-xs text-gray-500 mb-1">Pleno 2</div>
                        <div class="flex justify-center">
                            <div v-if="row.original.kehadiran.pleno_2 === 1" 
                                 class="h-6 w-6 rounded-full bg-green-400 flex items-center justify-center">
                                <Check class="h-4 w-4 text-green-600" />
                            </div>
                            <div v-else 
                                 class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center">
                                <Minus class="h-4 w-4 text-gray-400" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-xs text-gray-500 mb-1">Pleno 3</div>
                        <div class="flex justify-center">
                            <div v-if="row.original.kehadiran.pleno_3 === 1" 
                                 class="h-6 w-6 rounded-full bg-green-400 flex items-center justify-center">
                                <Check class="h-4 w-4 text-green-600" />
                            </div>
                            <div v-else 
                                 class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center">
                                <Minus class="h-4 w-4 text-gray-400" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-xs text-gray-500 mb-1">Pleno 4</div>
                        <div class="flex justify-center">
                            <div v-if="row.original.kehadiran.pleno_4 === 1" 
                                 class="h-6 w-6 rounded-full bg-green-400 flex items-center justify-center">
                                <Check class="h-4 w-4 text-green-600" />
                            </div>
                            <div v-else 
                                 class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center">
                                <Minus class="h-4 w-4 text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-between items-center pt-3 border-t">
                    <div class="text-sm text-gray-500">
                        Total kehadiran
                    </div>
                    <div :class="`font-semibold ${
                        row.original.kehadiran.total_kehadiran === 4 ? 'text-green-600' :
                        row.original.kehadiran.total_kehadiran >= 2 ? 'text-blue-600' :
                        row.original.kehadiran.total_kehadiran === 1 ? 'text-yellow-600' :
                        'text-red-600'
                    }`">
                        {{ row.original.kehadiran.total_kehadiran }}/4
                    </div>
                </div>
            </div>

            <div v-if="table.getRowModel().rows.length === 0" 
                 class="bg-white border rounded-lg p-8 text-center">
                <Search class="h-8 w-8 text-gray-300 mx-auto mb-3" />
                <p class="text-gray-500">Tidak ada peserta yang ditemukan</p>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="text-sm text-gray-500 text-center">
            Menampilkan {{ table.getRowModel().rows.length }} dari {{ props.data.length }} peserta
        </div>
    </div>
</template>