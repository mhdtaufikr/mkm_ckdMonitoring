<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PlannedInventoryItemsExport implements FromArray, WithHeadings, WithEvents
{
    public function array(): array
    {
        return [
            // Example row data (optional, can be left empty)
        ];
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Planned Receiving Date',
            'Planned Quantity',
            'Vendor Name'
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getComment('B1')->getText()->createTextRun("Format: dd/mm/yyyy");
            },
        ];
    }
}
