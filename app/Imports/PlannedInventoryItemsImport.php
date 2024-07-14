<?php

namespace App\Imports;

use App\Models\PlannedInventoryItem;
use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class PlannedInventoryItemsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $inventory = Inventory::where('code', $row['product_code'])->first();

        if ($inventory) {
            return new PlannedInventoryItem([
                '_id' => uniqid(),
                'inventory_id' => $inventory->_id,
                'planned_receiving_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['planned_receiving_date'])->format('Y-m-d'), // Convert Excel date to PHP date
                'planned_qty' => $row['planned_quantity'],
                'status' => 'planned' // Assuming a default status of 'planned' since it is not in the file
            ]);
        }
    }
}
