<?php
// File: app/Exports/CalonExport.php - BARU

namespace App\Exports;

use App\Models\Calon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CalonExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function collection()
    {
        return Calon::orderBy('jabatan')
                   ->orderBy('nomor_urut')
                   ->get();
    }
    
    public function headings(): array
    {
        return [
            'Nama',
            'Asal Pimpinan',
            'Jenis Kelamin',
            'Nomor Urut',
            'Jabatan',
            'Foto URL',
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ];
    }
    
    public function map($calon): array
    {
        return [
            $calon->nama,
            $calon->asal_pimpinan,
            $calon->jenis_kelamin,
            $calon->nomor_urut,
            $calon->jabatan,
            $calon->foto ? asset('storage/' . $calon->foto) : '',
            $calon->created_at->format('Y-m-d H:i:s'),
            $calon->updated_at->format('Y-m-d H:i:s'),
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2C3E50']]],
            'A' => ['font' => ['bold' => false]],
            'B' => ['font' => ['bold' => false]],
            'C' => ['font' => ['bold' => false]],
            'D' => ['font' => ['bold' => false]],
            'E' => ['font' => ['bold' => false]],
        ];
    }
    
    public function title(): string
    {
        return 'Data Calon';
    }
}