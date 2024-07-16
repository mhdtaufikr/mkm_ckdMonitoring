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
        $today = now()->format('Y-m-d');

        // Get comparisons for the current month until today
        $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
            ->whereYear('planned_receiving_date', $currentYear)
            ->whereDate('planned_receiving_date', '<=', $today)
            ->get();

        // Group by item_code
        $itemCodes = $comparisons->groupBy('item_code');

        // Fetch stock levels from the view for the current month until today
        $stockLevels = DB::table('inventory_stock_level')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->whereDate('date', '<=', $today)
            ->get()
            ->groupBy('item_code');

        // Fetch the sum of planned and actual quantities grouped by vendor name and date for the current month until today
        $vendorData = DB::table('vendor_comparison')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereDate('date', '<=', $today)
            ->whereIn('vendor_name', ['MOSSI', 'SCI', 'USC', 'AAP'])
            ->get()
            ->groupBy('vendor_name');

            // Fetch item code quantities from the inventories table
        $itemCodeQuantities = DB::table('inventories')
        ->select('code', 'qty')
        ->get()
        ->groupBy(function ($item) {
            static $groupIndex = 0;
            static $itemCount = 0;
            if ($itemCount++ % 10 == 0) {
                $groupIndex++;
            }
            return $groupIndex;
        });

        // Calculate averages
        $averageInventoryMonitoring = $this->calculateAverageInventoryMonitoring($comparisons);
        $averageOTDC = $this->calculateAverageOTDC($vendorData);
        return view('home.index', compact('itemCodes', 'stockLevels', 'vendorData', 'itemCodeQuantities', 'averageInventoryMonitoring', 'averageOTDC'));
    }

        private function calculateAverageInventoryMonitoring($comparisons)
    {
        $today = now()->format('Y-m-d');
        $filteredComparisons = $comparisons->filter(function ($comparison) use ($today) {
            return $comparison->planned_receiving_date <= $today;
        });

        $totalPlannedQtyInventory = $filteredComparisons->sum('planned_qty');
        $totalReceivedQtyInventory = $filteredComparisons->sum('received_qty'); // assuming you have received_qty column
        return ($totalPlannedQtyInventory > 0) ? ($totalReceivedQtyInventory / $totalPlannedQtyInventory) * 100 : 0;
    }

        private function calculateAverageOTDC($vendorData)
    {
        $today = now()->format('Y-m-d');
        $flattenedData = $vendorData->flatten();
        $filteredData = $flattenedData->filter(function ($data) use ($today) {
            return $data->date <= $today;
        });

        $totalPlannedQtyOTDC = $filteredData->sum('total_planned_qty');
        $totalReceivedQtyOTDC = $filteredData->sum('total_actual_qty');
        return ($totalPlannedQtyOTDC > 0) ? ($totalReceivedQtyOTDC / $totalPlannedQtyOTDC) * 100 : 0;
    }

}

