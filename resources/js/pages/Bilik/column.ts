import { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import DropdownAction from './DataTableDropDown.vue'

export interface Bilik {
    id: string
    nama: string
    status: string
}

export const bilikColumn: ColumnDef<Bilik>[] = [
  {
    accessorKey: 'nama',
    header: () => h('div', { class: '' }, 'Nama Lengkap'),
  },
  {
    accessorKey: 'status',
    header: () => h('div', { class: '' }, 'Status'),
  },
  {
    id: 'actions',
    enableHiding: false,
    cell: ({ row }) => {
      const bilik = row.original

      return h('div', { class: 'relative' }, h(DropdownAction, {
        bilikId: bilik.id,
      }))
    },
  },
]