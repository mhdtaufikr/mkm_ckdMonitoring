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
    $locations = MstLocation::where('name', 'like', '%' . $search . '%')->limit(10)->get();

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

            // Extract Lot No. from refNumber, e.g., "ABK-303" from "3821-KTB-PDM-VIII-2024-ABK-303-"
            $refNumberParts = explode('-', $firstItem['refNumber']);
            $lotNo = $refNumberParts[count($refNumberParts) - 3] . '-' . $refNumberParts[count($refNumberParts) - 2]; // Combine the last two parts to get "ABK-303"
            $firstItem['lot_no'] = $lotNo; // Add 'lot_no' to the item

            return $firstItem;
        })->values(); // Reset keys after accumulation
    } else {
        $accumulatedItems = collect(); // Empty collection if API fails
    }

    return view('delivery.ckdStamping.detail', compact('getHeader', 'accumulatedItems'));
}



public function ckdStampingSubmit(Request $request)
{
    // Validate the request data
    $request->validate([
        'dn_id' => 'required|exists:delivery_notes,id',
        'delivery_note_details.*.part_no' => 'required|string|max:50',
        'delivery_note_details.*.part_name' => 'required|string|max:255',
        'delivery_note_details.*.qty' => 'required|integer|min:1',
        'delivery_note_details.*.remarks' => 'nullable|string|max:255',
        'delivery_note_details.*.lot_no' => 'nullable|string|max:50',  // Added validation for lot_no
        'manual_delivery_note_details.*.part_no' => 'nullable|string|max:50',
        'manual_delivery_note_details.*.part_name' => 'nullable|string|max:255',
        'manual_delivery_note_details.*.qty' => 'nullable|integer|min:1',
        'manual_delivery_note_details.*.remarks' => 'nullable|string|max:255',
    ]);

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
            // Check if all fields are null, if so, skip this entry
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

    // Redirect back to the delivery note index with a success message
    return redirect()->route('delivery-note.index')->with('status', 'Delivery note details added successfully!');
}






    public function ckdStampingPDF($id){
        $id = decrypt($id);

        // Fetch the delivery note and its details from the database
        $deliveryNote = DeliveryNote::find($id);
        $deliveryNoteDetails = DeliveryNoteDetail::where('dn_id', $id)->get();

        // Load the view and pass data to it
        $pdf = PDF::loadView('delivery.pdf.delivery_note_matrix', compact('deliveryNote', 'deliveryNoteDetails'));

        // Generate and return the PDF
        return $pdf->download('DeliveryNote_' . $deliveryNote->delivery_note_number . '.pdf');
    }





}
