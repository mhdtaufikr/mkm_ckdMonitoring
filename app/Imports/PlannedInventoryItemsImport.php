<?php

namespace App\Imports;

use App\Models\PlannedInventoryItem;
use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Exception;

class PlannedInventoryItemsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            // Khusus untuk location_id tertentu
            $specialLocationIds = ['617bd0ad83ef510374337d84', '65a72c7fad782dc26a0626f6'];
            $currentMonth = date('Y-m');

            if (in_array($row['location_id'], $specialLocationIds)) {
                // Ambil 3 huruf belakang dari kode produk
                $productCodeSuffix = $row['product_code'];

                // Dapatkan semua inventory dengan 3 huruf belakang yang sesuai
                $inventories = Inventory::where('location_id', $row['location_id'])
                    ->where('code', 'like', '%' . $productCodeSuffix)
                    ->get();

                // Jika tidak ditemukan inventory, buat inventory baru
                if ($inventories->isEmpty()) {
                    $inventory = Inventory::create([
                        '_id' => uniqid(),
                        'code' => $row['product_code'],
                        'location_id' => $row['location_id'],
                        'name' => 'Auto-generated',
                        'qty' => 0,  // Assume 0 for the quantity, you may adjust as needed
                        'organization_id' => 'auto-generated',  // Adjust as needed
                    ]);

                    foreach (range(1, 31) as $day) {
                        $plannedQty = $row[(string)$day];
                        if (is_numeric($plannedQty) && $plannedQty > 0) {  // Proses hanya jika ada quantity yang direncanakan
                            $plannedReceivingDate = $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                            PlannedInventoryItem::create([
                                '_id' => uniqid(),
                                'inventory_id' => $inventory->_id,
                                'planned_receiving_date' => $plannedReceivingDate,
                                'planned_qty' => (int) $plannedQty,
                                'status' => 'planned',
                                'vendor_name' => $row['vendor_name']
                            ]);
                        }
                    }
                } else {
                    // Hitung jumlah produk dengan kode belakang yang sesuai
                    $count = $inventories->count();

                    foreach (range(1, 31) as $day) {
                        $plannedQty = $row[(string)$day];
                        if (is_numeric($plannedQty) && $plannedQty > 0) {  // Proses hanya jika ada quantity yang direncanakan
                            $splitQty = $plannedQty / $count;
                            $plannedReceivingDate = $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);

                            foreach ($inventories as $inventory) {
                                $plannedItem = PlannedInventoryItem::where('inventory_id', $inventory->_id)
                                    ->where('planned_receiving_date', $plannedReceivingDate)
                                    ->first();

                                if ($plannedItem) {
                                    // Update existing planned item
                                    $plannedItem->update([
                                        'planned_qty' => $splitQty,
                                        'status' => 'planned',
                                        'vendor_name' => $row['vendor_name']
                                    ]);
                                } else {
                                    // Create new planned item
                                    PlannedInventoryItem::create([
                                        '_id' => uniqid(),
                                        'inventory_id' => $inventory->_id,
                                        'planned_receiving_date' => $plannedReceivingDate,
                                        'planned_qty' => $splitQty,
                                        'status' => 'planned',
                                        'vendor_name' => $row['vendor_name']
                                    ]);
                                }
                            }
                        }
                    }
                }
            } else {
                // Logika sebelumnya untuk location_id yang tidak spesial
                $inventory = Inventory::where('code', $row['product_code'])->where('location_id', $row['location_id'])->first();

                if (!$inventory) {
                    // Create a new inventory record if not found
                    $inventory = Inventory::create([
                        '_id' => uniqid(),
                        'code' => $row['product_code'],
                        'location_id' => $row['location_id'],
                        'name' => 'Auto-generated',
                        'qty' => 0,  // Assume 0 for the quantity, you may adjust as needed
                        'organization_id' => 'auto-generated',  // Adjust as needed
                    ]);
                }

                foreach (range(1, 31) as $day) {
                    $plannedQty = $row[(string)$day];
                    if (is_numeric($plannedQty) && $plannedQty > 0) {  // Hanya proses jika ada quantity yang direncanakan
                        $plannedReceivingDate = $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                        $plannedItem = PlannedInventoryItem::where('inventory_id', $inventory->_id)
                            ->where('planned_receiving_date', $plannedReceivingDate)
                            ->first();

                        if ($plannedItem) {
                            // Update existing planned item
                            $plannedItem->update([
                                'planned_qty' => $plannedQty,
                                'status' => 'planned',
                                'vendor_name' => $row['vendor_name']
                            ]);
                        } else {
                            // Create new planned item
                            PlannedInventoryItem::create([
                                '_id' => uniqid(),
                                'inventory_id' => $inventory->_id,
                                'planned_receiving_date' => $plannedReceivingDate,
                                'planned_qty' => (int) $plannedQty,
                                'status' => 'planned',
                                'vendor_name' => $row['vendor_name']
                            ]);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
