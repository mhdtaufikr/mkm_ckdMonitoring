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
        $response = Http::withHeaders([
            'x-api-key' => '315f9f6eb55fd6db9f87c0c0862007e0615ea467'
        ])->get('https://api.mile.app/public/v1/warehouse/inventory-item', [
            'location_id' => '5f335f29a2ef087afa109156',
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

            foreach ($items as $item) {
                // Find the corresponding inventory by code
                $inventory = Inventory::where('code', $item['code'])->first();

                if ($inventory) {
                    // Extract rack_type from the first word of rack
                    $rackType = explode(' ', $item['rack'])[0];

                    // Get the vendor name from the custom_field_product
                    $vendorName = $item['cutting_center'] ?? null;

                    InventoryItem::updateOrCreate(
                        ['_id' => $item['_id']],
                        [
                            'inventory_id' => $inventory->_id,
                            'serial_number' => $item['serial_number'] ?? null,
                            'rack' => $item['rack'] ?? null,
                            'rack_type' => $rackType ?? null,
                            'qty' => $item['qty'] ?? 0,
                            'status' => $item['status'] ?? null,
                            'receiving_date' => $item['receive_date'] ?? null,
                            'refNumber' => $item['refNumber'] ?? null,
                            'is_out' => $item['is_out'] ?? false,
                            'vendor_name' => $vendorName,
                            'updated_at' => $item['updated_at'] ?? null,
                            'created_at' => $item['created_at'] ?? null
                        ]
                    );
                }
            }
        } else {
            Log::error('Failed to fetch inventory item data:', $response->body());
        }

        $this->info('Inventory item data fetched and stored successfully.');
    }
}
