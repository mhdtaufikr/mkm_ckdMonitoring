<?php

namespace App\Http\Controllers;
use App\Models\DeliveryNote;
use App\Models\MstLocation;
use App\Models\DeliveryNoteDetail;
use Illuminate\Support\Facades\Http;
use PDF; // Ensure you import the PDF facade
use Yajra\DataTables\Facades\DataTables;
use App\Models\DeliveryNoteJourney;
use Auth;
use DB;
use carbon\Carbon;

use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    public function ckdStamping(Request $request)
{
    if ($request->ajax()) {
        $data = DeliveryNote::query()->orderBy('created_at', 'desc');

        return DataTables::of($data)
            ->addColumn('actions', function ($row) {
                return view('partials.actions', compact('row'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('delivery.ckdStamping.index');
}

    public function getLocations(Request $request)
{
    $search = $request->get('search');

    // Debugging: Check if the search term is received correctly
    \Log::info('Search term received: ' . $search);

    // Fetch locations based on the search query
    $locations = MstLocation::where('name', 'like', '%' . $search . '%')->get();

    \Log::info('Locations fetched: ', $locations->toArray()); // Log the fetched locations

    // Format locations for Select2
    $formattedLocations = [];
    foreach ($locations as $location) {
        $formattedLocations[] = [
            'id' => $location->_id,  // The ID that will be stored
            'text' => strtoupper($location->name)  // The text that will be displayed
        ];
    }

    return response()->json($formattedLocations);
}


    public function ckdStampingStore(Request $request)
    {

        // Validate incoming request data
        $request->validate([
            'driver_license' => 'required|string|max:50',
            'destination' => 'required|string|max:255', // Validate destination as a string, but it is an ID
            'date' => 'required|date',
            'plat_no' => 'required|string|max:50',
            'transportation' => 'required|string|max:100',
        ]);

        // Get the location details using the ID provided in the 'destination'
        $location = MstLocation::find($request->destination); // Here, 'destination' contains the location ID

        if (!$location) {
            // If the location is not found, return an error
            return back()->withErrors(['destination' => 'Invalid destination selected'])->withInput();
        }

        // Generate delivery_note_number using destination code, date, and time
        $dateFormatted = \Carbon\Carbon::parse($request->date)->format('Ymd'); // Format date as YYYYMMDD
        $timeFormatted = \Carbon\Carbon::now()->format('His'); // Format time as HHMMSS (hours, minutes, seconds)
        $destinationCode = strtoupper(substr($location->code, 0, 3)); // Take the first 3 letters of the location code
        $deliveryNoteNumber = $destinationCode . '-' . $dateFormatted . '-' . $timeFormatted; // Combine to make unique

        // Store the data into the delivery_notes table
        $deliveryNote = new DeliveryNote();
        $deliveryNote->delivery_note_number = $deliveryNoteNumber;
        $deliveryNote->customer_po_number = $request->customer_po_number;
        $deliveryNote->order_number = $request->order_number;
        $deliveryNote->customer_number = $request->customer_number;
        $deliveryNote->driver_license = $request->driver_license;
        $deliveryNote->destination = $location->name; // Store the code from the location
        $deliveryNote->date = $request->date;
        $deliveryNote->plat_no = $request->plat_no;
        $deliveryNote->transportation = $request->transportation;
        $deliveryNote->save();

        // Redirect to the create page with the newly created delivery note's ID
        return redirect()->route('delivery-note.create', ['id' => encrypt($deliveryNote->id)])
                        ->with('status', 'Delivery note added successfully!');
    }




    public function ckdStampingCreate($id)
    {
        set_time_limit(300); // Menambah batas waktu eksekusi menjadi 5 menit
        $id = decrypt($id);
        $getHeader = DeliveryNote::where('id', $id)->first();

        // Optimize location query by using ID instead of name
        $location = MstLocation::where('name', $getHeader->destination)->first();

        // Set up date parameters
        $targetDate = $getHeader->date;
        $startDate = Carbon::parse($targetDate)->startOfDay()->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($targetDate)->endOfDay()->format('Y-m-d H:i:s');

        $accumulatedItems = collect();
        $page = 1;
        $stop = false;

        do {
            $response = Http::withHeaders([
                'x-api-key' => '315f9f6eb55fd6db9f87c0c0862007e0615ea467'
            ])->get('https://api.mile.app/public/v1/warehouse/inventory-item', [
                'location_id' => $location->_id,
                'limit' => 10,
                'page' => $page,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'serial_number' => '',
                'rack' => '',
                'rack_type' => ''
            ]);

            if (!$response->successful()) break;

            $data = $response->json();
            $currentItems = collect($data['data']);

            foreach ($currentItems as $item) {
                $itemDate = Carbon::parse($item['updated_at'])->toDateString();

                if ($itemDate === $targetDate) {
                    $accumulatedItems->push($item);
                } else {
                    // Found older item - stop pagination
                    $stop = true;
                    break;
                }
            }

            // Check if we should continue
            if ($stop || !isset($data['next_page_url'])) break;
            $page++;

        } while (true);

        // Process the accumulated items
        $processedItems = $accumulatedItems->map(function ($item) {
            // Optimized regex pattern
            preg_match('/([A-Z]{3}-\d{2,3})(?=-\d+$)/', $item['serial_number'], $matches);
            $result = $matches[1] ?? 'Unknown';

            return array_merge($item, [
                'lot_no' => $item['lot_no'] ?? $result,
                'extracted_result' => $result
            ]);
        })->groupBy(fn($item) => $item['code'].'-'.$item['extracted_result'])
          ->map(function ($group) {
              $first = $group->first();
              $first['qty'] = $group->sum('qty');
              $first['unique_id'] = uniqid();
              return $first;
          })->values();

        return view('delivery.ckdStamping.detail', [
            'getHeader' => $getHeader,
            'accumulatedItems' => $processedItems
        ]);
    }



public function ckdStampingSubmit(Request $request)
{

    // Retrieve the Delivery Note ID
    $dn_id = $request->input('dn_id');

    // Loop through each delivery note detail and save it to the database
    if ($request->has('delivery_note_details')) {
        foreach ($request->input('delivery_note_details') as $detail) {
            DeliveryNoteDetail::create([
                'dn_id' => $dn_id,
                'part_no' => $detail['part_no'],
                'part_name' => $detail['part_name'],
                'group_no' => $detail['lot_no'] ?? null, // Use lot_no as group_no if it exists
                'qty' => $detail['qty'],
                'remarks' => $detail['remarks'],
            ]);
        }
    }

    // Loop through each manual delivery note detail and save it to the database
    if ($request->has('manual_delivery_note_details')) {
        foreach ($request->input('manual_delivery_note_details') as $manualDetail) {
            if (is_null($manualDetail['part_no']) && is_null($manualDetail['part_name']) && is_null($manualDetail['qty']) && is_null($manualDetail['remarks'])) {
                continue; // Skip this iteration
            }

            DeliveryNoteDetail::create([
                'dn_id' => $dn_id,
                'part_no' => $manualDetail['part_no'],
                'part_name' => $manualDetail['part_name'],
                'qty' => $manualDetail['qty'],
                'remarks' => $manualDetail['remarks'],
            ]);
        }
    }

    // Redirect to the intermediate page that will handle the download and redirect
    return redirect()->route('delivery-note.trigger-download', ['id' => encrypt($dn_id)]);
}



public function ckdStampingTriggerDownload($id)
{
    $id = decrypt($id);

    // Fetch the delivery note and its details
    $deliveryNote = DeliveryNote::find($id);

    return view('delivery.trigger_download', compact('deliveryNote'));
}




        public function ckdStampingPDF($id)
        {
            $id = decrypt($id);

            // Fetch the delivery note and its details from the database
            $deliveryNote = DeliveryNote::find($id);
            $deliveryNoteDetails = DeliveryNoteDetail::where('dn_id', $id)->get();

            // Load the view and pass data to it
            $pdf = PDF::loadView('delivery.pdf.delivery_note_matrix', compact('deliveryNote', 'deliveryNoteDetails'))
                    ->setPaper([0, 0, 680, 792]); // Set paper size to 24 cm wide (680 points) x 28 cm long (792 points) in portrait orientation

            // Generate and return the PDF
            return $pdf->stream('DeliveryNote_' . $deliveryNote->delivery_note_number . '.pdf');
        }


        public function manual()
    {
        // You no longer need to fetch all locations here
        $item = DeliveryNote::get();

        return view('delivery.index', compact('item'));
    }


    public function manualStore(Request $request)
    {

        // Validate incoming request data
        $request->validate([
            'driver_license' => 'required|string|max:50',
            'destination' => 'required|string|max:255', // Validate destination as a string, but it is an ID
            'date' => 'required|date',
            'plat_no' => 'required|string|max:50',
            'transportation' => 'required|string|max:100',
        ]);





        // Generate delivery_note_number using destination code, date, and time
        $dateFormatted = \Carbon\Carbon::parse($request->date)->format('Ymd'); // Format date as YYYYMMDD
        $timeFormatted = \Carbon\Carbon::now()->format('His'); // Format time as HHMMSS (hours, minutes, seconds)
        $destinationCode = strtoupper(substr($request->destination, 0, 3)); // Take the first 3 letters of the location code
        $deliveryNoteNumber = $destinationCode . '-' . $dateFormatted . '-' . $timeFormatted; // Combine to make unique

        // Store the data into the delivery_notes table
        $deliveryNote = new DeliveryNote();
        $deliveryNote->delivery_note_number = $deliveryNoteNumber;
        $deliveryNote->customer_po_number = $request->customer_po_number;
        $deliveryNote->order_number = $request->order_number;
        $deliveryNote->customer_number = $request->customer_number;
        $deliveryNote->driver_license = $request->driver_license;
        $deliveryNote->destination = $request->destination; // Store the code from the location
        $deliveryNote->date = $request->date;
        $deliveryNote->plat_no = $request->plat_no;
        $deliveryNote->transportation = $request->transportation;
        $deliveryNote->save();

        // Redirect to the create page with the newly created delivery note's ID
        return redirect()->route('delivery-note.create.manual', ['id' => encrypt($deliveryNote->id)])
                        ->with('status', 'Delivery note added successfully!');
    }

    public function manualCreate($id)
    {
        $id = decrypt($id);
        $getHeader = DeliveryNote::where('id', $id)->first();


        return view('delivery.detail', compact('getHeader'));
    }

    // DeliveryNoteController.php

    public function show($id)
{
    try {
        // Decrypt the ID
        $id = decrypt($id);
    } catch (DecryptException $e) {
        return redirect()->route('delivery-note.index')->with('error', 'Invalid ID.');
    }

    // Fetch the delivery note header and details
    $getHeader = DeliveryNote::find($id);
    $getDetails = DeliveryNoteDetail::where('dn_id', $id)->get();

    // Check if the delivery note exists
    if (!$getHeader) {
        return redirect()->route('delivery-note.index')->with('error', 'Delivery Note not found.');
    }

    // Pass the data to the view
    return view('delivery.ckdStamping.view', compact('getHeader', 'getDetails'));
}

public function destroy($id)
{
    try {
        // Find the delivery note by ID and delete it
        $deliveryNote = DeliveryNote::findOrFail($id);
        $deliveryNote->delete();

        return redirect()->route('delivery-note.index')->with('status', 'Delivery note deleted successfully.');
    } catch (ModelNotFoundException $e) {
        return redirect()->route('delivery-note.index')->with('failed', 'Delivery note not found.');
    }
}

public function scan()
{
    return view('delivery.scan');
}
public function action(Request $request)
{


    // Find the DeliveryNote by delivery_note_number
    $getDeliveryNote = DeliveryNote::where('delivery_note_number', $request->delivery_note)->first();

    // Check if the DeliveryNote exists
    if (!$getDeliveryNote) {
        return redirect()->back()->with('error', 'Delivery Note not found.');
    }

    // Redirect to the actionID route with the DeliveryNote ID
    return redirect()->route('delivery.action', ['id' => $getDeliveryNote->id]);
}


public function actionID($id)
{
    $getDeliveryNote = DeliveryNote::where('id', $id)->first();
     // Fetch the delivery note header and details
     $getHeader = DeliveryNote::find($id);
     $getDetails = DeliveryNoteDetail::where('dn_id', $id)->get();

     // Check if the delivery note exists
     if (!$getHeader) {
         return redirect()->route('delivery-note.index')->with('error', 'Delivery Note not found.');
     }
     $getJournal = DeliveryNoteJourney::where('delivery_note_id', $id)->get();
     // Pass the data to the view
     return view('delivery.scan.index', compact('getHeader', 'getDetails','getJournal','getDeliveryNote'));

}
public function approve(Request $request, $id)
{
    $request->validate([
        'signature' => 'required',
    ]);

    // Start a database transaction
    DB::beginTransaction();

    try {
        // Retrieve the DeliveryNote by ID
        $deliveryNote = DeliveryNote::find($id);

        if (!$deliveryNote) {
            return redirect()->back()->with('failed', 'Delivery Note not found.');
        }

        // Check current journey log for this delivery note
        $currentLog = DeliveryNoteJourney::where('delivery_note_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Check if the current user has already submitted a log for this status
        $authUser = auth()->user();
        $userLogExists = DeliveryNoteJourney::where('delivery_note_id', $id)
            ->where('status', $currentLog ? $currentLog->status : null)
            ->where('remarks', 'like', '%' . $authUser->name . '%')
            ->exists();

        if ($userLogExists) {
            return redirect()->back()->with('failed', 'You have already submitted this log.');
        }

        // Define the next status based on the authenticated user's role
        if ($authUser->name === 'warehouse') {
            // Ensure the journey starts with warehouse
            if (!$currentLog && $authUser->name !== 'warehouse') {
                return redirect()->back()->with('failed', 'The journey must start with "Item Dispatched" from the warehouse.');
            }

            $nextStatus = 'Item Dispatched';
            $nextRemarks = 'Dispatched from warehouse';
        } elseif ($authUser->name === 'security') {
            if (!$currentLog || $currentLog->status !== 'Item Dispatched') {
                return redirect()->back()->with('failed', 'The delivery must first be dispatched from the warehouse.');
            }

            $nextStatus = 'On Delivery';
            $nextRemarks = 'Security confirmed on delivery';
        } else {
            if (!$currentLog || $currentLog->status !== 'On Delivery') {
                return redirect()->back()->with('failed', 'The delivery must first be marked as on delivery.');
            }

            $nextStatus = 'Delivered';
            $nextRemarks = 'Delivered to recipient';
        }

        // Insert the new log
        DeliveryNoteJourney::create([
            'delivery_note_id' => $id,
            'status' => $nextStatus,
            'scanned_at' => now(),
            'location' => $request->input('location', null), // Optional location field
            'remarks' => $nextRemarks . ' by ' . $authUser->name,
            'signature' => $request->input('signature'),
        ]);

        // Commit the transaction
        DB::commit();

        return redirect()->back()->with('success', "Delivery status updated to '{$nextStatus}'.");
    } catch (\Exception $e) {
        // Rollback the transaction if an error occurs
        DB::rollBack();

        return redirect()->back()->with('failed', 'An error occurred: ' . $e->getMessage());
    }
}





}
