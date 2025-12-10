<?php

namespace App\Exports;

use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PesertaExport implements FromCollection, WithMapping, WithStyles, WithTitle
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
        ];
    }

    /**
     * Headings untuk Excel
     */
    // public function headings(): array
    // {
    //     return [
    //         'KODE UNIK',
    //         'NAMA LENGKAP', 
    //         'ASAL PIMPINAN',
    //     ];
    // }

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