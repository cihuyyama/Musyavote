<?php

namespace App\Exports;

use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PesertaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Peserta::with('kehadiran')
            ->orderBy('kode_unik')
            ->get();
    }

    /**
     * Map data untuk Excel
     */
    public function map($peserta): array
    {
        return [
            $peserta->kode_unik,
            $peserta->nama,
            $peserta->asal_pimpinan,
            $peserta->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $peserta->status,
            $peserta->password_plain,
            $peserta->kehadiran ? $peserta->kehadiran->pleno_1 : 0,
            $peserta->kehadiran ? $peserta->kehadiran->pleno_2 : 0,
            $peserta->kehadiran ? $peserta->kehadiran->pleno_3 : 0,
            $peserta->kehadiran ? $peserta->kehadiran->pleno_4 : 0,
            $peserta->kehadiran ? $peserta->kehadiran->total_kehadiran : 0,
            $peserta->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Headings untuk Excel
     */
    public function headings(): array
    {
        return [
            'KODE UNIK',
            'NAMA LENGKAP', 
            'ASAL PIMPINAN',
            'JENIS KELAMIN',
            'STATUS',
            'PASSWORD',
            'PLENO 1',
            'PLENO 2',
            'PLENO 3', 
            'PLENO 4',
            'TOTAL KEHADIRAN',
            'TANGGAL DIBUAT'
        ];
    }

    /**
     * Judul worksheet
     */
    public function title(): string
    {
        return 'Data Peserta';
    }

    /**
     * Styling untuk Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2D5F7F']]
            ],
            
            // Auto size columns
            'A:L' => [
                'alignment' => ['vertical' => 'center']
            ],
        ];
    }
}