<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RoomPrestationReportExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        protected array $byPrestationRows,
        protected string $month
    ) {}

    public function array(): array
    {
        return $this->byPrestationRows;
    }

    public function headings(): array
    {
        return ['Prestation', 'Nombre de RDV'];
    }

    public function title(): string
    {
        return 'Par prestation';
    }
}
