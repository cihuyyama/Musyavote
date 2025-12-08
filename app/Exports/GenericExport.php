<?php
// File: app/Exports/GenericExport.php - BARU

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GenericExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $data;
    protected $title;
    
    public function __construct(array $data, string $title = 'Sheet1')
    {
        $this->data = $data;
        $this->title = $title;
    }
    
    public function array(): array
    {
        return $this->data;
    }
    
    public function headings(): array
    {
        // Return empty array karena heading sudah ada di data pertama
        return [];
    }
    
    public function title(): string
    {
        return $this->title;
    }
}