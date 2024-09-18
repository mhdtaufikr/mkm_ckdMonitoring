@extends('layouts.master')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4"></div>
        </div>
    </header>
    <!-- Main page content-->
    <div class="container-fluid px-4 mt-n10">
        <div class="content-wrapper">
            <section class="content-header"></section>
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Delivery Note Details <strong>{{ $getHeader->delivery_note_number }}</strong></h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="">Customer PO Number</label>
                                            <p><strong>{{ $getHeader->customer_po_number }}</strong></p>
                                            <label for="">Order Number</label>
                                            <p><strong>{{ $getHeader->order_number }}</strong></p>
                                            <label for="">Customer Number</label>
                                            <p><strong>{{ $getHeader->customer_number }}</strong></p>
                                            <label for="">Driver Name</label>
                                            <p><strong>{{ $getHeader->driver_license }}</strong></p>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="">Destination</label>
                                            <p><strong>{{ $getHeader->destination }}</strong></p>
                                            <label for="">Date</label>
                                            <p><strong>{{ $getHeader->date }}</strong></p>
                                            <label for="">Plat No</label>
                                            <p><strong>{{ $getHeader->plat_no }}</strong></p>
                                            <label for="">Transportation</label>
                                            <p><strong>{{ $getHeader->transportation }}</strong></p>
                                        </div>
                                        <div class="col-md-4">
                                            {{-- Generate QR code with the delivery_note_number --}}
                                            <label for="">QR Code</label>
                                            <p>{!! QrCode::size(200)->generate($getHeader->delivery_note_number) !!}</p>
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Dynamic Table for Delivery Note Details -->
                                    <form action="{{ route('delivery-note-details.store') }}" method="POST" id="deliveryNoteForm">
                                        @csrf
                                        <input type="hidden" name="dn_id" value="{{ $getHeader->id }}">

                                        <!-- Manual Entry Table -->
                                        <div id="additionalPartsSection">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Part No</th>
                                                        <th>Part Name</th>
                                                        <th>Quantity</th>
                                                        <th>Remarks</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="manualEntryTable">
                                                    <tr>
                                                        <td>1</td>
                                                        <td><input type="text" name="manual_delivery_note_details[0][part_no]" class="form-control part_no_manual" /></td>
                                                        <td><input type="text" name="manual_delivery_note_details[0][part_name]" class="form-control part_name_manual" /></td>
                                                        <td><input type="number" name="manual_delivery_note_details[0][qty]" class="form-control qty_manual" /></td>
                                                        <td><input type="text" name="manual_delivery_note_details[0][remarks]" class="form-control remarks_manual" /></td>
                                                        <td>
                                                            <button type="button" id="addManual" class="btn btn-primary btn-sm">Add</button>
                                                            <button type="button" class="btn btn-danger btn-sm remove-manual-tr">Remove</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Submit Button -->
                                        <a href="{{ route('delivery-note.index') }}" class="btn btn-secondary btn-sm ">Back to List</a>
                                        <button type="submit" class="btn btn-success btn-sm">Save & Print</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<!-- JavaScript to Handle Form Validation and Table Logic -->
<script type="text/javascript">
   $(document).ready(function() {
    var j = 0;

    // Add row to Manual Entry Table
    $("#addManual").click(function() {
        ++j;
        $("#manualEntryTable").append('<tr><td>'+(j+1)+'</td>'+
            '<td><input type="text" name="manual_delivery_note_details['+j+'][part_no]" class="form-control part_no_manual" /></td>'+
            '<td><input type="text" name="manual_delivery_note_details['+j+'][part_name]" class="form-control part_name_manual" /></td>'+
            '<td><input type="number" name="manual_delivery_note_details['+j+'][qty]" class="form-control qty_manual" /></td>'+
            '<td><input type="text" name="manual_delivery_note_details['+j+'][remarks]" class="form-control remarks_manual" /></td>'+
            '<td><button type="button" class="btn btn-danger btn-sm remove-manual-tr">Remove</button></td></tr>');
    });

    // Remove row from Manual Entry Table
    $(document).on('click', '.remove-manual-tr', function() {
        $(this).parents('tr').remove();
    });

    $('#deliveryNoteForm').submit(function(event) {
        var isValid = true;
        // Add any additional validation if needed
    });
});

</script>

@endsection
