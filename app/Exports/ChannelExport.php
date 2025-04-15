<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ChannelExport implements FromArray, WithHeadings
{
    protected $channelData;

    public function __construct(array $channelData)
    {
        $this->channelData = $channelData;
    }

    public function array(): array
    {
        return $this->channelData;
    }

    public function headings(): array
    {
        // Automatically get headings from the first result to match the selected fields
        return count($this->channelData) > 0
            ? array_keys($this->channelData[0])
            : [];
    }
}
