import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';

export interface AdminPresensi {
    id: string;
    nama: string;
    username: string;
    pleno_akses: number[];
    password_plain: string;
    created_at: string;
    status: string;
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
        accessorKey: 'password_plain',
        header: () => h('div', { class: '' }, 'Password'),
    },
    {
        accessorKey: 'status',
        header: () => h('div', { class: '' }, 'Status'),
        cell: ({ row }) => {
            const admin = row.original;
            const isActive = admin.status === 'active';

            return h('div', { class: 'flex items-center space-x-2' }, [
                h('span', { 
                    class: `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${
                        isActive 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800'
                    }`
                }, isActive ? 'Aktif' : 'Nonaktif')
            ]);
        },
    },
    {
        accessorKey: 'pleno_akses',
        header: () => h('div', { class: '' }, 'Akses Pleno'),
        cell: ({ row }) => {
            const plenoAkses = row.original.pleno_akses;
            
            if (!plenoAkses || plenoAkses.length === 0) {
                return h('div', { class: 'text-gray-400' }, 'Tidak ada akses');
            }
            
            const sortedAkses = [...plenoAkses].sort((a, b) => a - b);
            const plenoText = sortedAkses.map(p => `Pleno ${p}`).join(', ');
            
            return h('div', { 
                class: 'text-gray-800'
            }, plenoText);
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
                    adminStatus: admin.status,
                }),
            );
        },
    },
];