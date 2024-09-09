@extends('layouts.master')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="tool"></i></div>
                            Inventory
                        </h1>
                    </div>
                    <div class="col-auto">
                        @if (request()->routeIs('inventory.cni'))
                        <form action="{{ route('inventory.cni') }}" method="GET" class="d-flex">
                            <input type="date" name="planned_date" class="form-control" value="{{ request('planned_date') }}">
                            <button type="submit" class="btn btn-primary ms-2">Filter by Date</button>
                        </form>
                    @else
                        <form action="{{ route('inventory.ckd') }}" method="GET" class="d-flex">
                            <input type="date" name="planned_date" class="form-control" value="{{ request('planned_date') }}">
                            <button type="submit" class="btn btn-primary ms-2">Filter by Date</button>
                        </form>
                    @endif

                    </div>
                    <div class="col-auto">
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadPlannedModal">
                            <i class="fas fa-file-excel"></i> Upload Planned Received Item
                        </button>
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
                    <div class="row ">
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
                                    <h3 class="card-title">List of Inventory</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3 col-sm-12">
                                            @if(request('planned_date'))
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkEditModal">
                                                <i class="fas fa-edit"></i> Bulk Edit Planned Quantities
                                            </button>
                                        @endif

                                    <!-- For Datatables -->

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
                                            <table id="tableInventory" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Product Code</th>
                                                        <th>Name</th>
                                                        <th>Location</th>
                                                        <th>Quantity</th>
                                                        <th>Vendor Name</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $no = 1;
                                                    @endphp
                                                    @foreach ($items as $data)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $data->code }}</td>
                                                        <td>{{ $data->name }}</td>
                                                        <td>{{ $data->location_name }}</td>
                                                        <td>{{ $data->qty }}</td>
                                                        <td>
                                                            @php
                                                                $vendorNamesList = $vendorNames[$data->_id] ?? '';
                                                            @endphp
                                                            {{ $vendorNamesList }}
                                                            </td>
                                                            <td>
                                                                @if ($data->qty > 999)
                                                                    <span class="badge bg-danger"><i class="fas fa-exclamation"></i></span>
                                                                @elseif ($data->qty < 0)
                                                                    <span class="badge bg-danger"><i class="fas fa-exclamation"></i></span>
                                                                @else
                                                                    <span class="badge bg-success"><i class="fas fa-exclamation"></i></span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        Actions
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ route('inventory.details', $data->_id) }}" title="Details">
                                                                                <i class="fas fa-info-circle" style="margin-right: 5px;"></i> Details
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editPlannedReceiveModal" data-id="{{ $data->_id }}" title="Edit Planned Receive">
                                                                                <i class="fas fa-edit" style="margin-right: 5px;"></i> Edit Planned Receive
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                </div>


                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <!-- Modal -->
                             <div class="modal fade" id="uploadPlannedModal" tabindex="-1" aria-labelledby="modal-add-label" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modal-add-label">Upload Planned Received Items</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ url('/inventory/planned/upload') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <input type="file" class="form-control" id="csvFile" name="excel-file" accept=".csv, .xlsx">
                                                    <p class="text-danger">*file must be .xlsx or .csv</p>
                                                </div>
                                                @error('excel-file')
                                                    <div class="alert alert-danger" role="alert">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                            <div class="modal-footer">
                                                <a href="{{ route('inventory.planned.template') }}" class="btn btn-link">
                                                    Download Excel Format
                                                </a>
                                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal -->
                            <!-- Modal for Editing Planned Receive -->
                            <div class="modal fade" id="editPlannedReceiveModal" tabindex="-1" aria-labelledby="editPlannedReceiveLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editPlannedReceiveLabel">Edit Planned Receive</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ url('/inventory/planned/update') }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <input type="hidden" id="inventoryId" name="inventory_id">
                                                <div class="mb-3">
                                                    <label for="inventoryCode" class="form-label">Inventory Code</label>
                                                    <select disabled class="form-select" id="inventoryCode" name="inventory_code" required>
                                                        @foreach ($items as $item)
                                                            <option value="{{ $item->code }}">{{ $item->code }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Planned Quantity</th>
                                                            <th>Vendor</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="plannedReceiveList">
                                                        <!-- Rows will be dynamically added here using JavaScript -->
                                                    </tbody>
                                                </table>
                                                <div class="mb-3">
                                                    <button type="button" class="btn btn-success" id="addPlannedReceive">Add New Planned Receive</button>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
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


<!-- End Modal for Bulk Editing Planned Quantities -->
<!-- For Datatables -->
<script>
    $(document).ready(function() {
        var table = $("#tableInventory").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });

        $('#editPlannedReceiveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var modal = $(this);

            // Fetch the planned receive data for the selected inventory item
            var plannedItems = @json($plannedItems).filter(item => item.inventory_id === id);
            var plannedReceiveList = modal.find('#plannedReceiveList');

            plannedReceiveList.empty(); // Clear existing rows

            plannedItems.forEach(function(item) {
                plannedReceiveList.append(`
                    <tr>
                        <td><input type="date" class="form-control" name="planned_dates[]" value="${item.planned_receiving_date.split(' ')[0]}" required></td>
                        <td><input type="number" class="form-control" name="planned_qtys[]" value="${item.planned_qty}" required></td>
                        <td><input type="text" class="form-control" name="vendor_name[]" value="${item.vendor_name}" required></td>
                        <td><input type="text" class="form-control" name="status[]" value="${item.status}" required></td>
                        <td><button type="button" class="btn btn-danger remove-planned-receive">Remove</button></td>
                    </tr>
                `);
            });

            $('#inventoryId').val(id);
            $('#inventoryCode').val(button.closest('tr').find('td:eq(1)').text());
        });

        $('#addPlannedReceive').on('click', function() {
            $('#plannedReceiveList').append(`
                <tr>
                    <td><input type="date" class="form-control" name="planned_dates[]" required></td>
                    <td><input type="number" class="form-control" name="planned_qtys[]" required></td>
                    <td><input type="text" class="form-control" name="vendor_name[]" required></td>
                    <td><input type="text" class="form-control" name="status[]" required></td>
                    <td><button type="button" class="btn btn-danger remove-planned-receive">Remove</button></td>
                </tr>
            `);
        });

        $(document).on('click', '.remove-planned-receive', function() {
            $(this).closest('tr').remove();
        });
    });
</script>
@endsection
