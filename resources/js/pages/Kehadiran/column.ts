import { Badge } from '@/components/ui/badge';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export interface Peserta {
    id: string;
    nama: string;
    foto: string | null;
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
        accessorKey: 'foto',
        header: () => h('div', { class: '' }, 'Foto'),
        cell: ({ row }) => {
            const peserta = row.original;
            return h('div', { class: 'flex' }, [
                h('img', {
                    src: peserta.foto
                        ? `/storage/${peserta.foto}`
                        : '/default-avatar.png',
                    alt: 'Foto Peserta',
                    class: 'w-10 h-10 rounded-full object-cover',
                }),
            ]);
        },
    },
    {
        accessorKey: 'nama',
        header: () => h('div', { class: '' }, 'Nama Lengkap'),
    },
    {
        accessorKey: 'asal_pimpinan',
        header: () => h('div', { class: '' }, 'Asal Pimpinan'),
    },
    {
        accessorKey: 'pleno_1',
        header: () => h('div', { class: 'text-center' }, 'Pleno 1'),
        cell: ({ row }) => {
            const peserta = row.original;
            return h(
                'div',
                { class: 'flex justify-center' },
                peserta.kehadiran.pleno_1 === 1
                    ? h(
                          Badge,
                          {
                              variant: 'default',
                              class: 'bg-green-500 text-white',
                          },
                          'Hadir',
                      )
                    : h(Badge, { variant: 'secondary' }, 'Tidak Hadir'),
            );
        },
        filterFn: (row, columnId, filterValue) => {
            if (!filterValue) return true;
            const hadir = row.original.kehadiran.pleno_1 === 1;
            return filterValue === 'hadir' ? hadir : !hadir;
        },
    },
    {
        accessorKey: 'pleno_2',
        header: () => h('div', { class: 'text-center' }, 'Pleno 2'),
        cell: ({ row }) => {
            const peserta = row.original;
            return h(
                'div',
                { class: 'flex justify-center' },
                peserta.kehadiran.pleno_2 === 1
                    ? h(
                          Badge,
                          {
                              variant: 'default',
                              class: 'bg-green-500 text-white',
                          },
                          'Hadir',
                      )
                    : h(Badge, { variant: 'secondary' }, 'Tidak Hadir'),
            );
        },
        filterFn: (row, columnId, filterValue) => {
            if (!filterValue) return true;
            const hadir = row.original.kehadiran.pleno_2 === 1;
            return filterValue === 'hadir' ? hadir : !hadir;
        },
    },
    {
        accessorKey: 'pleno_3',
        header: () => h('div', { class: 'text-center' }, 'Pleno 3'),
        cell: ({ row }) => {
            const peserta = row.original;
            return h(
                'div',
                { class: 'flex justify-center' },
                peserta.kehadiran.pleno_3 === 1
                    ? h(
                          Badge,
                          {
                              variant: 'default',
                              class: 'bg-green-500 text-white',
                          },
                          'Hadir',
                      )
                    : h(Badge, { variant: 'secondary' }, 'Tidak Hadir'),
            );
        },
        filterFn: (row, columnId, filterValue) => {
            if (!filterValue) return true;
            const hadir = row.original.kehadiran.pleno_3 === 1;
            return filterValue === 'hadir' ? hadir : !hadir;
        },
    },
    {
        accessorKey: 'pleno_4',
        header: () => h('div', { class: 'text-center' }, 'Pleno 4'),
        cell: ({ row }) => {
            const peserta = row.original;
            return h(
                'div',
                { class: 'flex justify-center' },
                peserta.kehadiran.pleno_4 === 1
                    ? h(
                          Badge,
                          {
                              variant: 'default',
                              class: 'bg-green-500 text-white',
                          },
                          'Hadir',
                      )
                    : h(Badge, { variant: 'secondary' }, 'Tidak Hadir'),
            );
        },
        filterFn: (row, columnId, filterValue) => {
            if (!filterValue) return true;
            const hadir = row.original.kehadiran.pleno_4 === 1;
            return filterValue === 'hadir' ? hadir : !hadir;
        },
    },
    {
        accessorKey: 'total_kehadiran',
        header: () => h('div', { class: 'text-center' }, 'Total Kehadiran'),
        cell: ({ row }) => {
            const peserta = row.original;
            return h(
                'div',
                { class: 'text-center font-semibold' },
                peserta.kehadiran.total_kehadiran,
            );
        },
    },
];
