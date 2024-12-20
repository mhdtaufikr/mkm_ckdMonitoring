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
                                        <h3><strong>CKD Part</strong></h3>
                                        <!-- Existing Table -->
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#No</th>
                                                    <th>Part No</th>
                                                    <th>Part Name</th>
                                                    <th>Lot</th>
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
                                                                <option value="{{ $item['unique_id'] }}"
                                                                    data-name="{{ $item['product']['name'] }}"
                                                                    data-qty="{{ $item['qty'] }}"
                                                                    data-lotno="{{ $item['lot_no'] }}"
                                                                    data-remarks="{{ $item['product']['default_unit'] ?? 'pcs' }}">
                                                                    {{ $item['product']['code'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>


                                                    </td>
                                                    <td><input type="text" name="delivery_note_details[0][part_name]" class="form-control part_name" readonly /></td>
                                                    <td><input type="text" name="delivery_note_details[0][qty]" class="form-control lot_no" readonly /></td>
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
                                        <h3><strong>Additional Part</strong></h3>
                                        <!-- Checkbox to Enable/Disable Additional Parts -->
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="addAdditionalParts" />
                                            <label class="form-check-label" for="addAdditionalParts">
                                                Add Additional Parts?
                                            </label>
                                        </div>

                                        <!-- Manual Entry Table - Initially Hidden -->
                                        <div id="additionalPartsSection" style="display: none;">
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

<script type="text/javascript">
    $(document).ready(function() {
        let selectedUniqueIds = new Set(); // Track selected unique IDs
        var i = 0;

        // Initialize Chosen for the first row
        $('.chosen-select').chosen({ width: "100%" });

        // Function to update dropdown options dynamically
        function updatePartNoOptions() {
            selectedUniqueIds.clear(); // Reset the Set

            // Collect all selected unique IDs
            $(".part_no").each(function() {
                let uniqueId = $(this).val();
                if (uniqueId) {
                    selectedUniqueIds.add(uniqueId);
                }
            });

            // Disable already selected unique IDs in all dropdowns
            $(".part_no").each(function() {
                let currentSelect = $(this);
                currentSelect.find("option").each(function() {
                    let optionValue = $(this).val();
                    if (selectedUniqueIds.has(optionValue) && currentSelect.val() !== optionValue) {
                        $(this).prop("disabled", true); // Disable if already selected elsewhere
                    } else {
                        $(this).prop("disabled", false); // Enable otherwise
                    }
                });
                currentSelect.trigger("chosen:updated"); // Update Chosen UI
            });
        }

        // Add new row to the table
        $("#add").click(function() {
            ++i;
            $("#dynamicTable").append('<tr><td>' + (i + 1) + '</td>' +
                '<td><select name="delivery_note_details[' + i + '][part_no]" class="form-control chosen-select part_no" required>' +
                '<option value="">Select Part No</option>' +
                '@foreach($accumulatedItems as $item)' +
                '<option value="{{ $item["unique_id"] }}" ' +
                'data-name="{{ $item["product"]["name"] }}" ' +
                'data-qty="{{ $item["qty"] }}" ' +
                'data-lotno="{{ $item["lot_no"] }}" ' +
                'data-remarks="{{ $item["product"]["default_unit"] ?? "pcs" }}">' +
                '{{ $item["product"]["code"] }}</option>' +
                '@endforeach' +
                '</select></td>' +
                '<td><input type="text" name="delivery_note_details[' + i + '][part_name]" class="form-control part_name" readonly /></td>' +
                '<td><input type="text" name="delivery_note_details[' + i + '][lot_no]" class="form-control lot_no" readonly /></td>' +
                '<td><input type="number" name="delivery_note_details[' + i + '][qty]" class="form-control qty" readonly /></td>' +
                '<td><input type="text" name="delivery_note_details[' + i + '][remarks]" class="form-control remarks" readonly /></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm remove-tr">Remove</button></td></tr>');

            // Reinitialize Chosen for the new dropdown
            $('.chosen-select').chosen({ width: "100%" });

            updatePartNoOptions(); // Update options after adding a new row
        });

        // Handle row removal
        $(document).on('click', '.remove-tr', function() {
            $(this).closest("tr").remove();
            updatePartNoOptions(); // Recalculate options after row removal
        });

        // Handle dropdown change event
        $(document).on('change', '.part_no', function() {
            let selectedOption = $(this).find(":selected");
            let row = $(this).closest("tr");

            row.find(".part_name").val(selectedOption.data("name"));
            row.find(".qty").val(selectedOption.data("qty"));
            row.find(".lot_no").val(selectedOption.data("lotno"));
            row.find(".remarks").val(selectedOption.data("remarks"));

            updatePartNoOptions(); // Recalculate options after selection
        });

        // Ensure initial row dropdown updates
        updatePartNoOptions();

        // Handle Checkbox Logic for Additional Parts
        $('#addAdditionalParts').change(function() {
            if ($(this).is(':checked')) {
                $('#additionalPartsSection').show(); // Show the table
                $('#manualEntryTable .part_no_manual, #manualEntryTable .part_name_manual, #manualEntryTable .qty_manual, #manualEntryTable .remarks_manual').prop('required', true);
            } else {
                $('#additionalPartsSection').hide(); // Hide the table
                $('#manualEntryTable .part_no_manual, #manualEntryTable .part_name_manual, #manualEntryTable .qty_manual, #manualEntryTable .remarks_manual').prop('required', false);
            }
        });

        $('#deliveryNoteForm').submit(function(event) {
            var isValid = true;

            // Check if "Add Additional Parts?" checkbox is checked
            if ($('#addAdditionalParts').is(':checked')) {
                // If checkbox is checked, ensure all fields are filled
                $('#manualEntryTable .part_no_manual').each(function() {
                    if ($(this).val() != '') {
                        // If part number is filled, check if other fields in the same row are filled
                        var row = $(this).closest('tr');
                        row.find('.part_name_manual, .qty_manual, .remarks_manual').each(function() {
                            if ($(this).val() == '') {
                                isValid = false;
                            }
                        });
                    }
                });

                if (!isValid) {
                    event.preventDefault(); // Prevent form submission
                    alert('Please fill out all fields in the Additional Part section.'); // Show alert
                }
            }
        });
    });
</script>



@endsection
