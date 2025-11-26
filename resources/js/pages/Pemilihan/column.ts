import { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import DropdownAction from './DataTableDropDown.vue'

export interface Pemilihan {
    id: string
    nama_pemilihan: string
    minimal_kehadiran: number
    boleh_tidak_memilih: boolean
}

export const pemilihanColumn: ColumnDef<Pemilihan>[] = [
  {
    accessorKey: 'nama_pemilihan',
    header: () => h('div', { class: '' }, 'Nama Pemilihan'),
  },
  {
    accessorKey: 'minimal_kehadiran',
    header: () => h('div', { class: '' }, 'Minimal Kehadiran'),
  },
  {
    accessorKey: 'boleh_tidak_memilih',
    header: () => h('div', { class: '' }, 'Boleh Tidak Memilih'),
    cell: ({ row }) => {
      const value = row.getValue('boleh_tidak_memilih') as boolean
      return h('div', { class: '' }, value ? 'Ya' : 'Tidak')
    }
  },
  {
    id: 'actions',
    enableHiding: false,
    cell: ({ row }) => {
      const bilik = row.original

      return h('div', { class: 'relative' }, h(DropdownAction, {
        pemilihanId: bilik.id,
      }))
    },
  },
]