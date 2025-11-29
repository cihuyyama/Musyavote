<?php

namespace App\Exports;

use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KartuPesertaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $pesertaIds;

    public function __construct($pesertaIds = [])
    {
        $this->pesertaIds = $pesertaIds;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Peserta::with('kehadiran')
            ->orderBy('kode_unik');

        if (!empty($this->pesertaIds)) {
            $query->whereIn('id', $this->pesertaIds);
        }

        return $query->get();
    }

    /**
     * Map data untuk kartu peserta
     */
    public function map($peserta): array
    {
        return [
            $peserta->kode_unik,
            $peserta->nama,
            $peserta->asal_pimpinan,
            $peserta->password_plain,
            'QR Code tersimpan di sistem',
            $peserta->kehadiran ? $peserta->kehadiran->total_kehadiran : 0,
            $peserta->status,
        ];
    }

    /**
     * Headings untuk kartu peserta
     */
    public function headings(): array
    {
        return [
            'KODE UNIK',
            'NAMA LENGKAP', 
            'ASAL PIMPINAN',
            'PASSWORD LOGIN',
            'QR CODE',
            'TOTAL KEHADIRAN',
            'STATUS'
        ];
    }

    /**
     * Judul worksheet
     */
    public function title(): string
    {
        return 'Kartu Peserta';
    }

    /**
     * Styling untuk Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Auto size columns
        foreach(range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            // Style untuk header
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2D5F7F']],
                'alignment' => ['horizontal' => 'center']
            ],
            
            // Style untuk data
            'A2:G1000' => [
                'alignment' => ['vertical' => 'center'],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
        ];
    }
}