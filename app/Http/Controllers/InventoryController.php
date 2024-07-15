<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\PlannedInventoryItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlannedInventoryItemsImport;
use App\Exports\PlannedInventoryItemsExport;

class InventoryController extends Controller
{
    public function index()
    {
        $items = Inventory::with('plannedInventoryItems')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('inventory.index', compact('items'));
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
}
