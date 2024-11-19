<?php

namespace App\Http\Controllers;
use App\Models\DeliveryNote;
use App\Models\MstLocation;
use App\Models\DeliveryNoteDetail;
use Illuminate\Support\Facades\Http;
use PDF; // Ensure you import the PDF facade

use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    public function ckdStamping()
    {
        // You no longer need to fetch all locations here
        $item = DeliveryNote::get();

        return view('delivery.ckdStamping.index', compact('item'));
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
    $id = decrypt($id);
    $getHeader = DeliveryNote::where('id', $id)->first();
    $location = MstLocation::where('name', $getHeader->destination)->first(); // Here, 'destination' contains the location ID
    // Fetch data from the API
    $response = Http::withHeaders([
        'x-api-key' => '315f9f6eb55fd6db9f87c0c0862007e0615ea467'
    ])->get('https://api.mile.app/public/v1/warehouse/inventory-item', [
        'location_id' => $location->_id,
        'limit' => -1,
        'page' => 1,
        'serial_number' => '',
        'rack' => '',
        'rack_type' => '',
        'start_date' => '',
        'end_date' => ''
    ]);

    // Check API response and filter data by date
    if ($response->successful()) {
        $inventoryItems = collect($response->json()['data']);

        // Filter inventory items to match the date in the delivery_notes table
        $filteredInventoryItems = $inventoryItems->filter(function ($item) use ($getHeader) {
            return \Carbon\Carbon::parse($item['updated_at'])->toDateString() === $getHeader->date;
        });

        // Accumulate quantities for items with the same product code
        $accumulatedItems = $filteredInventoryItems->groupBy('product.code')->map(function ($items, $code) {
            $firstItem = $items->first();
            $totalQty = $items->sum('qty'); // Sum quantities for the same product code
            $firstItem['qty'] = $totalQty;  // Update the quantity to the accumulated value

           // Extract "AAG" from the 'code', e.g., "ME412734-AAG"
            $codeParts = explode('-', $firstItem['code']);

            // Ensure that there's a part after the hyphen
            if (count($codeParts) >= 2) {
                $lotNo = $codeParts[1]; // "AAG" is the part after the hyphen
            } else {
                // Handle cases where the code doesn't have a hyphen
                $lotNo = 'Unknown'; // Set a default value if the code doesn't match the expected format
            }

            // Add 'lot_no' to the item
            $firstItem['lot_no'] = $lotNo; // Add 'lot_no' as "AAG"


            return $firstItem;
        })->values(); // Reset keys after accumulation
    } else {
        $accumulatedItems = collect(); // Empty collection if API fails
    }

    return view('delivery.ckdStamping.detail', compact('getHeader', 'accumulatedItems'));
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


}
