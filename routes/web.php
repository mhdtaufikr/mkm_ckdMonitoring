<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DeliveryNoteController;

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
    Route::match(['get', 'post'], '/home/ckd', [HomeController::class, 'indexCkd'])->name('checksheet.CKD');
    Route::get('/home/ckd/nouba', [HomeController::class, 'indexCkdNouba'])->name('checksheet.CKDNouba');
    Route::get('/home/l305', [HomeController::class, 'l305'])->name('checksheet.l305');
    Route::get('/home/cvcL404', [HomeController::class, 'cvcL404'])->name('checksheet.cvcL404');
    Route::get('/home/test', [HomeController::class, 'test']);
    Route::get('/details-page/{date}', [HomeController::class, 'detailCKD']);
    Route::get('/details-page/cni/{date}', [HomeController::class, 'detailsCKDCNI']);


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


    Route::get('/inventory/ckd', [InventoryController::class, 'indexCKD'])->name('inventory.ckd');
    Route::get('/inventory/cni', [InventoryController::class, 'indexCNI'])->name('inventory.cni');
    Route::post('/inventory/planned/update/bulk', [InventoryController::class, 'updatePlanned'])->name('update.bulk');
    Route::get('/inventory/raw-material', [InventoryController::class, 'index'])->name('inventory.index');;
    Route::get('/inventory/{id}/details', [InventoryController::class, 'show'])->name('inventory.details');
    Route::post('/inventory/planned/upload', [InventoryController::class, 'uploadPlanned'])->name('inventory.planned.upload');
    Route::post('/inventory/planned/upload/ckd', [InventoryController::class, 'uploadPlannedCKD'])->name('inventory.planned.upload.ckd');
    Route::get('/download/excel/format/planned', [InventoryController::class, 'downloadPlannedTemplate'])->name('inventory.planned.template');
    Route::post('/inventory/planned/update', [InventoryController::class, 'updatePlannedReceive'])->name('inventory.planned.update');

    //Master Product
    Route::get('/master/product', [ProductController::class, 'index']);


    //Delivery Note
    Route::get('/delivery/ckd/stamping', [DeliveryNoteController::class, 'ckdStamping'])->name('delivery-note.index');
    Route::post('/delivery-note/store', [DeliveryNoteController::class, 'ckdStampingStore']);
    Route::get('/delivery-note/create/{id}', [DeliveryNoteController::class, 'ckdStampingCreate'])->name('delivery-note.create');
    Route::post('/delivery-note-details/store', [DeliveryNoteController::class, 'ckdStampingSubmit'])->name('delivery-note-details.store');
    Route::get('/delivery-note/pdf/{id}', [DeliveryNoteController::class, 'ckdStampingPDF'])->name('delivery-note.pdf');
    Route::get('/get-locations', [DeliveryNoteController::class, 'getLocations'])->name('get-locations');
    Route::get('/delivery-note/trigger-download/{id}', [DeliveryNoteController::class, 'ckdStampingTriggerDownload'])->name('delivery-note.trigger-download');

    Route::get('/delivery/manual', [DeliveryNoteController::class, 'manual'])->name('delivery-note.index.manual');
    Route::post('/delivery-note/store/manual', [DeliveryNoteController::class, 'manualStore']);
    Route::get('/delivery-note/create/manual/{id}', [DeliveryNoteController::class, 'manualCreate'])->name('delivery-note.create.manual');
    Route::get('/delivery-note/detail/{id}', [DeliveryNoteController::class, 'show'])->name('delivery-note.detail');
    Route::delete('/delivery-note/{id}', [DeliveryNoteController::class, 'destroy'])->name('delivery-note.destroy');

});
