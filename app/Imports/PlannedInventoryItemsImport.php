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

                // Hitung jumlah produk dengan kode belakang yang sesuai
                $count = $inventories->count();

                if ($count > 0) {
                    foreach (range(1, 31) as $day) {
                        $plannedQty = $row[(string)$day];
                        if ($plannedQty) {  // Proses hanya jika ada quantity yang direncanakan
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
                } else {
                    throw new Exception("No inventories found with suffix {$productCodeSuffix} for location {$row['location_id']}");
                }
            } else {
                // Logika sebelumnya untuk location_id yang tidak spesial
                $inventory = Inventory::where('code', $row['product_code'])->where('location_id', $row['location_id'])->first();

                if ($inventory) {
                    foreach (range(1, 31) as $day) {
                        $plannedQty = $row[(string)$day];
                        if ($plannedQty) {  // Hanya proses jika ada quantity yang direncanakan
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
                                    'planned_qty' => $plannedQty,
                                    'status' => 'planned',
                                    'vendor_name' => $row['vendor_name']
                                ]);
                            }
                        }
                    }
                } else {
                    throw new Exception("Inventory not found for product code {$row['product_code']} and location {$row['location_id']}");
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
