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
                                    <h3 class="card-title">Inventory Details</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Product Code</label>
                                           <p> <strong>{{ $inventory->code }}</strong></p>
                                            <label for="">Name</label>
                                           <p> <strong>{{ $inventory->name }}</strong></p>

                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Quantity</label>
                                           <p> <strong>{{ $inventory->qty }}</strong></p>
                                            <label for="">Status</label>
                                           <p><strong>
                                                @if ($inventory->qty > 999)
                                                    <span class="badge bg-danger"><i class="fas fa-exclamation"></i></span>
                                                @elseif ($inventory->qty < 0)
                                                    <span class="badge bg-danger"><i class="fas fa-exclamation"></i></span>
                                                @else
                                                    <span class="badge bg-success"><i class="fas fa-exclamation"></i></span>
                                                @endif
                                            </strong></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mb-4">
                                        <h4>Planned Receivings:</h4>
                                        <div class="table-responsive">
                                            <table id="plannedTable" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Planned Receiving Date</th>
                                                        <th>Planned Quantity</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $no = 1;
                                                    @endphp
                                                    @foreach ($inventory->plannedInventoryItems as $plannedItem)
                                                        <tr>
                                                            <td>{{ $no++ }}</td>
                                                            <td>{{ $plannedItem->planned_receiving_date }}</td>
                                                            <td>{{ $plannedItem->planned_qty }}</td>
                                                            <td>{{ $plannedItem->status }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h4>Actual Receivings:</h4>
                                        <div class="table-responsive">
                                            <table id="actualTable" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Serial Number</th>
                                                        <th>Rack</th>
                                                        <th>Rack Type</th>
                                                        <th>Quantity</th>
                                                        <th>Status</th>
                                                        <th>Receiving Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $no = 1;
                                                    @endphp
                                                    @foreach ($inventory->inventoryItems as $receivedItem)
                                                        <tr>
                                                            <td>{{ $no++ }}</td>
                                                            <td>{{ $receivedItem->serial_number }}</td>
                                                            <td>{{ $receivedItem->rack }}</td>
                                                            <td>{{ $receivedItem->rack_type }}</td>
                                                            <td>{{ $receivedItem->qty }}</td>
                                                            <td>{{ $receivedItem->status }}</td>
                                                            <td>{{ $receivedItem->receiving_date }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
<!-- For Datatables -->
<script>
    $(document).ready(function() {
        $('#actualTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });
        $('#plannedTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });
    });
</script>
@endsection
