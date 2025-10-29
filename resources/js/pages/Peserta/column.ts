import { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import DropdownAction from './DataTableDropDown.vue'

export interface Peserta {
    id: string
    nama: string
    foto: string | null
    asal_pimpinan: string
    jenis_kelamin: string
    status: string
}

export const pesertaColumn: ColumnDef<Peserta>[] = [
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
    }
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
      const peserta = row.original

      return h('div', { class: 'relative' }, h(DropdownAction, {
        pesertaId: peserta.id,
      }))
    },
  },
]