import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { CheckIcon, XIcon } from 'lucide-vue-next';

export interface Kehadiran {
    id: number;
    peserta_id: string;
    pleno_1: number;
    pleno_2: number;
    pleno_3: number;
    pleno_4: number;
    total_kehadiran: number;
    created_at: string;
    updated_at: string;
}

export interface Peserta {
    id: string;
    kode_unik: string;
    foto: string | null;
    nama: string;
    asal_pimpinan: string;
    jenis_kelamin: string;
    status: string;
    created_at: string;
    updated_at: string;
    foto_url: string;
    kehadiran: Kehadiran;
}

export const pesertaColumn: ColumnDef<Peserta>[] = [
    {
        accessorKey: 'kode_unik',
        header: () => h('div', { class: 'text-center' }, 'Kode'),
        cell: ({ row }) => {
            return h('div', { class: 'text-center' }, row.original.kode_unik);
        },
    },
    {
        accessorKey: 'foto_url',
        header: () => h('div', { class: 'text-center' }, 'Foto'),
        cell: ({ row }) => {
            const peserta = row.original;
            return h('div', { class: 'flex justify-center' }, [
                h('img', {
                    src: peserta.foto_url,
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
            const kehadiran = row.original.kehadiran;
            return h(
                'div',
                { class: 'flex justify-center' },
                kehadiran.pleno_1 === 1
                    ? h(CheckIcon, { class: 'w-5 h-5 text-green-500' })
                    : h(XIcon, { class: 'w-5 h-5 text-red-500' }),
            );
        },
        filterFn: (row, columnId, filterValue) => {
            if (!filterValue) return true;
            const kehadiran = row.original.kehadiran.pleno_1;
            
            if (filterValue === 'hadir') {
                return kehadiran === 1;
            } else if (filterValue === 'tidak_hadir') {
                return kehadiran === 0;
            }
            return true;
        },
        accessorFn: (row) => row.kehadiran.pleno_1, // Menggunakan accessorFn untuk nested property
    },
    {
        accessorKey: 'pleno_2',
        header: () => h('div', { class: 'text-center' }, 'Pleno 2'),
        cell: ({ row }) => {
            const kehadiran = row.original.kehadiran;
            return h(
                'div',
                { class: 'flex justify-center' },
                kehadiran.pleno_2 === 1
                    ? h(CheckIcon, { class: 'w-5 h-5 text-green-500' })
                    : h(XIcon, { class: 'w-5 h-5 text-red-500' }),
            );
        },
        filterFn: (row, columnId, filterValue) => {
            if (!filterValue) return true;
            const kehadiran = row.original.kehadiran.pleno_2;
            
            if (filterValue === 'hadir') {
                return kehadiran === 1;
            } else if (filterValue === 'tidak_hadir') {
                return kehadiran === 0;
            }
            return true;
        },
        accessorFn: (row) => row.kehadiran.pleno_2,
    },
    {
        accessorKey: 'pleno_3',
        header: () => h('div', { class: 'text-center' }, 'Pleno 3'),
        cell: ({ row }) => {
            const kehadiran = row.original.kehadiran;
            return h(
                'div',
                { class: 'flex justify-center' },
                kehadiran.pleno_3 === 1
                    ? h(CheckIcon, { class: 'w-5 h-5 text-green-500' })
                    : h(XIcon, { class: 'w-5 h-5 text-red-500' }),
            );
        },
        filterFn: (row, columnId, filterValue) => {
            if (!filterValue) return true;
            const kehadiran = row.original.kehadiran.pleno_3;
            
            if (filterValue === 'hadir') {
                return kehadiran === 1;
            } else if (filterValue === 'tidak_hadir') {
                return kehadiran === 0;
            }
            return true;
        },
        accessorFn: (row) => row.kehadiran.pleno_3,
    },
    {
        accessorKey: 'pleno_4',
        header: () => h('div', { class: 'text-center' }, 'Pleno 4'),
        cell: ({ row }) => {
            const kehadiran = row.original.kehadiran;
            return h(
                'div',
                { class: 'flex justify-center' },
                kehadiran.pleno_4 === 1
                    ? h(CheckIcon, { class: 'w-5 h-5 text-green-500' })
                    : h(XIcon, { class: 'w-5 h-5 text-red-500' }),
            );
        },
        filterFn: (row, columnId, filterValue) => {
            if (!filterValue) return true;
            const kehadiran = row.original.kehadiran.pleno_4;
            
            if (filterValue === 'hadir') {
                return kehadiran === 1;
            } else if (filterValue === 'tidak_hadir') {
                return kehadiran === 0;
            }
            return true;
        },
        accessorFn: (row) => row.kehadiran.pleno_4,
    },
    {
        accessorKey: 'total_kehadiran',
        header: () => h('div', { class: 'text-center' }, 'Total'),
        cell: ({ row }) => {
            const kehadiran = row.original.kehadiran;
            return h(
                'div',
                { 
                    class: 'text-center font-semibold',
                    style: {
                        color: kehadiran.total_kehadiran === 4 ? '#16a34a' : 
                               kehadiran.total_kehadiran >= 2 ? '#ca8a04' : 
                               kehadiran.total_kehadiran === 0 ? '#dc2626' : '#2563eb'
                    }
                },
                kehadiran.total_kehadiran,
            );
        },
        accessorFn: (row) => row.kehadiran.total_kehadiran,
    },
];