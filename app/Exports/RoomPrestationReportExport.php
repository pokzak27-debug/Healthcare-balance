<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RoomPrestationReportExport implements WithMultipleSheets
{
    public function __construct(
        protected array $byRoomRows,
        protected array $byPrestationRows,
        protected string $month
    ) {}

    public function sheets(): array
    {
        return [
            new class($this->byRoomRows) implements FromArray, WithHeadings, WithTitle {
                public function __construct(protected array $data) {}

                public function array(): array
                {
                    return $this->data;
                }

                public function headings(): array
                {
                    return ['Salle', 'Nombre de RDV'];
                }

                public function title(): string
                {
                    return 'Par salle';
                }
            },
            new class($this->byPrestationRows) implements FromArray, WithHeadings, WithTitle {
                public function __construct(protected array $data) {}

                public function array(): array
                {
                    return $this->data;
                }

                public function headings(): array
                {
                    return ['Prestation', 'Nombre de RDV'];
                }

                public function title(): string
                {
                    return 'Par prestation';
                }
            },
        ];
    }
}
