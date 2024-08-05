<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\MasterProduct;

class FetchProducts extends Command
{
    protected $signature = 'fetch:products';
    protected $description = 'Fetch products from API and store in the master_products table';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(300);

        $page = -1;
        $limit = -1; // Adjust the limit according to the API documentation and performance needs
        $moreData = true;

        while ($moreData) {
            $response = Http::withHeaders([
                'x-api-key' => '315f9f6eb55fd6db9f87c0c0862007e0615ea467',
                'Content-Type' => 'application/json'
            ])->get('https://api.mile.app/public/v1/warehouse/product', [
                'location_id' => '',
                'stock_status' => '',
                'limit' => $limit,
                'page' => $page,
                's' => '',
                'show_item' => false
            ]);

            if ($response->successful()) {
                $products = $response->json()['data']; // Assuming 'data' key contains the products

                foreach ($products as $product) {
                    MasterProduct::updateOrCreate(
                        ['_id' => $product['_id']],
                        [
                            'code' => $product['code'] ?? null,
                            'name' => $product['name'] ?? null,
                            'levelmin' => $product['levelmin'] ?? 0,
                            'levelmax' => $product['levelmax'] ?? 0,
                            'has_sn' => $product['has_sn'] ?? false,
                            'attributes' => json_encode($product['attributes'] ?? []),
                            'no_case' => $product['custom_field']['no_case'] ?? null,
                            'variantCode' => $product['custom_field']['variantCode'] ?? null,
                            'dest_delivery' => $product['custom_field']['dest_delivery'] ?? null,
                            'prod_process' => $product['custom_field']['prod_process'] ?? null,
                            'part_no' => $product['custom_field']['part_no'] ?? null,
                            'group_no' => $product['custom_field']['group_no'] ?? null,
                            'g_number' => $product['custom_field']['g_number'] ?? null,
                            'model' => $product['custom_field']['model'] ?? null,
                            'cutting_center' => $product['custom_field']['cutting_center'] ?? null,
                            'press_destination' => $product['custom_field']['press_destination'] ?? null,
                            'tags' => json_encode($product['tags'] ?? []),
                            'length' => $product['dimension']['length'] ?? 0,
                            'width' => $product['dimension']['width'] ?? 0,
                            'height' => $product['dimension']['height'] ?? 0,
                            'weight' => $product['dimension']['weight'] ?? 0,
                            'color' => $product['color'] ?? null,
                            'default_sn_formula' => $product['default_sn_formula'] ?? null,
                            'default_unit' => $product['default_unit'] ?? null,
                            'organization_id' => $product['organization_id'] ?? null,
                            'updated_at' => now(),
                            'created_at' => now()
                        ]
                    );
                }

                $moreData = count($products) == $limit;
                $page++;
            } else {
                $this->error('Failed to fetch products from API.');
                $moreData = false;
            }
        }

        $this->info('Products fetched and stored successfully.');
    }
}

