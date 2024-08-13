<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\InventoryItem;
use App\Models\Inventory;

class FetchInventoryItemData extends Command
{
    protected $signature = 'fetch:inventory-item';
    protected $description = 'Fetch inventory item data from API and store in database';

    public function handle()
    {
        $apiKey = '315f9f6eb55fd6db9f87c0c0862007e0615ea467'; // Replace with your actual API key
        $locationIds = [
            '5f335f29a2ef087afa109156',
            '65a72c7fad782dc26a0626f6',
            '617bd0ad83ef510374337d84',
            '5fc4b12bc329204cb00b56bf',
            '65bd1017b4490c26c00a82d9',
            '6582ef8060c9390d890568d4'
        ];

        foreach ($locationIds as $locationId) {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey
            ])->get('https://api.mile.app/public/v1/warehouse/inventory-item', [
                'location_id' => $locationId,
                'limit' => -1,
                'page' => 1,
                'serial_number' => '',
                'rack' => '',
                'rack_type' => '',
                'start_date' => '',
                'end_date' => ''
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'];

                // Fetch existing inventory items for the specific location
                $existingInventoryItems = InventoryItem::whereIn('inventory_id', Inventory::where('location_id', $locationId)->pluck('_id'))
                    ->get()
                    ->keyBy('_id');

                    foreach ($items as $item) {
                        // Find the corresponding inventory by code and location_id
                        $inventory = Inventory::where('code', $item['code'])->where('location_id', $locationId)->first();

                        if ($inventory) {
                            // Extract rack_type from the first word of rack
                            $rackType = explode(' ', $item['rack'])[0];

                            // Determine if the item exists in the current location's inventory items
                            $existingInventoryItem = InventoryItem::where('_id', $item['_id'])
                                ->where('inventory_id', $inventory->_id)
                                ->first();

                            if ($existingInventoryItem) {
                                // If it exists, update the existing record
                                $existingInventoryItem->update([
                                    'serial_number' => $item['serial_number'] ?? null,
                                    'rack' => $item['rack'] ?? null,
                                    'rack_type' => $rackType ?? null,
                                    'qty' => $item['qty'] ?? 0,
                                    'status' => $item['status'] ?? null,
                                    'receiving_date' => $item['receive_date'] ?? null,
                                    'refNumber' => $item['refNumber'] ?? null,
                                    'is_out' => $item['is_out'] ?? false,
                                    'vendor_name' => $this->getVendorName($item, $locationId),
                                    'updated_at' => $item['updated_at'] ?? now(),
                                    'created_at' => $item['created_at'] ?? now()
                                ]);
                            } else {
                                // If it doesn't exist, create a new record
                                InventoryItem::create([
                                    '_id' => $item['_id'],
                                    'inventory_id' => $inventory->_id,
                                    'serial_number' => $item['serial_number'] ?? null,
                                    'rack' => $item['rack'] ?? null,
                                    'rack_type' => $rackType ?? null,
                                    'qty' => $item['qty'] ?? 0,
                                    'status' => $item['status'] ?? null,
                                    'receiving_date' => $item['receive_date'] ?? null,
                                    'refNumber' => $item['refNumber'] ?? null,
                                    'is_out' => $item['is_out'] ?? false,
                                    'vendor_name' => $this->getVendorName($item, $locationId),
                                    'updated_at' => $item['updated_at'] ?? now(),
                                    'created_at' => $item['created_at'] ?? now()
                                ]);
                            }

                            Log::info('Inventory Item Processed: ', ['inventory_item' => $item['_id']]);
                        }
                    }



                // Log fetched inventory item IDs (for debugging purposes)
                Log::info('Fetched inventory item IDs for location ' . $locationId, array_keys($existingInventoryItems->toArray()));
            } else {
                Log::error('Failed to fetch inventory item data for location ' . $locationId, [
                    'response_body' => $response->body()
                ]);
            }
        }

        $this->info('Inventory item data fetched and stored successfully.');
    }

    private function getVendorName($item, $locationId)
    {
        $vendorName = $item['cutting_center'] ?? null;

        if (in_array($locationId, ['65a72c7fad782dc26a0626f6', '617bd0ad83ef510374337d84'])) {
            return $vendorName ?? 'SENOPATI';
        }

        if ($locationId == '6582ef8060c9390d890568d4') {
            return $vendorName ?? 'MKM';
        }

        return $vendorName;
    }
}
