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
                        <div class="col-sm-12">
                            <!--alert success -->
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

                            <!--alert success -->
                            <!--validasi form-->
                              @if (count($errors)>0)
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
                            <!--end validasi form-->
                          </div>
                        <div class="col-md-12 mb-4">

                            @php
                            // Initialize status classes for timeline items
                            $statusClasses = [
                                'Item Dispatched' => 'inactive', // Default to inactive
                                'On Delivery' => 'inactive',
                                'Delivered' => 'inactive',
                            ];

                            // Loop through the journal and mark items as active if they exist
                            foreach ($getJournal as $journal) {
                                if (isset($statusClasses[$journal->status])) {
                                    $statusClasses[$journal->status] = 'active'; // Mark as active
                                }
                            }
                        @endphp

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                   Part Journey</strong>
                                </h3>
                            </div>

                            <style>
                                .timeline-item {
                                    opacity: 0.3; /* Default inactive style */
                                }

                                .timeline-item.active {
                                    opacity: 1; /* Fully visible when active */
                                }

                                .timeline-icon {
                                    color: gray; /* Default inactive icon color */
                                }

                                .timeline-item.active .timeline-icon {
                                    color: #0d6efd; /* Active icon color */
                                }
                            </style>



                            <div class="card-body">
                                <div class="horizontal-timeline">
                                    <!-- Point 1: Item Dispatched -->
                                    <div class="timeline-item {{ $statusClasses['Item Dispatched'] }}">
                                        <i class="fas fa-box-open timeline-icon"></i>
                                        <div class="timeline-content">
                                            <h5>Item Dispatched</h5>
                                            <p>The item has been dispatched from the warehouse.</p>
                                        </div>
                                    </div>
                                    <!-- Point 2: On Delivery -->
                                    <div class="timeline-item {{ $statusClasses['On Delivery'] }}">
                                        <i class="fas fa-truck timeline-icon"></i>
                                        <div class="timeline-content">
                                            <h5>On Delivery</h5>
                                            <p>The item is currently in transit.</p>
                                        </div>
                                    </div>
                                    <!-- Point 3: Delivered -->
                                    <div class="timeline-item {{ $statusClasses['Delivered'] }}">
                                        <i class="fas fa-check-circle timeline-icon"></i>
                                        <div class="timeline-content">
                                            <h5>Delivered</h5>
                                            <p>The item has been received.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Signature Pad -->
                                <div class="mt-5">
                                    <h5>Signature</h5>
                                    <div class="border border-secondary rounded" id="signature-pad-container" style="width: 100%; height: 200px;">
                                        <canvas id="signatureCanvas" style="display: block;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-secondary mt-2" id="clearSignature">Clear</button>
                                    <form action="{{url('/delivery-note/approve/'.$getDeliveryNote->id)}}" method="POST" class="mt-3">
                                        @csrf
                                        <!-- Hidden input to store the signature -->
                                        <input type="hidden" id="signature" name="signature" />
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </form>
                                </div>
                            </div>

                        </div>

                        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log("Initializing Signature Pad...");

        const canvas = document.getElementById("signatureCanvas");
        const container = document.getElementById("signature-pad-container");
        const signaturePad = new SignaturePad(canvas);
        const clearButton = document.getElementById("clearSignature");
        const form = document.querySelector("form");

        // Function to resize the canvas dynamically
        function resizeCanvas() {
            console.log("Resizing canvas...");
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = container.offsetWidth * ratio;
            canvas.height = container.offsetHeight * ratio;

            const context = canvas.getContext("2d");
            if (!context) {
                console.error("Canvas context not found!");
                return;
            }
            context.scale(ratio, ratio); // Ensure scaling for high-DPI screens
            signaturePad.clear(); // Clear the pad after resizing

            // Debug: Check canvas dimensions
            console.log(`Canvas resized: width=${canvas.width}, height=${canvas.height}`);
        }

        // Resize the canvas initially and on window resize
        resizeCanvas();
        window.addEventListener("resize", resizeCanvas);

        // Clear button functionality
        if (clearButton) {
            clearButton.addEventListener("click", function () {
                console.log("Clearing Signature Pad...");
                signaturePad.clear();
            });
        } else {
            console.error("Clear button not found!");
        }

        // Save the signature data to the hidden input before form submission
        if (form) {
            form.addEventListener("submit", function (e) {
                console.log("Form submit event triggered.");

                if (signaturePad.isEmpty()) {
                    console.warn("Signature Pad is empty. Submission prevented.");
                    e.preventDefault();
                    alert("Please provide a signature.");
                    return false;
                }

                const signatureData = signaturePad.toDataURL("image/png"); // Convert signature to Base64
                console.log("Signature captured:", signatureData);

                document.getElementById("signature").value = signatureData; // Save Base64 string to hidden input
            });
        } else {
            console.error("Form not found!");
        }
    });
</script>


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

                            /* Inactive state styling (gray) */
                            .horizontal-timeline.inactive .timeline-icon {
                                color: gray;
                                border-color: gray;
                            }

                            .horizontal-timeline.inactive .timeline-content h5,
                            .horizontal-timeline.inactive .timeline-content p {
                                color: gray;
                            }

                            .horizontal-timeline.inactive::before {
                                background-color: gray; /* Make the line gray as well */
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
