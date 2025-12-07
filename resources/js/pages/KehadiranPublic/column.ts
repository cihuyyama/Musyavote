import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Check, Minus } from 'lucide-vue-next';

export interface Peserta {
    id: string;
    kode_unik: string;
    nama: string;
    asal_pimpinan: string;
    jenis_kelamin: string;
    status: string;
    kehadiran: {
        pleno_1: number;
        pleno_2: number;
        pleno_3: number;
        pleno_4: number;
        total_kehadiran: number;
    };
}

export const pesertaColumn: ColumnDef<Peserta>[] = [
    {
        accessorKey: 'kode_unik',
        header: () => h('div', { class: 'font-medium text-gray-700' }, 'ID'),
        cell: ({ row }) => {
            return h('div', { class: 'font-mono text-sm text-gray-600' }, 
                `#${row.getValue('kode_unik')}`
            );
        },
    },
    {
        accessorKey: 'nama',
        header: () => h('div', { class: 'font-medium text-gray-700' }, 'Nama Peserta'),
        cell: ({ row }) => {
            return h('div', { class: 'font-normal' }, row.getValue('nama'));
        },
    },
    {
        accessorKey: 'asal_pimpinan',
        header: () => h('div', { class: 'font-medium text-gray-700' }, 'Asal Pimpinan'),
        cell: ({ row }) => {
            return h('div', { class: 'text-gray-600' }, row.getValue('asal_pimpinan'));
        },
    },
    {
        accessorKey: 'pleno_1',
        header: () => h('div', { class: 'font-medium text-center text-gray-700' }, 'Pleno 1'),
        cell: ({ row }) => {
            const hadir = row.original.kehadiran.pleno_1 === 1;
            return h('div', { class: 'flex justify-center' }, [
                hadir 
                    ? h(Check, { class: 'h-5 w-5 text-green-600' })
                    : h(Minus, { class: 'h-5 w-5 text-gray-400' })
            ]);
        },
    },
    {
        accessorKey: 'pleno_2',
        header: () => h('div', { class: 'font-medium text-center text-gray-700' }, 'Pleno 2'),
        cell: ({ row }) => {
            const hadir = row.original.kehadiran.pleno_2 === 1;
            return h('div', { class: 'flex justify-center' }, [
                hadir 
                    ? h(Check, { class: 'h-5 w-5 text-green-600' })
                    : h(Minus, { class: 'h-5 w-5 text-gray-400' })
            ]);
        },
    },
    {
        accessorKey: 'pleno_3',
        header: () => h('div', { class: 'font-medium text-center text-gray-700' }, 'Pleno 3'),
        cell: ({ row }) => {
            const hadir = row.original.kehadiran.pleno_3 === 1;
            return h('div', { class: 'flex justify-center' }, [
                hadir 
                    ? h(Check, { class: 'h-5 w-5 text-green-600' })
                    : h(Minus, { class: 'h-5 w-5 text-gray-400' })
            ]);
        },
    },
    {
        accessorKey: 'pleno_4',
        header: () => h('div', { class: 'font-medium text-center text-gray-700' }, 'Pleno 4'),
        cell: ({ row }) => {
            const hadir = row.original.kehadiran.pleno_4 === 1;
            return h('div', { class: 'flex justify-center' }, [
                hadir 
                    ? h(Check, { class: 'h-5 w-5 text-green-600' })
                    : h(Minus, { class: 'h-5 w-5 text-gray-400' })
            ]);
        },
    },
    {
        accessorKey: 'total_kehadiran',
        header: () => h('div', { class: 'font-medium text-center text-gray-700' }, 'Total'),
        cell: ({ row }) => {
            const total = row.original.kehadiran.total_kehadiran;
            return h('div', { 
                class: `text-center font-medium ${
                    total === 4 ? 'text-green-600' :
                    total >= 2 ? 'text-blue-600' :
                    total === 1 ? 'text-yellow-600' :
                    'text-red-600'
                }`
            }, total);
        },
    },
];