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
    public function collection()
    {
        return Peserta::with('kehadiran')->get();
    }

    public function headings(): array
    {
        return [
            'NAMA',
            'ASAL PIMPINAN', 
            'JENIS KELAMIN',
            'STATUS',
            'TOTAL KEHADIRAN',
            'PLENO 1',
            'PLENO 2',
            'PLENO 3',
            'PLENO 4'
        ];
    }

    public function map($peserta): array
    {
        return [
            $peserta->nama,
            $peserta->asal_pimpinan,
            $peserta->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $peserta->status,
            $peserta->kehadiran ? $peserta->kehadiran->total_kehadiran : 0,
            $peserta->kehadiran ? $peserta->kehadiran->pleno_1 : 0,
            $peserta->kehadiran ? $peserta->kehadiran->pleno_2 : 0,
            $peserta->kehadiran ? $peserta->kehadiran->pleno_3 : 0,
            $peserta->kehadiran ? $peserta->kehadiran->pleno_4 : 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E86C1']]
            ],
            // Style untuk data
            'A2:I' . ($sheet->getHighestRow()) => [
                'font' => ['size' => 11],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Data Peserta';
    }
}