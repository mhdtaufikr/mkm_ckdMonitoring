<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterProduct;

class ProductController extends Controller
{
    public function index()
    {
        $items = MasterProduct::get();
        return view('product.index', compact('items'));
    }

}
