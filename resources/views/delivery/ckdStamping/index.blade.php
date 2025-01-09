@extends('layouts.master')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i class="fas fa-database"></i></div>
                            Delivery Notes
                        </h1>
                    </div>
                    <div class="col-auto">
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Main page content-->
    <div class="container-fluid px-4 mt-n10">
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h1></h1>
                        </div>
                    </div>
                </div>
            </section>
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">List of Delivery Notes</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="mb-3 col-sm-12">
                                            <button type="button" class="btn btn-dark btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modal-add">
                                                Add Delivery Note <i class="fas fa-plus-square"></i>
                                            </button>
                                            <style>
                                                /* Mengubah warna label untuk input yang required */
                                                .form-group label[for]:after {

                                                    color: red;
                                                }

                                                /* Jika ingin field input required memiliki border berwarna merah saat belum terisi */
                                                input:required:invalid {
                                                    border-color: red;
                                                }
                                                select:required:invalid {
                                                    border-color: red;
                                                }
                                            </style>

                                            <!-- Modal -->
<div class="modal fade" id="modal-add" tabindex="-1" aria-labelledby="modal-add-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add-label">Add Delivery Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('/delivery-note/store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customer_po_number">Customer PO Number</label>
                                <input type="text" class="form-control" id="customer_po_number" name="customer_po_number" placeholder="Enter Customer PO Number">
                            </div>
                            <div class="form-group mb-3">
                                <label for="order_number">Order Number</label>
                                <input type="text" class="form-control" id="order_number" name="order_number" placeholder="Enter Order Number">
                            </div>
                            <div class="form-group mb-3">
                                <label for="plat_no">Plat No.</label>
                                <input type="text" class="form-control" id="plat_no" name="plat_no" placeholder="Enter Plat No." required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="transportation">Transportation</label>
                                <input type="text" class="form-control" id="transportation" name="transportation" placeholder="Enter Transportation" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customer_number">Customer Number</label>
                                <input type="text" class="form-control" id="customer_number" name="customer_number" placeholder="Enter Customer Number">
                            </div>
                            <div class="form-group mb-3">
                                <label for="driver_license">Driver Name</label>
                                <input type="text" class="form-control" id="driver_license" name="driver_license" placeholder="Enter Driver Name" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="destination">Destination</label>
                                <select class="form-control chosen-select" id="destination" name="destination" required style="width: 100%;">
                                    <option value="">Select a Destination</option> <!-- Placeholder option -->
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="date">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script for Chosen initialization and modal -->
<script>
    $(document).ready(function () {
        // Initialize Chosen when the modal is opened
        $('#modal-add').on('shown.bs.modal', function () {
            $('#destination').chosen({
                width: "100%",
                no_results_text: "No results found!",
                placeholder_text_single: "Select a Destination"
            });

            // Fetch data via AJAX and update the Chosen dropdown
            $.ajax({
                url: '{{ route('get-locations') }}',
                dataType: 'json',
                success: function (data) {
                    var $destination = $('#destination');
                    $destination.empty(); // Clear current options
                    $.each(data, function (key, value) {
                        $destination.append('<option value="' + value.id + '">' + value.text + '</option>');
                    });
                    $destination.trigger("chosen:updated"); // Update Chosen dropdown
                },
                error: function (xhr, status, error) {
                    console.error("Error during the AJAX request:", error);
                }
            });
        });
    });
</script>



                                            @if (session('status'))
                                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                    <strong>{{ session('status') }}</strong>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            @endif
                                            @if (session('failed'))
                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                    <strong>{{ session('failed') }}</strong>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            @endif
                                            @if (count($errors) > 0)
                                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    <ul>
                                                        <li><strong>Data Process Failed !</strong></li>
                                                        @foreach ($errors->all() as $error)
                                                            <li><strong>{{ $error }}</strong></li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table id="tableProduct" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Delivery Note Number</th>
                                                        <th>Customer PO Number</th>
                                                        <th>Order Number</th>
                                                        <th>Customer Number</th>
                                                        <th>Driver License</th>
                                                        <th>Destination</th>
                                                        <th>Date</th>
                                                        <th>Transportation</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>

                                            <script>
                                                $(document).ready(function() {
                                                    $("#tableProduct").DataTable({
                                                        processing: true,
                                                        serverSide: true,
                                                        responsive: true,
                                                        lengthChange: false,
                                                        autoWidth: false,
                                                        ajax: "{{ route('delivery-note.index') }}",
                                                        columns: [
                                                            { data: 'delivery_note_number', name: 'delivery_note_number' },
                                                            { data: 'customer_po_number', name: 'customer_po_number' },
                                                            { data: 'order_number', name: 'order_number' },
                                                            { data: 'customer_number', name: 'customer_number' },
                                                            { data: 'driver_license', name: 'driver_license' },
                                                            { data: 'destination', name: 'destination' },
                                                            { data: 'date', name: 'date' },
                                                            { data: 'transportation', name: 'transportation' },
                                                            { data: 'actions', name: 'actions', orderable: false, searchable: false },
                                                        ],
                                                    });
                                                });
                                            </script>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal -->
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

@endsection
