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
        $locationId = '5f335f29a2ef087afa109156';
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $today = now()->format('Y-m-d');

        // Get comparisons for the current month until today
        $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
            ->whereYear('planned_receiving_date', $currentYear)
            ->whereDate('planned_receiving_date', '<=', $today)
            ->where('id_location', $locationId)
            ->get();

        // Group by item_code
        $itemCodes = $comparisons->groupBy('item_code');

        // Fetch the sum of planned and actual quantities grouped by vendor name and date for the current month until today
        $vendorData = DB::table('vendor_comparison')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereIn('vendor_name', ['MOSSI', 'SCI', 'USC', 'AAP'])
            ->where('location_id', $locationId)
            ->get()
            ->groupBy('vendor_name');

        // Fetch vendor monthly summary
        $vendorMonthlySummary = DB::table('vendor_monthly_summary')
            ->select('vendor_name', 'total_planned_qty', 'total_actual_qty')
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('location_id', $locationId)
            ->get();

        // Fetch item code quantities from the inventories table
        $itemCodeQuantities = DB::table('inventories')
            ->select('code', 'qty')
            ->where('location_id', $locationId)
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

        // Prepare data for the chart
        $vendors = $vendorMonthlySummary->pluck('vendor_name')->toArray();
        $totalPlanned = $vendorMonthlySummary->pluck('total_planned_qty')->toArray();
        $totalActual = $vendorMonthlySummary->pluck('total_actual_qty')->toArray();

        return view('home.index', compact('itemCodes', 'vendorData', 'itemCodeQuantities', 'averageInventoryMonitoring', 'averageOTDC', 'vendors', 'totalPlanned', 'totalActual'));
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


    public function indexCkd()
    {
        $locationId = '65a72c7fad782dc26a0626f6';
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $today = now()->format('Y-m-d');

        // Get comparisons for the current month until today
        $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
            ->whereYear('planned_receiving_date', $currentYear)
            ->whereDate('planned_receiving_date', '<=', $today)
            ->where('id_location', $locationId)
            ->get();

        // Group by item_code
        $itemCodes = $comparisons->groupBy('item_code');

        // Fetch the sum of planned and actual quantities grouped by vendor name and date for the current month until today
        $vendorData = DB::table('vendor_comparison')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereIn('vendor_name', ['MOSSI', 'SCI', 'USC', 'AAP'])
            ->where('location_id', $locationId)
            ->get()
            ->groupBy('vendor_name');

        // Fetch vendor monthly summary
        $vendorMonthlySummary = DB::table('vendor_monthly_summary')
            ->select('vendor_name', 'total_planned_qty', 'total_actual_qty')
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('location_id', $locationId)
            ->get();

        // Fetch item code quantities from the inventories table
        $itemCodeQuantities = DB::table('inventories')
            ->select('code', 'qty')
            ->where('location_id', $locationId)
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

        // Prepare data for the chart
        $vendors = $vendorMonthlySummary->pluck('vendor_name')->toArray();
        $totalPlanned = $vendorMonthlySummary->pluck('total_planned_qty')->toArray();
        $totalActual = $vendorMonthlySummary->pluck('total_actual_qty')->toArray();

        return view('home.index', compact('itemCodes', 'vendorData', 'itemCodeQuantities', 'averageInventoryMonitoring', 'averageOTDC', 'vendors', 'totalPlanned', 'totalActual'));
    }

    public function indexCkdNouba()
    {
        $locationId = '617bd0ad83ef510374337d84';
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $today = now()->format('Y-m-d');

        // Get comparisons for the current month until today
        $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
            ->whereYear('planned_receiving_date', $currentYear)
            ->whereDate('planned_receiving_date', '<=', $today)
            ->where('id_location', $locationId)
            ->get();

        // Group by item_code
        $itemCodes = $comparisons->groupBy('item_code');

        // Fetch the sum of planned and actual quantities grouped by vendor name and date for the current month until today
        $vendorData = DB::table('vendor_comparison')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereIn('vendor_name', ['MOSSI', 'SCI', 'USC', 'AAP'])
            ->where('location_id', $locationId)
            ->get()
            ->groupBy('vendor_name');

        // Fetch vendor monthly summary
        $vendorMonthlySummary = DB::table('vendor_monthly_summary')
            ->select('vendor_name', 'total_planned_qty', 'total_actual_qty')
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('location_id', $locationId)
            ->get();

        // Fetch item code quantities from the inventories table
        $itemCodeQuantities = DB::table('inventories')
            ->select('code', 'qty')
            ->where('location_id', $locationId)
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

        // Prepare data for the chart
        $vendors = $vendorMonthlySummary->pluck('vendor_name')->toArray();
        $totalPlanned = $vendorMonthlySummary->pluck('total_planned_qty')->toArray();
        $totalActual = $vendorMonthlySummary->pluck('total_actual_qty')->toArray();

        return view('home.index', compact('itemCodes', 'vendorData', 'itemCodeQuantities', 'averageInventoryMonitoring', 'averageOTDC', 'vendors', 'totalPlanned', 'totalActual'));
    }



}

