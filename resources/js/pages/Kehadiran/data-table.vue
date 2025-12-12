<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, ColumnFiltersState } from '@tanstack/vue-table';
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    useVueTable,
} from '@tanstack/vue-table';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import { CheckIcon, XIcon, ChevronDownIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';

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

// Filter state untuk setiap pleno
const pleno1Filter = ref('');
const pleno2Filter = ref('');
const pleno3Filter = ref('');
const pleno4Filter = ref('');

// Apply filter untuk pleno - GANTI ID KOLOM
const applyPlenoFilter = (pleno: number, value: string) => {
    const columnId = `pleno_${pleno}`; // Hanya 'pleno_1', bukan 'kehadiran.pleno_1'

    // Update local state
    if (pleno === 1) pleno1Filter.value = value;
    if (pleno === 2) pleno2Filter.value = value;
    if (pleno === 3) pleno3Filter.value = value;
    if (pleno === 4) pleno4Filter.value = value;

    // Apply filter ke table
    table.getColumn(columnId)?.setFilterValue(value);
};

// Clear semua filter - GANTI ID KOLOM
const clearAllFilters = () => {
    pleno1Filter.value = '';
    pleno2Filter.value = '';
    pleno3Filter.value = '';
    pleno4Filter.value = '';

    table.getColumn('pleno_1')?.setFilterValue('');
    table.getColumn('pleno_2')?.setFilterValue('');
    table.getColumn('pleno_3')?.setFilterValue('');
    table.getColumn('pleno_4')?.setFilterValue('');
    table.getColumn('nama')?.setFilterValue('');
};

// Hitung jumlah filter aktif
const activeFilterCount = computed(() => {
    let count = 0;
    if (pleno1Filter.value) count++;
    if (pleno2Filter.value) count++;
    if (pleno3Filter.value) count++;
    if (pleno4Filter.value) count++;
    const namaFilter = table.getColumn('nama')?.getFilterValue();
    if (namaFilter && String(namaFilter).trim()) count++;
    return count;
});

// Fungsi untuk mendapatkan label filter
const getFilterLabel = (filterValue: string) => {
    if (filterValue === 'hadir') return 'Hadir';
    if (filterValue === 'tidak_hadir') return 'Tidak Hadir';
    return '';
};

// Fungsi untuk mendapatkan ikon filter
const getFilterIcon = (filterValue: string) => {
    if (filterValue === 'hadir') return CheckIcon;
    if (filterValue === 'tidak_hadir') return XIcon;
    return null;
};

// Debug: Log kolom yang tersedia
const logColumns = () => {
    console.log('Available columns:', table.getAllColumns().map(col => col.id));
};
</script>

<template>
    <div>
        <!-- Filter and Search Bar Container -->
        <div class="flex items-center justify-between py-4">
            <!-- Left Group: Pleno Filters + Clear Button -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-600">Filter Pleno:</span>

                <!-- Pleno 1 Filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2 px-3 py-1 h-8"
                            :class="{ 'border-blue-500 bg-blue-50': pleno1Filter }"
                        >
                            <span>Pleno 1</span>
                            <ChevronDownIcon class="h-3 w-3" />
                            <Badge
                                v-if="pleno1Filter"
                                variant="secondary"
                                class="ml-1 h-5"
                            >
                                {{ getFilterLabel(pleno1Filter) }}
                            </Badge>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-48">
                        <DropdownMenuLabel>Status Kehadiran</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(1, 'hadir')">
                            <div class="flex items-center gap-2 w-full">
                                <CheckIcon class="w-4 h-4 text-green-500" />
                                <span>Hadir</span>
                            </div>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="applyPlenoFilter(1, 'tidak_hadir')">
                            <div class="flex items-center gap-2 w-full">
                                <XIcon class="w-4 h-4 text-red-500" />
                                <span>Tidak Hadir</span>
                            </div>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(1, '')">
                            <div class="flex items-center gap-2 w-full text-gray-500">
                                <span>Hapus Filter</span>
                            </div>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Pleno 2 Filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2 px-3 py-1 h-8"
                            :class="{ 'border-blue-500 bg-blue-50': pleno2Filter }"
                        >
                            <span>Pleno 2</span>
                            <ChevronDownIcon class="h-3 w-3" />
                            <Badge
                                v-if="pleno2Filter"
                                variant="secondary"
                                class="ml-1 h-5"
                            >
                                {{ getFilterLabel(pleno2Filter) }}
                            </Badge>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-48">
                        <DropdownMenuLabel>Status Kehadiran</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(2, 'hadir')">
                            <div class="flex items-center gap-2 w-full">
                                <CheckIcon class="w-4 h-4 text-green-500" />
                                <span>Hadir</span>
                            </div>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="applyPlenoFilter(2, 'tidak_hadir')">
                            <div class="flex items-center gap-2 w-full">
                                <XIcon class="w-4 h-4 text-red-500" />
                                <span>Tidak Hadir</span>
                            </div>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(2, '')">
                            <div class="flex items-center gap-2 w-full text-gray-500">
                                <span>Hapus Filter</span>
                            </div>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Pleno 3 Filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2 px-3 py-1 h-8"
                            :class="{ 'border-blue-500 bg-blue-50': pleno3Filter }"
                        >
                            <span>Pleno 3</span>
                            <ChevronDownIcon class="h-3 w-3" />
                            <Badge
                                v-if="pleno3Filter"
                                variant="secondary"
                                class="ml-1 h-5"
                            >
                                {{ getFilterLabel(pleno3Filter) }}
                            </Badge>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-48">
                        <DropdownMenuLabel>Status Kehadiran</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(3, 'hadir')">
                            <div class="flex items-center gap-2 w-full">
                                <CheckIcon class="w-4 h-4 text-green-500" />
                                <span>Hadir</span>
                            </div>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="applyPlenoFilter(3, 'tidak_hadir')">
                            <div class="flex items-center gap-2 w-full">
                                <XIcon class="w-4 h-4 text-red-500" />
                                <span>Tidak Hadir</span>
                            </div>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(3, '')">
                            <div class="flex items-center gap-2 w-full text-gray-500">
                                <span>Hapus Filter</span>
                            </div>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Pleno 4 Filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2 px-3 py-1 h-8"
                            :class="{ 'border-blue-500 bg-blue-50': pleno4Filter }"
                        >
                            <span>Pleno 4</span>
                            <ChevronDownIcon class="h-3 w-3" />
                            <Badge
                                v-if="pleno4Filter"
                                variant="secondary"
                                class="ml-1 h-5"
                            >
                                {{ getFilterLabel(pleno4Filter) }}
                            </Badge>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-48">
                        <DropdownMenuLabel>Status Kehadiran</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(4, 'hadir')">
                            <div class="flex items-center gap-2 w-full">
                                <CheckIcon class="w-4 h-4 text-green-500" />
                                <span>Hadir</span>
                            </div>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="applyPlenoFilter(4, 'tidak_hadir')">
                            <div class="flex items-center gap-2 w-full">
                                <XIcon class="w-4 h-4 text-red-500" />
                                <span>Tidak Hadir</span>
                            </div>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(4, '')">
                            <div class="flex items-center gap-2 w-full text-gray-500">
                                <span>Hapus Filter</span>
                            </div>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Clear All Filters Button -->
                <Button
                    v-if="activeFilterCount > 0"
                    variant="ghost"
                    size="sm"
                    @click="clearAllFilters"
                    class="h-8 text-red-600 hover:text-red-700 hover:bg-red-50 border border-red-200"
                >
                    Hapus Semua Filter ({{ activeFilterCount }})
                </Button>

                <!-- Debug Button (optional, bisa dihapus setelah fix) -->
                <Button
                    variant="ghost"
                    size="sm"
                    @click="logColumns"
                    class="h-8 text-gray-500 text-xs"
                    title="Debug columns"
                >
                    Debug
                </Button>
            </div>

            <!-- Right Group: Search Input -->
            <div class="flex items-center">
                <Input
                    class="w-64"
                    placeholder="Cari nama peserta..."
                    :model-value="
                        (table.getColumn('nama')?.getFilterValue() as string) || ''
                    "
                    @update:model-value="
                        table.getColumn('nama')?.setFilterValue($event)
                    "
                />
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-md border overflow-hidden">
            <Table>
                <TableHeader class="bg-gray-50">
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="font-semibold text-gray-700"
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
                            class="hover:bg-gray-50 transition-colors"
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
                                class="h-32 text-center"
                            >
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <div class="mb-2">📊</div>
                                    <div class="font-medium">Tidak ada data yang sesuai dengan filter.</div>
                                    <div class="text-sm mt-1" v-if="activeFilterCount > 0">
                                        Coba ubah atau hapus filter untuk melihat data.
                                    </div>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
    </div>
</template>