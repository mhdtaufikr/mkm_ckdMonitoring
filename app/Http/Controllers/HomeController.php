<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pallet;
use App\Models\Commoninformation;

class HomeController extends Controller
{
    public function index()
    {
        return view('home.index');

    }
}
