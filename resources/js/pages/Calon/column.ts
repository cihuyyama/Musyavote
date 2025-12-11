// File: resources/js/Pages/Calon/column.ts
import { ColumnDef, FilterFn } from '@tanstack/vue-table';
import { h } from 'vue';
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
import { MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

export interface Calon {
    id: string;
    nama: string;
    asal_pimpinan: string;
    jenis_kelamin: 'L' | 'P';
    foto: string | null;
    foto_url: string | null;
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
        header: () => h('div', { class: 'text-center font-medium' }, 'No. Urut'),
        cell: ({ row }) => {
            const calon = row.original;
            return h('div', { class: 'text-center font-medium' }, calon.nomor_urut.toString());
        },
    },
    {
        accessorKey: 'foto',
        header: () => h('div', { class: 'font-medium' }, 'Foto'),
        cell: ({ row }) => {
            const calon = row.original;

            const handleFotoUpdate = (event: Event) => {
                const target = event.target as HTMLInputElement;
                if (target.files && target.files[0]) {
                    const formData = new FormData();
                    formData.append('file', target.files[0]);
                    formData.append('_method', 'PATCH');

                    router.post(`/calon/${calon.id}`, formData, {
                        preserveScroll: true,
                        preserveState: true,
                    });
                }
            };

            const openFileDialog = () => {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = 'image/*';
                fileInput.style.display = 'none';
                fileInput.onchange = handleFotoUpdate;
                document.body.appendChild(fileInput);
                fileInput.click();
                document.body.removeChild(fileInput);
            };

            return h('div', { class: 'flex justify-center' }, [
                h(
                    'button',
                    {
                        onClick: openFileDialog,
                        class: 'relative focus:outline-none transition-transform hover:scale-105',
                        title: 'Klik untuk mengubah foto'
                    },
                    [
                        h('img', {
                            src: calon.foto_url || '/default-avatar.png',
                            alt: 'Foto Calon',
                            class: 'w-12 h-12 rounded-full object-cover border-2 border-gray-200 hover:border-blue-500 transition-colors cursor-pointer',
                            onError: (e) => {
                                // Fallback jika gambar error
                                (e.target as HTMLImageElement).src = '/default-avatar.png';
                            }
                        }),
                        // Indicator kecil di sudut kanan bawah
                        // h('div', {
                        //     class: 'absolute -bottom-1 -right-1 bg-blue-500 text-white rounded-full p-1 shadow-lg',
                        // }, [
                        //     h('svg', {
                        //         class: 'w-3 h-3',
                        //         fill: 'none',
                        //         stroke: 'currentColor',
                        //         viewBox: '0 0 24 24',
                        //     }, [
                        //         h('path', {
                        //             strokeLinecap: 'round',
                        //             strokeLinejoin: 'round',
                        //             strokeWidth: 2,
                        //             d: 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z'
                        //         }),
                        //         h('path', {
                        //             strokeLinecap: 'round',
                        //             strokeLinejoin: 'round',
                        //             strokeWidth: 2,
                        //             d: 'M15 13a3 3 0 11-6 0 3 3 0 016 0z'
                        //         })
                        //     ])
                        // ])
                    ]
                ),
            ]);
        },
    },
    {
        accessorKey: 'nama',
        header: () => h('div', { class: 'font-medium' }, 'Nama Lengkap'),
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
        header: () => h('div', { class: 'text-center font-medium' }, 'Jabatan'),
        cell: ({ row }) => {
            const calon = row.original;
            return h('div', { class: 'text-center' }, [
                h(Badge, {
                    variant: 'outline',
                    class: 'bg-gray-100 text-gray-700 border-gray-300'
                }, calon.jabatan)
            ]);
        },
        filterFn: jabatanFilterFn,
    },
    {
        accessorKey: 'jenis_kelamin',
        header: () => h('div', { class: 'text-center font-medium' }, 'Jenis Kelamin'),
        cell: ({ row }) => {
            const calon = row.original;
            const jenisKelaminText = calon.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
            return h('div', { class: 'text-center' }, jenisKelaminText);
        },
        filterFn: jenisKelaminFilterFn,
    },
    {
        id: 'actions',
        enableHiding: false,
        header: () => h('div', { class: 'text-center font-medium' }, 'Aksi'),
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
                            class: 'h-8 w-8 p-0 hover:bg-gray-100'
                        }, [
                            h('span', { class: 'sr-only' }, 'Buka menu'),
                            h(MoreHorizontal, { class: 'h-4 w-4 text-gray-600' })
                        ])
                    ]),
                    h(DropdownMenuContent, { align: 'end' }, [
                        h(DropdownMenuLabel, {}, 'Aksi'),
                        h(DropdownMenuSeparator, {}),
                        h(DropdownMenuItem, {
                            onClick: handleEdit,
                            class: 'cursor-pointer hover:bg-gray-50'
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