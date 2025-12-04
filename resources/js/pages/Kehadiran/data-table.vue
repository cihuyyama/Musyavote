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
import { ChevronDownIcon } from 'lucide-vue-next';
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

// Filter state untuk setiap pleno
const pleno1Filter = ref('');
const pleno2Filter = ref('');
const pleno3Filter = ref('');
const pleno4Filter = ref('');

// Apply filter untuk pleno
const applyPlenoFilter = (pleno: number, value: string) => {
    const columnId = `pleno_${pleno}`;

    // Update local state
    if (pleno === 1) pleno1Filter.value = value;
    if (pleno === 2) pleno2Filter.value = value;
    if (pleno === 3) pleno3Filter.value = value;
    if (pleno === 4) pleno4Filter.value = value;

    // Apply filter ke table
    table.getColumn(columnId)?.setFilterValue(value);
};

// Clear semua filter
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
const activeFilterCount = () => {
    let count = 0;
    if (pleno1Filter.value) count++;
    if (pleno2Filter.value) count++;
    if (pleno3Filter.value) count++;
    if (pleno4Filter.value) count++;
    if (table.getColumn('nama')?.getFilterValue()) count++;
    return count;
};
</script>

<template>
    <div>
        <!-- Filter and Search Bar Container -->
        <!-- Menggunakan 'justify-between' untuk memposisikan Filter Group di kiri dan Search di kanan -->
        <div class="flex items-center justify-between py-4">
            
            <!-- Left Group: Pleno Filters + Clear Button -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium">Filter Pleno:</span>

                <!-- Pleno 1 Filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2"
                        >
                            Pleno 1
                            <ChevronDownIcon class="h-4 w-4" />
                            <Badge
                                v-if="pleno1Filter"
                                variant="secondary"
                                class="ml-1"
                            >
                                {{
                                    pleno1Filter === 'hadir'
                                        ? 'Hadir'
                                        : 'Tidak Hadir'
                                }}
                            </Badge>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuLabel>Status Kehadiran</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(1, 'hadir')">
                            Hadir
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            @click="applyPlenoFilter(1, 'tidak_hadir')"
                        >
                            Tidak Hadir
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(1, '')">
                            Clear Filter
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Pleno 2 Filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2"
                        >
                            Pleno 2
                            <ChevronDownIcon class="h-4 w-4" />
                            <Badge
                                v-if="pleno2Filter"
                                variant="secondary"
                                class="ml-1"
                            >
                                {{
                                    pleno2Filter === 'hadir'
                                        ? 'Hadir'
                                        : 'Tidak Hadir'
                                }}
                            </Badge>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuLabel>Status Kehadiran</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(2, 'hadir')">
                            Hadir
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            @click="applyPlenoFilter(2, 'tidak_hadir')"
                        >
                            Tidak Hadir
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(2, '')">
                            Clear Filter
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Pleno 3 Filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2"
                        >
                            Pleno 3
                            <ChevronDownIcon class="h-4 w-4" />
                            <Badge
                                v-if="pleno3Filter"
                                variant="secondary"
                                class="ml-1"
                            >
                                {{
                                    pleno3Filter === 'hadir'
                                        ? 'Hadir'
                                        : 'Tidak Hadir'
                                }}
                            </Badge>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuLabel>Status Kehadiran</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(3, 'hadir')">
                            Hadir
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            @click="applyPlenoFilter(3, 'tidak_hadir')"
                        >
                            Tidak Hadir
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(3, '')">
                            Clear Filter
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Pleno 4 Filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2"
                        >
                            Pleno 4
                            <ChevronDownIcon class="h-4 w-4" />
                            <Badge
                                v-if="pleno4Filter"
                                variant="secondary"
                                class="ml-1"
                            >
                                {{
                                    pleno4Filter === 'hadir'
                                        ? 'Hadir'
                                        : 'Tidak Hadir'
                                }}
                            </Badge>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuLabel>Status Kehadiran</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(4, 'hadir')">
                            Hadir
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            @click="applyPlenoFilter(4, 'tidak_hadir')"
                        >
                            Tidak Hadir
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="applyPlenoFilter(4, '')">
                            Clear Filter
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Clear All Filters Button - Ditempatkan bersama filter pleno di kiri -->
                <Button
                    v-if="activeFilterCount() > 0"
                    variant="ghost"
                    size="sm"
                    @click="clearAllFilters"
                    class="text-red-600 hover:text-red-700"
                >
                    Clear All ({{ activeFilterCount() }})
                </Button>
            </div>

            <!-- Right Group: Search Input - Ditempatkan di sisi paling kanan -->
            <div class="flex items-center">
                <Input
                    class="max-w-sm w-[400px]"
                    placeholder="Pencarian nama..."
                    :model-value="
                        table.getColumn('nama')?.getFilterValue() as string
                    "
                    @update:model-value="
                        table.getColumn('nama')?.setFilterValue($event)
                    "
                />
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
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
                            :data-state="
                                row.getIsSelected() ? 'selected' : undefined
                            "
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
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
                                class="h-24 text-center"
                            >
                                Tidak ada data yang sesuai dengan filter.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
    </div>
</template>