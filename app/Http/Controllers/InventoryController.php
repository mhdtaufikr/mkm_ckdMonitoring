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
        $items = DB::table('inventories')
            ->join('mst_locations', 'inventories.location_id', '=', 'mst_locations._id')
            ->select('inventories.*', 'mst_locations.name as location_name')
            ->orderBy('inventories.created_at', 'desc')
            ->get();

        $inventoryCodes = DB::table('inventories')
            ->select('code')
            ->distinct()
            ->get();

        // Fetch vendor names for each inventory item
        $vendorNames = DB::table('inventory_items')
            ->select('inventory_id', DB::raw('GROUP_CONCAT(DISTINCT vendor_name) as vendor_names'))
            ->groupBy('inventory_id')
            ->get()
            ->pluck('vendor_names', 'inventory_id'); // Pluck to get an associative array with inventory_id as key

        $plannedItems = DB::table('planned_inventory_items')->get();

        return view('inventory.index', compact('items', 'inventoryCodes', 'plannedItems', 'vendorNames'));
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

        $file = $request->file('excel-file');
        Excel::import(new PlannedInventoryItemsImport, $file);

        return redirect()->route('inventory.index')->with('status', 'Planned receiving items uploaded successfully.');
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

    return redirect()->route('inventory.index')->with('status', 'Planned receive updated successfully.');
}






}
