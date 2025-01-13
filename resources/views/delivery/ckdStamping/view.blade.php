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
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3>Part Journey</h3>
                                </div>
                                <div class="card-body">
                                    <div class="horizontal-timeline">
                                        <!-- Point 1: Barang Keluar -->
                                        <div class="timeline-item">
                                            <i class="fas fa-box-open timeline-icon"></i>
                                            <div class="timeline-content">
                                                <h5>Item Dispatched</h5>
                                                <p>The item has been dispatched from the warehouse.</p>
                                            </div>
                                        </div>
                                        <!-- Point 2: On Delivery -->
                                        <div class="timeline-item">
                                            <i class="fas fa-truck timeline-icon"></i>
                                            <div class="timeline-content">
                                                <h5>On Delivery</h5>
                                                <p>The item is currently in transit.</p>
                                            </div>
                                        </div>
                                        <!-- Point 3: Received -->
                                        <div class="timeline-item">
                                            <i class="fas fa-check-circle timeline-icon"></i>
                                            <div class="timeline-content">
                                                <h5>Delivered</h5>
                                                <p>The item has been received.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <style>
                                                        /* Timeline container for horizontal layout */
                                .horizontal-timeline {
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
                                    margin: 20px 0;
                                    position: relative;
                                    padding: 20px 0;
                                }

                                /* Line between points */
                                .horizontal-timeline::before {
                                    content: '';
                                    position: absolute;
                                    top: 50%; /* Align the line vertically */
                                    left: 10%; /* Adjust the starting position */
                                    right: 10%; /* Adjust the ending position */
                                    height: 2px; /* Adjust the thickness */
                                    background-color: #0d6efd; /* Line color */
                                    z-index: 0; /* Place it behind the timeline items */
                                }

                                /* Timeline items */
                                .timeline-item {
                                    position: relative;
                                    text-align: center;
                                    z-index: 2;
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    flex: 1;
                                    max-width: 150px; /* Limit width for each item */
                                    background: #fff; /* Ensure background hides the line behind */
                                }

                                /* Timeline icons */
                                .timeline-icon {
                                    font-size: 60px; /* Adjust icon size */
                                    color: #0d6efd;
                                    background-color: #fff;
                                    border: 3px solid #0d6efd; /* Thicker border */
                                    border-radius: 50%;
                                    padding: 10px; /* Add padding */
                                    margin-bottom: 10px;
                                    z-index: 3; /* Ensure it appears above the line */
                                }

                                /* Timeline content */
                                .timeline-content {
                                    margin-top: 10px;
                                    font-size: 14px; /* Adjust text size */
                                }

                            </style>

                        </div>
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

                                    <!-- Table for Delivery Note Details -->
                                    <h3><strong>CKD Parts</strong></h3>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#No</th>
                                                <th>Part No</th>
                                                <th>Part Name</th>
                                                <th>Lot</th>
                                                <th>Quantity</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($getDetails as $index => $detail)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $detail->part_no }}</td>
                                                    <td>{{ $detail->part_name }}</td>
                                                    <td>{{ $detail->group_no }}</td>
                                                    <td>{{ $detail->qty }}</td>
                                                    <td>{{ $detail->remarks }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <a href="{{ route('delivery-note.index') }}" class="btn btn-secondary btn-sm ">Back to List</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
@endsection
