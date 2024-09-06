<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GeneratePlannedItems extends Command
{
    protected $signature = 'planned:generate';
    protected $description = 'Automatically generate or update planned receiving items based on inventory receiving quantities, grouped by inventory_id and receiving_date with vendor assignment';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Group inventory items by inventory_id and receiving_date, summing up the qty
        $groupedInventoryItems = DB::table('inventory_items')
            ->join('inventories', 'inventory_items.inventory_id', '=', 'inventories._id')
            ->select('inventory_items.inventory_id', 'inventory_items.receiving_date', DB::raw('SUM(inventory_items.qty) as total_qty'), 'inventories.location_id')
            ->where('inventory_items.is_out', 0)
            ->whereNotNull('inventory_items.receiving_date')
            ->groupBy('inventory_items.inventory_id', 'inventory_items.receiving_date', 'inventories.location_id')
            ->get();

        foreach ($groupedInventoryItems as $item) {
            // Determine the vendor name based on the location_id
            $vendorName = $this->getVendorNameByLocation($item->location_id);

            // Check if the planned item already exists for the inventory item on the given date
            $existingPlan = DB::table('planned_inventory_items')
                ->where('inventory_id', $item->inventory_id)
                ->where('planned_receiving_date', Carbon::parse($item->receiving_date)->toDateString()) // Same date as receiving
                ->first();

            // If the planned record doesn't exist, insert a new planned item
            if (!$existingPlan) {
                DB::table('planned_inventory_items')->insert([
                    '_id' => uniqid(),
                    'inventory_id' => $item->inventory_id,
                    'planned_receiving_date' => Carbon::parse($item->receiving_date)->toDateString(),
                    'planned_qty' => $item->total_qty, // Use the accumulated qty
                    'vendor_name' => $vendorName,
                    'status' => 'pending', // Default status
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                $this->info("Planned item created for inventory ID: {$item->inventory_id} with vendor: {$vendorName} and accumulated qty: {$item->total_qty}.");
            } else {
                // If the planned record exists, update the planned_qty to reflect the accumulated qty
                DB::table('planned_inventory_items')
                    ->where('_id', $existingPlan->_id)
                    ->update([
                        'planned_qty' => $item->total_qty, // Update to match the accumulated qty
                        'vendor_name' => $vendorName, // Ensure the correct vendor name is updated
                        'updated_at' => Carbon::now()
                    ]);

                $this->info("Planned item updated for inventory ID: {$item->inventory_id}, vendor: {$vendorName}, updated qty: {$item->total_qty}.");
            }
        }

        $this->info('Planned items generation and update process completed.');
    }

    /**
     * Get the vendor name based on the location_id
     */
    private function getVendorNameByLocation($locationId)
    {
        if ($locationId == '6582ef8060c9390d890568d4') {
            return 'MKM';
        } elseif ($locationId == '65a72c7fad782dc26a0626f6') {
            return 'SENOPATI';
        } else {
            return 'UNKNOWN'; // Default or other vendor name if needed
        }
    }
}

