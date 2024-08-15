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
                                    <h3 class="card-title">Inventory Details for {{ $fullDate }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="detailsTable" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Item Code</th>
                                                    <th>Item Name</th>
                                                    <th>Quantity Planned ({{$sumplaned}})</th>
                                                    <th>Quantity Actual ({{$sumactual}})</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @foreach ($detailedData as $data)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $data->item_code }}</td>
                                                        <td>{{ $data->item_name }}</td>
                                                        <td>{{ $data->qty_plan ?? 0 }}</td>
                                                        <td>{{ $data->qty_actual ?? 0 }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="{{ url('/home/ckd') }}" class="btn btn-secondary btn-sm">Back to List</a>
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
        $('#detailsTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });
    });
</script>
@endsection
