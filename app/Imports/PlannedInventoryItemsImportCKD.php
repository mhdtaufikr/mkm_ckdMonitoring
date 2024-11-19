<?php

namespace App\Imports;

use App\Models\PlannedInventoryItem;
use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class PlannedInventoryItemsImportCKD implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            $currentMonth = now()->format('Y-m'); // Current year and month (e.g., 2024-11)
            $specialLocationId = "65a72c7fad782dc26a0626f6";
            $lastThreeChars = $row['product_code']; // e.g., ABV, SL

            // Step 1: Fetch the inventory matching the last three characters and location ID
            $inventory = Inventory::where('location_id', $specialLocationId)
                ->where('code', 'like', '%' . $lastThreeChars) // Match last three characters
                ->first();

            if (!$inventory) {
                // Log and skip if no matching inventory is found
                \Log::error("No inventory found for code suffix {$lastThreeChars} at location {$specialLocationId}");
                return;
            }

            // Step 2: Process planned quantities
            foreach (range(1, 31) as $day) {
                $plannedQty = $row[(string)$day] ?? 0; // Planned quantity for the day

                if (is_numeric($plannedQty) && $plannedQty > 0) {
                    $plannedReceivingDate = $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);

                    // Check if a record already exists for this inventory and date
                    $existingPlannedItem = PlannedInventoryItem::where('inventory_id', $inventory->_id)
                        ->where('planned_receiving_date', $plannedReceivingDate)
                        ->first();

                    if ($existingPlannedItem) {
                        // If the record exists, accumulate the quantity
                        $existingPlannedItem->update([
                            'planned_qty' => $existingPlannedItem->planned_qty + $plannedQty,
                            'updated_at' => now(),
                        ]);
                    } else {
                        // If no record exists, create a new one
                        PlannedInventoryItem::create([
                            '_id' => uniqid(),
                            'inventory_id' => $inventory->_id,
                            'planned_receiving_date' => $plannedReceivingDate,
                            'planned_qty' => $plannedQty,
                            'vendor_name' => $row['vendor_name'],
                            'status' => 'planned',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            \Log::error("Error processing row: " . $e->getMessage());
            throw $e;
        }
    }

    public function headingRow(): int
    {
        return 1; // Assuming your file has headings in the first row
    }
}
