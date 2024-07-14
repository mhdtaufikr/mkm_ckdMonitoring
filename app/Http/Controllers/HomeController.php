<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryComparison;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        return view('home.index', compact('itemCodes', 'stockLevels'));
    }
}
