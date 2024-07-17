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

        // Get planned data
        $plannedData = DB::table('planned_inventory_view')
        ->whereMonth('planned_receiving_date', $currentMonth)
        ->whereYear('planned_receiving_date', $currentYear)
        ->where('location_id',$locationId)
        ->get()
        ->groupBy('item_code');

        // Get actual data
        $actualData = DB::table('actual_inventory_view')
            ->whereMonth('receiving_date', $currentMonth)
            ->whereYear('receiving_date', $currentYear)
            ->whereDate('receiving_date', '<=', $today)
            ->where('location_id',$locationId)
            ->get()
            ->groupBy('item_code');

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

        // Prepare data for the chart
        $vendors = $vendorMonthlySummary->pluck('vendor_name')->toArray();
        $totalPlanned = $vendorMonthlySummary->pluck('total_planned_qty')->toArray();
        $totalActual = $vendorMonthlySummary->pluck('total_actual_qty')->toArray();
        // Fetch vendor monthly summary
        $itemNotArrived = DB::table('items_not_arrived')
        ->whereMonth('planned_receiving_date', now()->month)
        ->whereYear('planned_receiving_date', now()->year)
        ->whereDate('planned_receiving_date', '<=', now()->toDateString())
        ->where('location_id', $locationId)
        ->orderBy('planned_receiving_date', 'desc') // Sort by newest data
        ->get();



        return view('home.index', compact('locationId','itemCodes','plannedData', 'actualData', 'vendorData', 'itemCodeQuantities', 'vendors', 'totalPlanned', 'totalActual','itemNotArrived'));
    }

    public function indexCkd()
    {
        $locationId = '65a72c7fad782dc26a0626f6';
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $today = now()->format('Y-m-d');

        // Get planned data
        $plannedData = DB::table('planned_inventory_view')
        ->whereMonth('planned_receiving_date', $currentMonth)
        ->whereYear('planned_receiving_date', $currentYear)
        ->where('location_id',$locationId)
        ->get()
        ->groupBy('item_code');

        // Get actual data
        $actualData = DB::table('actual_inventory_view')
            ->whereMonth('receiving_date', $currentMonth)
            ->whereYear('receiving_date', $currentYear)
            ->whereDate('receiving_date', '<=', $today)
            ->where('location_id',$locationId)
            ->get()
            ->groupBy('item_code');

        // Fetch the sum of planned and actual quantities grouped by vendor name and date for the current month until today
        $vendorData = DB::table('vendor_comparison')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereIn('vendor_name', ['SENOPATI'])
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
                if ($itemCount++ % 5 == 0) {
                    $groupIndex++;
                }
                return $groupIndex;
            });

        // Fetch variant code quantities from the inventories table
        $variantCodeQuantities = DB::table('inventories')
            ->select('variantCode', DB::raw('SUM(qty) as total_qty'))
            ->where('location_id', $locationId)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereNotNull('variantCode') // Exclude null variantCode
            ->groupBy('variantCode')
            ->orderBy('variantCode')
            ->get()
            ->groupBy(function ($item) {
                static $groupIndex = 0;
                static $itemCount = 0;
                if ($itemCount++ % 5 == 0) {
                    $groupIndex++;
                }
                return $groupIndex;
            });

        // Prepare data for the chart
        $vendors = $vendorMonthlySummary->pluck('vendor_name')->toArray();
        $totalPlanned = $vendorMonthlySummary->pluck('total_planned_qty')->toArray();
        $totalActual = $vendorMonthlySummary->pluck('total_actual_qty')->toArray();

         // Fetch vendor monthly summary
         $itemNotArrived = DB::table('items_not_arrived')
         ->whereMonth('planned_receiving_date', now()->month)
         ->whereYear('planned_receiving_date', now()->year)
         ->whereDate('planned_receiving_date', '<=', now()->toDateString())
         ->where('location_id', $locationId)
         ->orderBy('planned_receiving_date', 'desc') // Sort by newest data
         ->get();

         // Get comparisons for the current month until today
        $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
        ->whereYear('planned_receiving_date', $currentYear)
        ->whereDate('planned_receiving_date', '<=', $today)
        ->where('id_location', $locationId)
        ->get();

    // Group by item_code
    $itemCodes = $comparisons->groupBy('item_code');

        return view('home.ckd', compact('locationId','itemCodes','itemNotArrived','plannedData', 'actualData', 'vendorData', 'itemCodeQuantities', 'vendors', 'totalPlanned', 'totalActual', 'variantCodeQuantities'));
    }



    public function indexCkdNouba()
    {
        $locationId = '617bd0ad83ef510374337d84';
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $today = now()->format('Y-m-d');

        // Get planned data
        $plannedData = DB::table('planned_inventory_view')
        ->whereMonth('planned_receiving_date', $currentMonth)
        ->whereYear('planned_receiving_date', $currentYear)
        ->where('location_id',$locationId)
        ->get()
        ->groupBy('item_code');

        // Get actual data
        $actualData = DB::table('actual_inventory_view')
            ->whereMonth('receiving_date', $currentMonth)
            ->whereYear('receiving_date', $currentYear)
            ->whereDate('receiving_date', '<=', $today)
            ->where('location_id',$locationId)
            ->get()
            ->groupBy('item_code');

        // Fetch the sum of planned and actual quantities grouped by vendor name and date for the current month until today
        $vendorData = DB::table('vendor_comparison')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereIn('vendor_name', ['SENOPATI'])
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
                if ($itemCount++ % 5 == 0) {
                    $groupIndex++;
                }
                return $groupIndex;
            });

        // Fetch variant code quantities from the inventories table
        $variantCodeQuantities = DB::table('inventories')
            ->select('variantCode', DB::raw('SUM(qty) as total_qty'))
            ->where('location_id', $locationId)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereNotNull('variantCode') // Exclude null variantCode
            ->groupBy('variantCode')
            ->orderBy('variantCode')
            ->get()
            ->groupBy(function ($item) {
                static $groupIndex = 0;
                static $itemCount = 0;
                if ($itemCount++ % 5 == 0) {
                    $groupIndex++;
                }
                return $groupIndex;
            });

        // Prepare data for the chart
        $vendors = $vendorMonthlySummary->pluck('vendor_name')->toArray();
        $totalPlanned = $vendorMonthlySummary->pluck('total_planned_qty')->toArray();
        $totalActual = $vendorMonthlySummary->pluck('total_actual_qty')->toArray();

         // Fetch vendor monthly summary
         $itemNotArrived = DB::table('items_not_arrived')
         ->whereMonth('planned_receiving_date', now()->month)
         ->whereYear('planned_receiving_date', now()->year)
         ->whereDate('planned_receiving_date', '<=', now()->toDateString())
         ->where('location_id', $locationId)
         ->orderBy('planned_receiving_date', 'desc') // Sort by newest data
         ->get();

         // Get comparisons for the current month until today
        $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
        ->whereYear('planned_receiving_date', $currentYear)
        ->whereDate('planned_receiving_date', '<=', $today)
        ->where('id_location', $locationId)
        ->get();

    // Group by item_code
    $itemCodes = $comparisons->groupBy('item_code');

        return view('home.ckd', compact('locationId','itemCodes','itemNotArrived','plannedData', 'actualData', 'vendorData', 'itemCodeQuantities', 'vendors', 'totalPlanned', 'totalActual', 'variantCodeQuantities'));
    }
}

