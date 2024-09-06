<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\PlannedInventoryItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlannedInventoryItemsImport;
use App\Exports\PlannedInventoryItemsExport;
use Carbon\Carbon;

class InventoryController extends Controller
{
    public function index()
{
    $locationIds = ['5fc4b12bc329204cb00b56bf', '5f335f29a2ef087afa109156','65bd1017b4490c26c00a82d9'];

    $items = DB::table('inventories')
        ->join('mst_locations', 'inventories.location_id', '=', 'mst_locations._id')
        ->whereIn('inventories.location_id', $locationIds)
        ->select('inventories.*', 'mst_locations.name as location_name')
        ->orderBy('inventories.created_at', 'desc')
        ->get();

    $inventoryCodes = DB::table('inventories')
        ->whereIn('location_id', $locationIds)
        ->select('code')
        ->distinct()
        ->get();

    // Fetch vendor names for each inventory item
    $vendorNames = DB::table('inventory_items')
        ->select('inventory_id', DB::raw('GROUP_CONCAT(DISTINCT vendor_name) as vendor_names'))
        ->groupBy('inventory_id')
        ->get()
        ->pluck('vendor_names', 'inventory_id'); // Pluck to get an associative array with inventory_id as key

    $plannedItems = DB::table('planned_inventory_items')
        ->whereIn('inventory_id', function($query) use ($locationIds) {
            $query->select('_id')
                  ->from('inventories')
                  ->whereIn('location_id', $locationIds);
        })
        ->get();

    return view('indexRaw.index', compact('items', 'inventoryCodes', 'plannedItems', 'vendorNames'));
}

public function indexCKD(Request $request)
{
    $locationIds = ['617bd0ad83ef510374337d84', '65a72c7fad782dc26a0626f6'];

    // Initial query for items
    $itemsQuery = DB::table('inventories')
        ->join('mst_locations', 'inventories.location_id', '=', 'mst_locations._id')
        ->whereIn('inventories.location_id', $locationIds)
        ->select('inventories.*', 'mst_locations.name as location_name')
        ->orderBy('inventories.created_at', 'desc');

    // Filter by planned date if provided
    if ($request->has('planned_date') && $request->planned_date) {
        $itemsQuery->whereIn('inventories._id', function ($query) use ($request) {
            $query->select('inventory_id')
                  ->from('planned_inventory_items')
                  ->whereDate('planned_receiving_date', $request->planned_date);
        });
    }

    // Execute the query
    $items = $itemsQuery->get();

    // Fetch all planned items for the given locations filtered by date
    $plannedItems = DB::table('planned_inventory_items')
        ->join('inventories', 'planned_inventory_items.inventory_id', '=', 'inventories._id')
        ->join('mst_locations', 'inventories.location_id', '=', 'mst_locations._id')
        ->whereIn('inventories.location_id', $locationIds)
        ->when($request->has('planned_date') && $request->planned_date, function ($query) use ($request) {
            return $query->whereDate('planned_receiving_date', $request->planned_date);
        })
        ->select(
            'planned_inventory_items.*',
            'inventories.code as product_code',
            'inventories.name as product_name',
            'mst_locations.name as location_name'
        )
        ->get();

    // Other data fetching (codes, vendors, etc.)
    $inventoryCodes = DB::table('inventories')
        ->whereIn('location_id', $locationIds)
        ->select('code')
        ->distinct()
        ->get();

    $vendorNames = DB::table('inventory_items')
        ->select('inventory_id', DB::raw('GROUP_CONCAT(DISTINCT vendor_name) as vendor_names'))
        ->groupBy('inventory_id')
        ->get()
        ->pluck('vendor_names', 'inventory_id');

    return view('inventory.index', compact('items', 'inventoryCodes', 'plannedItems', 'vendorNames'));
}


public function updatePlanned(Request $request)
{
    foreach ($request->planned_qty as $inventoryId => $qty) {
        DB::table('planned_inventory_items')
            ->where('_id', $inventoryId)
            ->update(['planned_qty' => $qty]);
    }

    return redirect()->route('inventory.ckd')->with('status', 'Planned quantities updated successfully!');
}


    public function show($id)
    {
        $inventory = Inventory::with(['plannedInventoryItems' => function($query) {
                $query->orderBy('created_at', 'desc');
            }, 'inventoryItems' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->findOrFail($id);
        return view('inventory.details', compact('inventory'));
    }

    public function uploadPlanned(Request $request)
{
    $request->validate([
        'excel-file' => 'required|mimes:xlsx,csv'
    ]);

    try {
        $file = $request->file('excel-file');
        Excel::import(new PlannedInventoryItemsImport, $file);

        return redirect()->route('inventory.index')->with('status', 'Planned receiving items uploaded successfully.');
    } catch (Exception $e) {
        return redirect()->route('inventory.index')->with('error', 'Failed to upload planned receiving items: ' . $e->getMessage());
    }
}




    public function downloadPlannedTemplate()
    {
        return Excel::download(new PlannedInventoryItemsExport, 'planned_receiving_items_format.xlsx');
    }

    public function comparison()
    {
        $comparisons = DB::table('inventory_comparison')->get();
        return view('inventory.comparison', compact('comparisons'));
    }

    public function updatePlannedReceive(Request $request)
{
    $inventoryId = $request->input('inventory_id');
    $plannedDates = $request->input('planned_dates');
    $plannedQtys = $request->input('planned_qtys');
    $vendorNames = $request->input('vendor_name');
    $statuses = $request->input('status');

    // Fetch existing planned receive items for this inventory
    $existingPlannedItems = PlannedInventoryItem::where('inventory_id', $inventoryId)->get();

    // Create a mapping of existing planned receive items by date
    $existingItemsMap = $existingPlannedItems->keyBy(function($item) {
        return Carbon::parse($item->planned_receiving_date)->format('Y-m-d');
    });

    // Delete existing planned receive items for this inventory
    PlannedInventoryItem::where('inventory_id', $inventoryId)->delete();

    // Check if there are planned dates provided in the request
    if ($plannedDates) {
        // Insert new planned receive items
        foreach ($plannedDates as $index => $date) {
            $existingItem = $existingItemsMap->get($date);

            PlannedInventoryItem::create([
                '_id' => uniqid(),
                'inventory_id' => $inventoryId,
                'planned_receiving_date' => $date,
                'planned_qty' => $plannedQtys[$index],
                'vendor_name' => $vendorNames[$index] ?? ($existingItem ? $existingItem->vendor_name : null),
                'status' => $statuses[$index] ?? ($existingItem ? $existingItem->status : 'Pending'), // Set a default status if not available
            ]);
        }
    }

    return redirect()->back()->with('status', 'Planned receive updated successfully.');
}

public function indexCNI(Request $request){
    $locationIds = ['6582ef8060c9390d890568d4'];

    // Initial query for items
    $itemsQuery = DB::table('inventories')
        ->join('mst_locations', 'inventories.location_id', '=', 'mst_locations._id')
        ->whereIn('inventories.location_id', $locationIds)
        ->select('inventories.*', 'mst_locations.name as location_name')
        ->orderBy('inventories.created_at', 'desc');

    // Filter by planned date if provided
    if ($request->has('planned_date') && $request->planned_date) {
        $itemsQuery->whereIn('inventories._id', function ($query) use ($request) {
            $query->select('inventory_id')
                  ->from('planned_inventory_items')
                  ->whereDate('planned_receiving_date', $request->planned_date);
        });
    }

    // Execute the query
    $items = $itemsQuery->get();

    // Fetch all planned items for the given locations filtered by date
    $plannedItems = DB::table('planned_inventory_items')
        ->join('inventories', 'planned_inventory_items.inventory_id', '=', 'inventories._id')
        ->join('mst_locations', 'inventories.location_id', '=', 'mst_locations._id')
        ->whereIn('inventories.location_id', $locationIds)
        ->when($request->has('planned_date') && $request->planned_date, function ($query) use ($request) {
            return $query->whereDate('planned_receiving_date', $request->planned_date);
        })
        ->select(
            'planned_inventory_items.*',
            'inventories.code as product_code',
            'inventories.name as product_name',
            'mst_locations.name as location_name'
        )
        ->get();

    // Other data fetching (codes, vendors, etc.)
    $inventoryCodes = DB::table('inventories')
        ->whereIn('location_id', $locationIds)
        ->select('code')
        ->distinct()
        ->get();

    $vendorNames = DB::table('inventory_items')
        ->select('inventory_id', DB::raw('GROUP_CONCAT(DISTINCT vendor_name) as vendor_names'))
        ->groupBy('inventory_id')
        ->get()
        ->pluck('vendor_names', 'inventory_id');

    return view('inventory.index', compact('items', 'inventoryCodes', 'plannedItems', 'vendorNames'));
}








}
