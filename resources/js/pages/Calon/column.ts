import { ColumnDef, FilterFn } from '@tanstack/vue-table'
import { h } from 'vue'
import DropdownAction from './DataTableDropDown.vue'

export interface Peserta {
  id: string
  nama: string
  foto: string | null
  asal_pimpinan: string
  jenis_kelamin: string
  status: string
  calon?: Calon | null
}

export interface Calon {
  id: string
  peserta_id: string
  jabatan: string
}

const jabatanFilterFn: FilterFn<any> = (row, columnId, filterValue) => {
    if (filterValue === 'all' || !filterValue) {
        return true;
    }

    const calonRelasi = row.original.calon; 
    if (calonRelasi) {
        const jabatanValues = Array.isArray(calonRelasi) ? calonRelasi.map((c: Calon) => c.jabatan) : [calonRelasi.jabatan];
        return jabatanValues.includes(filterValue);
    }

    return false;
};

export const calonColumn: ColumnDef<Peserta>[] = [
  {
    accessorKey: 'foto',
    header: () => h('div', { class: '' }, 'Foto'),
    cell: ({ row }) => {
      const peserta = row.original
      return h('div', { class: 'flex' }, [
        h('img', {
          src: peserta.foto ? `/storage/${peserta.foto}` : '/default-avatar.png',
          alt: 'Foto Peserta',
          class: 'w-10 h-10 rounded-full object-cover',
        }),
      ])
    },
  },
  {
    accessorKey: 'nama',
    header: () => h('div', { class: '' }, 'Nama Lengkap'),
  },
  {
    accessorKey: 'calon',
    header: () => h('div', { class: '' }, 'Jabatan'),
    cell: ({ row }) => {
      const peserta = row.original;
      return h('div', {}, peserta.calon ? peserta.calon.jabatan : '-');
    },
    filterFn: jabatanFilterFn,
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
    id: 'actions',
    enableHiding: false,
    cell: ({ row }) => {
      const peserta = row.original
      const calonId = peserta.calon?.id ?? ''

      return h('div', { class: 'relative' }, h(DropdownAction, {
        calonId: calonId,
      }))
    },
  },
]