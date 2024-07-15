<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\InventoryComparison;

class HomeController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get comparisons for the current month
        $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
            ->whereYear('planned_receiving_date', $currentYear)
            ->get();

        // Group by item_code
        $itemCodes = $comparisons->groupBy('item_code');

        // Fetch stock levels from the view
        $stockLevels = DB::table('inventory_stock_level')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get()
            ->groupBy('item_code');

        // Fetch the sum of planned and actual quantities grouped by vendor name and date
        $vendorData = DB::table('vendor_comparison')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('vendor_name');

        // Fetch item code quantities from the inventories table
        $itemCodeQuantities = DB::table('inventories')
            ->select('code', 'qty')
            ->get()
            ->groupBy(function ($item, $key) {
                return floor($key / 10); // Split the item codes into groups of 10 for the carousel
            });

        // Calculate average OTD for Inventory Monitoring
        $totalPlannedQtyInventory = $comparisons->sum('planned_qty');
        $totalReceivedQtyInventory = $comparisons->sum('received_qty'); // assuming you have received_qty column
        $averageInventoryMonitoring = ($totalPlannedQtyInventory > 0) ? ($totalReceivedQtyInventory / $totalPlannedQtyInventory) * 100 : 0;

        // Calculate average OTD for OTDC
        $totalPlannedQtyOTDC = $vendorData->flatten()->sum('total_planned_qty');
        $totalReceivedQtyOTDC = $vendorData->flatten()->sum('total_actual_qty');
        $averageOTDC = ($totalPlannedQtyOTDC > 0) ? ($totalReceivedQtyOTDC / $totalPlannedQtyOTDC) * 100 : 0;

        return view('home.index', compact('itemCodes', 'stockLevels', 'vendorData', 'itemCodeQuantities', 'averageInventoryMonitoring', 'averageOTDC'));
    }
}
