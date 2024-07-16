<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory;
use App\Models\MstLocation;

class FetchInventoryData extends Command
{
    protected $signature = 'fetch:inventory';
    protected $description = 'Fetch inventory data from API and store in database';

    public function handle()
    {
        $apiKey = '315f9f6eb55fd6db9f87c0c0862007e0615ea467'; // Replace with your actual API key
        $locationIds = [
            '5f335f29a2ef087afa109156',
            '65a72c7fad782dc26a0626f6',
            '617bd0ad83ef510374337d84'
        ];

        foreach ($locationIds as $locationId) {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey
            ])->get('https://api.mile.app/public/v1/warehouse/inventory', [
                'location_id' => $locationId,
                'stock_status' => 'normal',
                'limit' => -1,
                'page' => 1,
                's' => '',
                'show_item' => false
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['collection']['data'];

                foreach ($items as $item) {
                    // Ensure location exists
                    $location = MstLocation::updateOrCreate(
                        ['_id' => $item['location_id']],
                        [
                            'name' => $item['location']['name'],
                            'code' => $item['location']['location_code'],
                            'address' => $item['location']['address'],
                            'phone' => $item['location']['phone'],
                            'location_type' => $item['location']['location_type'],
                            'lat' => $item['location']['lat'],
                            'lng' => $item['location']['lng']
                        ]
                    );

                    Inventory::updateOrCreate(
                        ['_id' => $item['_id']],
                        [
                            'code' => $item['code'],
                            'name' => $item['name'],
                            'qty' => $item['qty'],
                            'location_id' => $item['location_id'],
                            'organization_id' => $item['organization_id'],
                            'updated_at' => $item['updated_at'],
                            'created_at' => $item['created_at']
                        ]
                    );
                }
            } else {
                Log::error('Failed to fetch inventory data for location ' . $locationId, [
                    'response_body' => $response->body()
                ]);
            }
        }

        $this->info('Inventory data fetched and stored successfully.');
    }
}
