import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Pemilihan } from '../Pemilihan/column';
import DropdownAction from './DataTableDropDown.vue';

export interface Bilik {
    id: string;
    nama: string;
    password_plain: string;
    status: string;
    pemilihan: Pemilihan[];
}

export const bilikColumn: ColumnDef<Bilik>[] = [
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
        accessorKey: 'pemilihan',
        header: () => h('div', { class: '' }, 'Pemilihan Terdaftar'),
        cell: ({ row }) => {
            const bilik = row.original;
            const pemilihan = bilik.pemilihan;
            return h(
                'div',
                { class: 'flex flex-col' },
                pemilihan.map((p) => h('span', {}, p.nama_pemilihan)),
            );
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const bilik = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    bilikId: bilik.id,
                }),
            );
        },
    },
];
