<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChannelExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $channelData;

    public function __construct(array $channelData)
    {
        // Remove "dislikes" column from each row
        $this->channelData = array_map(function ($row) {
            unset($row['Dislikes']); // Key must match exactly
            return $row;
        }, $channelData);
    }

    public function array(): array
    {
        return $this->channelData;
    }

    public function headings(): array
    {
        return !empty($this->channelData) && is_array($this->channelData[0])
            ? array_keys($this->channelData[0])
            : [];
    }

    public function title(): string
    {
        return 'Results';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Make headings bold
        ];
    }

    public function columnWidths(): array
    {
        // You can customize widths per column label
        return [
            'A' => 50, // Video URL
            'B' => 25, // Channel Name
            'C' => 50, // Title
            'D' => 22, // Published Date
            'E' => 12, // Views
            'F' => 12, // Likes
            'G' => 12, // Comments
            'H' => 25, // Video ID
            'I' => 12, // Duration
        ];
    }
}
