<?php 

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvalidExport implements FromArray, WithTitle, WithStyles
{
    protected $invalid;
    protected $missing; 
    protected $duplicates;

    public function __construct($invalid, $missing, $duplicates)
    {
        $this->invalid = $invalid;
        $this->missing = $missing;
        $this->duplicates = $duplicates;
    }

    public function array(): array
    {
        $data = [];

        if (!empty($this->invalid)) {
            $data[] = ['❌ Invalid URLs'];
            $data = array_merge($data, array_map(fn($url) => [$url], $this->invalid));
            $data[] = ['']; // Spacer row
            $data[] = ['']; // Extra spacer
        }

        if (!empty($this->missing)) {
            $data[] = ['⚠️ Missing URLs (Not Found from API)'];
            $data = array_merge($data, array_map(fn($url) => [$url], $this->missing));
            $data[] = [''];
            $data[] = [''];
        }

        if (!empty($this->duplicates)) {
            $data[] = ['🔁 Duplicate URLs'];
            $data = array_merge($data, array_map(fn($url) => [$url], $this->duplicates));
        }

        return $data ?: [['No invalid, missing, or duplicate URLs']];
    }

    public function title(): string
    {
        return 'Not Fetched';
    }

    public function styles(Worksheet $sheet)
    {
        $styleRows = [];

        $rowIndex = 1;
        if (!empty($this->invalid)) {
            $styleRows[$rowIndex] = ['font' => ['bold' => true]];
            $rowIndex += count($this->invalid) + 3;
        }

        if (!empty($this->missing)) {
            $styleRows[$rowIndex] = ['font' => ['bold' => true]];
            $rowIndex += count($this->missing) + 3;
        }

        if (!empty($this->duplicates)) {
            $styleRows[$rowIndex] = ['font' => ['bold' => true]];
        }

        return $styleRows;
    }
}
