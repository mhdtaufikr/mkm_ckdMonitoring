<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryComparison;

class HomeController extends Controller
{
    public function index()
    {
        $comparisons = InventoryComparison::all();

        // Group by item_code
        $itemCodes = $comparisons->groupBy('item_code');

        return view('home.index', compact('itemCodes'));
    }
}
