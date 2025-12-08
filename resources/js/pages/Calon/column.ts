// File: resources/js/Pages/Calon/column.ts
import { ColumnDef, FilterFn } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropDown.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuLabel, 
    DropdownMenuSeparator, 
    DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';
import { MoreHorizontal, Pencil, Trash2} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

export interface Calon {
    id: string;
    nama: string;
    asal_pimpinan: string;
    jenis_kelamin: 'L' | 'P';
    foto: string | null;
    nomor_urut: number;
    jabatan: 'Ketua' | 'Formatur';
    created_at: string;
    updated_at: string;
}

// Filter untuk jabatan
const jabatanFilterFn: FilterFn<any> = (row, columnId, filterValue) => {
    if (filterValue === 'all' || !filterValue) {
        return true;
    }
    return row.original.jabatan === filterValue;
};

// Filter untuk jenis kelamin
const jenisKelaminFilterFn: FilterFn<any> = (row, columnId, filterValue) => {
    if (filterValue === 'all' || !filterValue) {
        return true;
    }
    return row.original.jenis_kelamin === filterValue;
};

export const calonColumn: ColumnDef<Calon>[] = [
    {
        accessorKey: 'nomor_urut',
        header: () => h('div', { class: 'text-center' }, 'No. Urut'),
        cell: ({ row }) => {
            const calon = row.original;
            return h('div', { 
                class: 'flex items-center justify-center'
            }, [
                h('div', { 
                    class: 'w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center'
                }, [
                    h('span', { class: 'text-blue-700 font-bold' }, calon.nomor_urut.toString())
                ])
            ]);
        },
    },
    {
        accessorKey: 'foto',
        header: () => h('div', { class: '' }, 'Foto'),
        cell: ({ row }) => {
            const calon = row.original;
            return h('div', { class: 'flex items-center' }, [
                h('img', {
                    src: calon.foto
                        ? `/storage/${calon.foto}`
                        : '/default-avatar.png',
                    alt: 'Foto Calon',
                    class: 'w-10 h-10 rounded-full object-cover',
                }),
            ]);
        },
    },
    {
        accessorKey: 'nama',
        header: () => h('div', { class: '' }, 'Nama Lengkap'),
        cell: ({ row }) => {
            const calon = row.original;
            return h('div', { class: '' }, [
                h('div', { class: 'font-medium text-gray-900' }, calon.nama),
                h('div', { class: 'text-sm text-gray-500' }, calon.asal_pimpinan)
            ]);
        },
    },
    {
        accessorKey: 'jabatan',
        header: () => h('div', { class: 'text-center' }, 'Jabatan'),
        cell: ({ row }) => {
            const calon = row.original;
            const jabatanColor = calon.jabatan === 'Ketua' 
                ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100' 
                : 'bg-green-100 text-green-800 hover:bg-green-100';
            
            return h('div', { class: 'flex justify-center' }, [
                h(Badge, { 
                    class: `${jabatanColor}`
                }, calon.jabatan)
            ]);
        },
        filterFn: jabatanFilterFn,
    },
    {
        accessorKey: 'jenis_kelamin',
        header: () => h('div', { class: 'text-center' }, 'Jenis Kelamin'),
        cell: ({ row }) => {
            const calon = row.original;
            const jenisKelaminText = calon.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
            const jenisKelaminColor = calon.jenis_kelamin === 'L' 
                ? 'bg-blue-100 text-blue-800 hover:bg-blue-100' 
                : 'bg-pink-100 text-pink-800 hover:bg-pink-100';
            
            return h('div', { class: 'flex justify-center' }, [
                h(Badge, { 
                    class: `${jenisKelaminColor}`
                }, jenisKelaminText)
            ]);
        },
        filterFn: jenisKelaminFilterFn,
    },
    {
        accessorKey: 'created_at',
        header: () => h('div', { class: 'text-center' }, 'Tanggal Dibuat'),
        cell: ({ row }) => {
            const calon = row.original;
            const date = new Date(calon.created_at);
            const formattedDate = date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            
            return h('div', { 
                class: 'text-sm text-gray-500 text-center'
            }, formattedDate);
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        header: () => h('div', { class: 'text-center' }, 'Aksi'),
        cell: ({ row }) => {
            const calon = row.original;
            
            const handleEdit = () => {
                router.get(`/calon/${calon.id}/edit`);
            };
            
            const handleDelete = () => {
                if (confirm(`Apakah Anda yakin ingin menghapus calon ${calon.nama}?`)) {
                    router.delete(`/calon/${calon.id}`, {
                        onSuccess: () => {
                            router.reload({ only: ['calons'] });
                        }
                    });
                }
            };
            
            return h('div', { class: 'relative flex justify-center' }, [
                h(DropdownMenu, {}, [
                    h(DropdownMenuTrigger, { asChild: true }, [
                        h(Button, { 
                            variant: 'ghost',
                            class: 'h-8 w-8 p-0'
                        }, [
                            h('span', { class: 'sr-only' }, 'Open menu'),
                            h(MoreHorizontal, { class: 'h-4 w-4' })
                        ])
                    ]),
                    h(DropdownMenuContent, { align: 'end' }, [
                        h(DropdownMenuLabel, {}, 'Aksi'),
                        h(DropdownMenuSeparator, {}),
                        h(DropdownMenuItem, { 
                            onClick: handleEdit,
                            class: 'cursor-pointer'
                        }, [
                            h(Pencil, { class: 'mr-2 h-4 w-4' }),
                            'Edit'
                        ]),
                        h(DropdownMenuSeparator, {}),
                        h(DropdownMenuItem, { 
                            onClick: handleDelete,
                            class: 'cursor-pointer text-red-600 hover:text-red-700 hover:bg-red-50'
                        }, [
                            h(Trash2, { class: 'mr-2 h-4 w-4' }),
                            'Hapus'
                        ])
                    ])
                ])
            ]);
        },
    },
];

// Versi alternatif jika ingin menggunakan DropdownAction komponen
export const calonColumnWithDropdownAction: ColumnDef<Calon>[] = [
    {
        accessorKey: 'nomor_urut',
        header: () => h('div', { class: 'text-center' }, 'No. Urut'),
        cell: ({ row }) => {
            const calon = row.original;
            return h('div', { 
                class: 'flex items-center justify-center'
            }, [
                h('div', { 
                    class: 'w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center'
                }, [
                    h('span', { class: 'text-blue-700 font-bold' }, calon.nomor_urut.toString())
                ])
            ]);
        },
    },
    {
        accessorKey: 'foto',
        header: () => h('div', { class: '' }, 'Foto'),
        cell: ({ row }) => {
            const calon = row.original;
            return h('div', { class: 'flex items-center' }, [
                h('img', {
                    src: calon.foto
                        ? `/storage/${calon.foto}`
                        : '/default-avatar.png',
                    alt: 'Foto Calon',
                    class: 'w-10 h-10 rounded-full object-cover',
                }),
            ]);
        },
    },
    {
        accessorKey: 'nama',
        header: () => h('div', { class: '' }, 'Nama Lengkap'),
        cell: ({ row }) => {
            const calon = row.original;
            return h('div', { class: '' }, [
                h('div', { class: 'font-medium text-gray-900' }, calon.nama),
                h('div', { class: 'text-sm text-gray-500' }, calon.asal_pimpinan)
            ]);
        },
    },
    {
        accessorKey: 'jabatan',
        header: () => h('div', { class: 'text-center' }, 'Jabatan'),
        cell: ({ row }) => {
            const calon = row.original;
            const jabatanColor = calon.jabatan === 'Ketua' 
                ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100' 
                : 'bg-green-100 text-green-800 hover:bg-green-100';
            
            return h('div', { class: 'flex justify-center' }, [
                h(Badge, { 
                    class: `${jabatanColor}`
                }, calon.jabatan)
            ]);
        },
        filterFn: jabatanFilterFn,
    },
    {
        accessorKey: 'jenis_kelamin',
        header: () => h('div', { class: 'text-center' }, 'Jenis Kelamin'),
        cell: ({ row }) => {
            const calon = row.original;
            const jenisKelaminText = calon.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
            const jenisKelaminColor = calon.jenis_kelamin === 'L' 
                ? 'bg-blue-100 text-blue-800 hover:bg-blue-100' 
                : 'bg-pink-100 text-pink-800 hover:bg-pink-100';
            
            return h('div', { class: 'flex justify-center' }, [
                h(Badge, { 
                    class: `${jenisKelaminColor}`
                }, jenisKelaminText)
            ]);
        },
        filterFn: jenisKelaminFilterFn,
    },
    {
        id: 'actions',
        enableHiding: false,
        header: () => h('div', { class: 'text-center' }, 'Aksi'),
        cell: ({ row }) => {
            const calon = row.original;
            
            // Gunakan DropdownAction komponen jika sudah ada
            return h('div', { class: 'relative' }, [
                h(DropdownAction, {
                    calonId: calon.id,
                    calonName: calon.nama,
                    onEdit: () => router.get(`/calon/${calon.id}/edit`),
                    onDelete: () => {
                        if (confirm(`Apakah Anda yakin ingin menghapus calon ${calon.nama}?`)) {
                            router.delete(`/calon/${calon.id}`, {
                                onSuccess: () => router.reload({ only: ['calons'] })
                            });
                        }
                    }
                })
            ]);
        },
    },
];

// Filter options untuk dropdown filter
export const jabatanFilterOptions = [
    { value: 'all', label: 'Semua Jabatan' },
    { value: 'Ketua', label: 'Ketua' },
    { value: 'Formatur', label: 'Formatur' },
];

export const jenisKelaminFilterOptions = [
    { value: 'all', label: 'Semua Jenis Kelamin' },
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];