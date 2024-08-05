<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Routes
// Route::get('/', [AuthController::class, 'login'])->name('login');
// Route::get('/auth/login', [AuthController::class, 'postLogin']);
// Route::get('/auth/callback', [AuthController::class, 'handleAzureCallback']);
// Route::get('/logout', [AuthController::class, 'logout']);

Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('/auth/login', [AuthController::class, 'postLogin']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::post('request/access', [AuthController::class, 'requestAccess']);


Route::middleware(['auth'])->group(function () {
    //Home Controller
    Route::get('/home', [HomeController::class, 'index'])->name('checksheet');
    Route::get('/home/ckd', [HomeController::class, 'indexCkd'])->name('checksheet.CKD');
    Route::get('/home/ckd/nouba', [HomeController::class, 'indexCkdNouba'])->name('checksheet.CKDNouba');
    Route::get('/home/l305', [HomeController::class, 'l305'])->name('checksheet.l305');
    Route::get('/home/cvcL404', [HomeController::class, 'cvcL404'])->name('checksheet.cvcL404');

    //Dropdown Controller
    Route::get('/dropdown', [DropdownController::class, 'index'])->middleware(['checkRole:IT']);
    Route::post('/dropdown/store', [DropdownController::class, 'store'])->middleware(['checkRole:IT']);
    Route::patch('/dropdown/update/{id}', [DropdownController::class, 'update'])->middleware(['checkRole:IT']);
    Route::delete('/dropdown/delete/{id}', [DropdownController::class, 'delete'])->middleware(['checkRole:IT']);

    //Rules Controller
    Route::get('/rule', [RulesController::class, 'index'])->middleware(['checkRole:IT']);
    Route::post('/rule/store', [RulesController::class, 'store'])->middleware(['checkRole:IT']);
    Route::patch('/rule/update/{id}', [RulesController::class, 'update'])->middleware(['checkRole:IT']);
    Route::delete('/rule/delete/{id}', [RulesController::class, 'delete'])->middleware(['checkRole:IT']);

    //User Controller
    Route::get('/user', [UserController::class, 'index'])->middleware(['checkRole:IT']);
    Route::post('/user/store', [UserController::class, 'store'])->middleware(['checkRole:IT']);
    Route::post('/user/store-partner', [UserController::class, 'storePartner'])->middleware(['checkRole:IT']);
    Route::patch('/user/update/{user}', [UserController::class, 'update'])->middleware(['checkRole:IT']);
    Route::get('/user/revoke/{user}', [UserController::class, 'revoke'])->middleware(['checkRole:IT']);
    Route::get('/user/access/{user}', [UserController::class, 'access'])->middleware(['checkRole:IT']);


    Route::get('/inventory/ckd', [InventoryController::class, 'indexCKD'])->name('inventory.index');
    Route::get('/inventory/raw-material', [InventoryController::class, 'index'])->name('inventory.index');;
    Route::get('/inventory/{id}/details', [InventoryController::class, 'show'])->name('inventory.details');
    Route::post('/inventory/planned/upload', [InventoryController::class, 'uploadPlanned'])->name('inventory.planned.upload');
    Route::get('/download/excel/format/planned', [InventoryController::class, 'downloadPlannedTemplate'])->name('inventory.planned.template');
    Route::post('/inventory/planned/update', [InventoryController::class, 'updatePlannedReceive'])->name('inventory.planned.update');

    //Master Product
    Route::get('/master/product', [ProductController::class, 'index']);

});
