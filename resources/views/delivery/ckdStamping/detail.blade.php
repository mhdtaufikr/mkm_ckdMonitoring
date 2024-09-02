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
                                            <label for="">Driver License</label>
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
<form action="{{ route('delivery-note-details.store') }}" method="POST">
    @csrf
    <input type="hidden" name="dn_id" value="{{ $getHeader->id }}">

    <!-- Existing Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#No</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Quantity</th>
                <th>Remarks</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="dynamicTable">
            <tr>
                <td>1</td>
                <td>
                    <select name="delivery_note_details[0][part_no]" class="form-control chosen-select part_no" required>
                        <option value="">Select Part No</option>
                        @foreach($accumulatedItems as $item)
                            <option value="{{ $item['product']['code'] }}"
                                data-name="{{ $item['product']['name'] }}"
                                data-qty="{{ $item['qty'] }}"
                                data-remarks="{{ $item['product']['default_unit'] }}">
                                {{ $item['product']['code'] }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="delivery_note_details[0][part_name]" class="form-control part_name" readonly /></td>
                <td><input type="number" name="delivery_note_details[0][qty]" class="form-control qty" readonly /></td>
                <td><input type="text" name="delivery_note_details[0][remarks]" class="form-control remarks" readonly /></td>
                <td>
                    <button type="button" name="add" id="add" class="btn btn-primary btn-sm">Add</button>
                    <button type="button" class="btn btn-danger btn-sm remove-tr">Remove</button>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Divider -->
    <hr>

    <!-- Manual Entry Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#No</th>
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
                <td><input type="text" name="manual_delivery_note_details[0][part_no]" class="form-control" /></td>
                <td><input type="text" name="manual_delivery_note_details[0][part_name]" class="form-control" /></td>
                <td><input type="number" name="manual_delivery_note_details[0][qty]" class="form-control" /></td>
                <td><input type="text" name="manual_delivery_note_details[0][remarks]" class="form-control" /></td>
                <td>
                    <button type="button" id="addManual" class="btn btn-primary btn-sm">Add</button>
                    <button type="button" class="btn btn-danger btn-sm remove-manual-tr">Remove</button></td>
            </tr>
        </tbody>
    </table>



    <!-- Submit Button -->
    <a href="{{ route('delivery-note.index') }}" class="btn btn-secondary btn-sm ">Back to List</a>
    <button type="submit" class="btn btn-success btn-sm">Save</button>
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

<!-- Add JavaScript for Manual Entry -->
<script type="text/javascript">
    $(document).ready(function() {
        var j = 0;

        // Add row to Manual Entry Table
        $("#addManual").click(function() {
            ++j;
            $("#manualEntryTable").append('<tr><td>'+(j+1)+'</td>'+
                '<td><input type="text" name="manual_delivery_note_details['+j+'][part_no]" class="form-control" /></td>'+
                '<td><input type="text" name="manual_delivery_note_details['+j+'][part_name]" class="form-control" /></td>'+
                '<td><input type="number" name="manual_delivery_note_details['+j+'][qty]" class="form-control" /></td>'+
                '<td><input type="text" name="manual_delivery_note_details['+j+'][remarks]" class="form-control" /></td>'+
                '<td><button type="button" class="btn btn-danger btn-sm remove-manual-tr">Remove</button></td></tr>');
        });

        // Remove row from Manual Entry Table
        $(document).on('click', '.remove-manual-tr', function() {
            $(this).parents('tr').remove();
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize Chosen
        $('.chosen-select').chosen({ width: "100%" });

        var i = 0;

        function updatePartNoOptions() {
            // Get all selected part numbers
            let selectedPartNos = [];
            $('.part_no').each(function() {
                if ($(this).val()) {
                    selectedPartNos.push($(this).val());
                }
            });

            // Update each select element's options
            $('.part_no').each(function() {
                var currentSelect = $(this);
                currentSelect.find('option').each(function() {
                    var optionValue = $(this).val();
                    if (optionValue && selectedPartNos.includes(optionValue)) {
                        if (currentSelect.val() === optionValue) {
                            $(this).prop('disabled', false); // Keep selected option enabled
                        } else {
                            $(this).prop('disabled', true); // Disable selected options in other selects
                        }
                    } else {
                        $(this).prop('disabled', false); // Enable all other options
                    }
                });
                currentSelect.trigger("chosen:updated"); // Update Chosen with the new disabled options
            });
        }

        $("#add").click(function() {
            ++i;
            $("#dynamicTable").append('<tr><td>'+(i+1)+'</td>'+
                '<td><select name="delivery_note_details['+i+'][part_no]" class="form-control chosen-select part_no" required>'+
                '<option value="">Select Part No</option>'+
                '@foreach($accumulatedItems as $item)'+
                '<option value="{{ $item['product']['code'] }}" data-name="{{ $item['product']['name'] }}" data-qty="{{ $item['qty'] }}" data-remarks="{{ $item['product']['default_unit'] }}">{{ $item['product']['code'] }}</option>'+
                '@endforeach'+
                '</select></td>'+
                '<td><input type="text" name="delivery_note_details['+i+'][part_name]" class="form-control part_name" readonly /></td>'+
                '<td><input type="number" name="delivery_note_details['+i+'][qty]" class="form-control qty" readonly /></td>'+
                '<td><input type="text" name="delivery_note_details['+i+'][remarks]" class="form-control remarks" readonly /></td>'+
                '<td><button type="button" class="btn btn-danger btn-sm remove-tr">Remove</button></td></tr>');

            // Reinitialize Chosen for the new element
            $('.chosen-select').chosen({ width: "100%" });

            updatePartNoOptions(); // Update options after adding a new row
        });

        $(document).on('click', '.remove-tr', function() {
            $(this).parents('tr').remove();
            updatePartNoOptions(); // Update options after removing a row
        });

        $(document).on('change', '.part_no', function() {
            var partNo = $(this).val();
            var row = $(this).closest('tr');
            var partName = $(this).find(':selected').data('name');
            var qty = $(this).find(':selected').data('qty');
            var remarks = $(this).find(':selected').data('remarks');

            row.find('.part_name').val(partName);
            row.find('.qty').val(qty);
            row.find('.remarks').val(remarks);

            updatePartNoOptions(); // Update options after changing selection
        });
    });
</script>

@endsection
