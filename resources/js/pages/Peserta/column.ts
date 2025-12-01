import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { router } from '@inertiajs/vue3';
import DropdownAction from './DataTableDropDown.vue';

export interface Peserta {
    id: string;
    nama: string;
    foto: string | null;
    asal_pimpinan: string;
    jenis_kelamin: string;
    status: string;
    kode_unik: string;
}

export const pesertaColumn: ColumnDef<Peserta>[] = [
    {
        accessorKey: 'foto',
        header: () => h('div', { class: '' }, 'Foto'),
        cell: ({ row }) => {
            const peserta = row.original;

            const handleFotoUpdate = (event: Event) => {
                const target = event.target as HTMLInputElement;
                if (target.files && target.files[0]) {
                    const formData = new FormData();
                    formData.append('file', target.files[0]);
                    formData.append('_method', 'PATCH');

                    router.post(`/peserta/${peserta.id}`, formData, {
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
                            src: peserta.foto
                                ? `/storage/${peserta.foto}`
                                : '/default-avatar.png',
                            alt: 'Foto Peserta',
                            class: 'w-12 h-12 rounded-full object-cover border-2 border-gray-200 hover:border-blue-500 transition-colors cursor-pointer',
                        }),
                        // Indicator kecil di sudut kanan bawah
                        h('div', {
                            class: 'absolute -bottom-1 -right-1 bg-blue-500 text-white rounded-full p-1 shadow-lg opacity-0 hidden' ,
                        }, [
                            h('svg', {
                                class: 'w-3 h-3',
                                fill: 'none',
                                stroke: 'currentColor',
                                viewBox: '0 0 24 24',
                            }, [
                                h('path', {
                                    strokeLinecap: 'round',
                                    strokeLinejoin: 'round',
                                    strokeWidth: 2,
                                    d: 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z'
                                }),
                                h('path', {
                                    strokeLinecap: 'round',
                                    strokeLinejoin: 'round',
                                    strokeWidth: 2,
                                    d: 'M15 13a3 3 0 11-6 0 3 3 0 016 0z'
                                })
                            ])
                        ])
                    ]
                ),
            ]);
        },
    },
    {
        accessorKey: 'nama',
        header: () => h('div', { class: '' }, 'Nama Lengkap'),
    },
    {
        accessorKey: 'kode_unik',
        header: () => h('div', { class: '' }, 'Kode'),
    },
    {
        accessorKey: 'asal_pimpinan',
        header: () => h('div', { class: '' }, 'Asal Pimpinan'),
    },
    {
        accessorKey: 'jenis_kelamin',
        header: () => h('div', { class: '' }, 'Jenis Kelamin'),
    },
    {
        accessorKey: 'status',
        header: () => h('div', { class: '' }, 'Status'),
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const peserta = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    pesertaId: peserta.id,
                }),
            );
        },
    },
];