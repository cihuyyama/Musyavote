import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropDown.vue';

export interface AdminPresensi {
    id: string;
    nama: string;
    username: string;
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
        accessorKey: 'created_at',
        header: () => h('div', { class: '' }, 'Tanggal Dibuat'),
        cell: ({ row }) => {
            const date = new Date(row.original.created_at);
            return h('div', { class: '' }, date.toLocaleDateString('id-ID'));
        },
    },
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