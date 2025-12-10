import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';

export interface AdminPresensi {
    id: string;
    nama: string;
    username: string;
    pleno_akses: number[]; // Tambahkan ini
    created_at: string;
}

export const adminPresensiColumn: ColumnDef<AdminPresensi>[] = [
    {
        accessorKey: 'nama',
        header: () => h('div', { class: '' }, 'Nama Lengkap'),
    },
    {
        accessorKey: 'username',
        header: () => h('div', { class: '' }, 'Username'),
    },
    {
        accessorKey: 'pleno_akses',
        header: () => h('div', { class: '' }, 'Akses Pleno'),
        cell: ({ row }) => {
            const plenoAkses = row.original.pleno_akses;
            
            if (!plenoAkses || plenoAkses.length === 0) {
                return h('div', { class: 'text-gray-400' }, 'Tidak ada akses');
            }
            
            // Sort pleno akses
            const sortedAkses = [...plenoAkses].sort((a, b) => a - b);
            const plenoText = sortedAkses.map(p => `Pleno ${p}`).join(', ');
            
            return h('div', { 
                class: 'text-gray-800'
            }, plenoText);
        },
    },
    // {
    //     accessorKey: 'created_at',
    //     header: () => h('div', { class: '' }, 'Tanggal Dibuat'),
    //     cell: ({ row }) => {
    //         const date = new Date(row.original.created_at);
    //         return h('div', { class: '' }, date.toLocaleDateString('id-ID'));
    //     },
    // },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const admin = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    adminId: admin.id,
                }),
            );
        },
    },
];