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
            'Vendor Name',
            'Location Id',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12',
            '13',
            '14',
            '15',
            '16',
            '17',
            '18',
            '19',
            '20',
            '21',
            '22',
            '23',
            '24',
            '25',
            '26',
            '27',
            '28',
            '29',
            '30',
            '31',

        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getComment('C1')->getText()->createTextRun("Get From IWS");
            },
        ];
    }
}
