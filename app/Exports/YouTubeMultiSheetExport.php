<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class YouTubeMultiSheetExport implements WithMultipleSheets
{
    protected $validData;
    protected $invalidUrls;
    protected $missingUrls;
    protected $duplicateUrls;

    public function __construct($validData, $invalidUrls, $missingUrls, $duplicateUrls)
    {
        $this->validData = $validData;
        $this->invalidUrls = $invalidUrls;
        $this->missingUrls = $missingUrls;
        $this->duplicateUrls = $duplicateUrls;
    }

    public function sheets(): array
    {
        $sheets = [];

        $validData = !empty($this->validData)
            ? $this->validData
            : [['No videos could be fetched successfully.']];

        $sheets[] = new ChannelExport($validData, 'Fetched Videos'); // Sheet 1 title

        if (!empty($this->invalidUrls) || !empty($this->missingUrls) || !empty($this->duplicateUrls)) {
            $sheets[] = new InvalidExport($this->invalidUrls, $this->missingUrls, $this->duplicateUrls, 'Errors'); // Sheet 2 title
        }

        return $sheets;
    }
}
