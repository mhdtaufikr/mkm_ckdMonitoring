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
        set_time_limit(300);
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
        ->select('_id', 'code', 'qty') // Ensure 'id' is selected
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
                set_time_limit(300);
                $locationId = '65a72c7fad782dc26a0626f6';
                $currentMonth = now()->month;
                $currentYear = now()->year;
                $today = now()->format('Y-m-d');

                // Get planned data
                $plannedData = DB::table('planned_inventory_view')
                ->select(
                    '*',
                    DB::raw('DATE(planned_receiving_date) AS planned_receiving_date'),
                    DB::raw('SUM(planned_qty) AS planned_qty')
                )
                ->where('location_id', '6582ef8060c9390d890568d4')
                ->whereMonth('planned_receiving_date', $currentMonth)
                ->whereYear('planned_receiving_date', $currentYear)
                ->groupBy('item_name', DB::raw('DATE(planned_receiving_date)'))
                ->orderBy(DB::raw('DATE(planned_receiving_date)'), 'asc')
                ->orderBy('item_name', 'asc')
                ->get()
                ->groupBy('item_name');

                // Get actual data
                $actualData = DB::table('actual_inventory_view')
                ->select(
                    '*',
                    DB::raw('DATE(receiving_date) AS receiving_date'),
                    DB::raw('SUM(received_qty) AS received_qty')
                )
                ->where('location_id', '6582ef8060c9390d890568d4')
                ->whereMonth('receiving_date', 8)
                ->whereYear('receiving_date', 2024)
                ->groupBy('item_name', DB::raw('DATE(receiving_date)'))
                ->orderBy(DB::raw('DATE(receiving_date)'), 'asc')
                ->orderBy('item_name', 'asc')

                ->get()
                ->groupBy('item_name');



                // Get comparisons for the current month until today
                $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
                ->whereYear('planned_receiving_date', $currentYear)
                ->whereDate('planned_receiving_date', '<=', $today)
                ->where('id_location', '6582ef8060c9390d890568d4')
                ->get();


                $vendorData = DB::table(
                    DB::table('vendor_comparison')
                        ->select(
                            'vendor_name',
                            'date',
                            'total_actual_qty',
                            'total_planned_qty',
                            'percentage'
                        )
                        ->distinct()
                        ->whereMonth('date', $currentMonth)
                        ->whereYear('date', $currentYear)
                        ->whereIn('vendor_name', ['SENOPATI'])
                        ->where('location_id', $locationId)
                )
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
                    ->select('code', 'qty','_id')
                    ->where('location_id', $locationId)
                    ->where('qty', '>', 0)
                    ->get()
                    ->groupBy(function ($item) {
                        static $groupIndex = 0;
                        static $itemCount = 0;
                        if ($itemCount++ % 5 == 0) {
                            $groupIndex++;
                        }
                        return $groupIndex;
                    });

                    // Fetch variant code quantities from the inventories table, joined with master_products
                    $variantCodeQuantities = DB::table('inventories')
                    ->join(DB::raw('(SELECT DISTINCT variantCode, model FROM master_products) as mp'), 'inventories.variantCode', '=', 'mp.variantCode')
                    ->select('inventories.variantCode', 'mp.model', DB::raw('SUM(inventories.qty) as total_qty'))
                    ->where('inventories.location_id', $locationId)
                    ->whereNotNull('inventories.variantCode') // Exclude null variantCode
                    ->whereNotNull('mp.model') // Exclude null model
                    ->groupBy('inventories.variantCode', 'mp.model')
                    ->orderBy('inventories.variantCode')
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

            $plannedDataModel = DB::table('view_planning')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');

        $actualDataModel = DB::table('view_actual')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');

        $comparisonDataModel = DB::table('view_comparison')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');

            $variantCodeQuantitiesCNI = DB::table('inventories')
            ->select('name', DB::raw('SUM(qty) as total_qty'))
            ->where('location_id', '6582ef8060c9390d890568d4')
            ->groupBy('name')
            ->get();


        return view('home.ckd', compact('comparisons','comparisonDataModel','actualDataModel','plannedDataModel','locationId','itemNotArrived','plannedData', 'actualData', 'vendorData', 'itemCodeQuantities', 'vendors', 'totalPlanned', 'totalActual', 'variantCodeQuantities','variantCodeQuantitiesCNI'));
    }



    public function indexCkdNouba()
    {
        set_time_limit(300);
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

            $plannedDataModel = DB::table('view_planning')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');

        $actualDataModel = DB::table('view_actual')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');

        $comparisonDataModel = DB::table('view_comparison')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');

        return view('home.cdknouba', compact('comparisonDataModel','actualDataModel','plannedDataModel','locationId','itemCodes','itemNotArrived','plannedData', 'actualData', 'vendorData', 'itemCodeQuantities', 'vendors', 'totalPlanned', 'totalActual', 'variantCodeQuantities'));
    }

    public function l305()
    {
        set_time_limit(300);
        $locationId = '5fc4b12bc329204cb00b56bf';
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
            ->whereIn('vendor_name', ['PRESS A','PRESS C','PRESS F'])
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

    public function cvcL404()
    {
        set_time_limit(300);
        $locationId = '65bd1017b4490c26c00a82d9';
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $today = now()->format('Y-m-d');

        // Get unique vendor names from inventory_items for the given location_id
        $vendorNames = DB::table('inventory_items')
            ->join('inventories', 'inventory_items.inventory_id', '=', 'inventories._id')
            ->where('inventories.location_id', $locationId)
            ->distinct()
            ->pluck('inventory_items.vendor_name')
            ->toArray();

        // Get planned data
        $plannedData = DB::table('planned_inventory_view')
            ->whereMonth('planned_receiving_date', $currentMonth)
            ->whereYear('planned_receiving_date', $currentYear)
            ->where('location_id', $locationId)
            ->get()
            ->groupBy('item_code');

        // Get actual data
        $actualData = DB::table('actual_inventory_view')
            ->whereMonth('receiving_date', $currentMonth)
            ->whereYear('receiving_date', $currentYear)
            ->whereDate('receiving_date', '<=', $today)
            ->where('location_id', $locationId)
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
            ->whereIn('vendor_name', $vendorNames)
            ->where('location_id', $locationId)
            ->get()
            ->groupBy('vendor_name');

             // Fetch the sum of planned and actual quantities grouped by vendor name and date for the current month until today
             $vendorDataAggregate = DB::table('vendor_comparison_aggregate')
             ->whereMonth('date', $currentMonth)
             ->whereYear('date', $currentYear)
             ->where('location_id', $locationId)
             ->select('date', DB::raw('SUM(total_planned_qty) as total_planned_qty'), DB::raw('SUM(total_actual_qty) as total_actual_qty'), DB::raw('AVG(percentage) as percentage'))
             ->groupBy('date', 'location_id')
             ->get();

        // Fetch vendor monthly summary
        $vendorMonthlySummary = DB::table('vendor_monthly_summary')
            ->select('vendor_name', 'total_planned_qty', 'total_actual_qty')
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('location_id', $locationId)
            ->get();


            $itemCodeQuantities = DB::table('inventories')
            ->select('_id', 'code', 'qty') // Ensure 'id' is selected
            ->where('location_id', $locationId)
            ->where('qty', '>', 0)
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

        // Fetch items not arrived
        $itemNotArrived = DB::table('items_not_arrived')
            ->whereMonth('planned_receiving_date', now()->month)
            ->whereYear('planned_receiving_date', now()->year)
            ->whereDate('planned_receiving_date', '<=', now()->toDateString())
            ->where('location_id', $locationId)
            ->orderBy('planned_receiving_date', 'desc') // Sort by newest data
            ->get();

        return view('home.l404', compact('vendorDataAggregate','locationId', 'itemCodes', 'plannedData', 'actualData', 'vendorData', 'itemCodeQuantities', 'vendors', 'totalPlanned', 'totalActual', 'itemNotArrived'));
    }

    public function test()
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

                    $vendorData = DB::table('vendor_comparison')
                    ->select(
                        'vendor_name',
                        'date',
                        DB::raw('SUM(total_actual_qty) as total_actual_qty'),
                        DB::raw('SUM(total_planned_qty) as total_planned_qty'),
                        DB::raw('AVG(percentage) as percentage') // Jika perlu rata-rata persentase
                    )
                    ->whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->whereIn('vendor_name', ['SENOPATI'])
                    ->where('location_id', $locationId)
                    ->groupBy('vendor_name', 'date')
                    ->get()
                    ->groupBy('vendor_name');

                // Fetch vendor monthly summary
                $vendorMonthlySummary = DB::table('vendor_monthly_summary')
                    ->select('vendor_name', 'total_planned_qty', 'total_actual_qty')
                    ->where('year', $currentYear)
                    ->where('month', $currentMonth)
                    ->where('location_id', $locationId)
                    ->get();

                    $itemCodeQuantities = DB::table('inventories')
                    ->select('_id', 'code', 'qty') // Ensure 'id' is selected
                    ->where('location_id', $locationId)
                    ->where('qty', '>', 0) // Exclude entries where qty is 0
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



                // Get comparisons for the current month until today
                $comparisons = InventoryComparison::whereMonth('planned_receiving_date', $currentMonth)
                ->whereYear('planned_receiving_date', $currentYear)
                ->whereDate('planned_receiving_date', '<=', $today)
                ->where('id_location', $locationId)
                ->get();

            // Group by item_code
            $itemCodes = $comparisons->groupBy('item_code');
            $plannedDataModel = DB::table('view_planning')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');

        $actualDataModel = DB::table('view_actual')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');

        $comparisonDataModel = DB::table('view_comparison')
            ->where('id_location', $locationId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy('model');


        return view('home.test', compact('comparisonDataModel','actualDataModel','plannedDataModel','locationId','itemCodes','plannedData', 'actualData', 'vendorData', 'itemCodeQuantities', 'vendors', 'totalPlanned', 'totalActual', 'variantCodeQuantities'));
    }

    public function detailCKD($date)
{
    // Construct the full date
    $currentYear = now()->year;
    $currentMonth = now()->month;
    $fullDate = $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . $date;

    $detailedData = DB::table('inventories as i')
    ->leftJoin('inventory_items as ii', function($join) use ($fullDate) {
        $join->on('ii.inventory_id', '=', 'i._id')
             ->whereDate('ii.receiving_date', '=', $fullDate)
             ->where('ii.vendor_name', '=', 'SENOPATI');
    })
    ->leftJoin('planned_inventory_items as pi', function($join) use ($fullDate) {
        $join->on('pi.inventory_id', '=', 'i._id')
             ->whereDate('pi.planned_receiving_date', '=', $fullDate)
             ->where('pi.vendor_name', '=', 'SENOPATI');
    })
    ->select(
        'i.code as item_code',
        'i.name as item_name',
        DB::raw('SUM(ii.qty) as qty_actual'),
        DB::raw('SUM(pi.planned_qty) as qty_plan')
    )
    ->where('i.location_id', '65a72c7fad782dc26a0626f6')
    ->where('i.name', '!=', 'Auto-generated') // Exclude items with name "Auto-generated"
    ->groupBy('i.code', 'i.name')
    ->havingRaw('SUM(ii.qty) > 0 OR SUM(pi.planned_qty) > 0')
    ->get();


    $sumactual = $detailedData->sum('qty_actual');
    $sumplaned = $detailedData->sum('qty_plan');

    // Return the data to a view
    return view('inventory.detailactivity', compact('detailedData', 'fullDate','sumactual','sumplaned'));
}





}

