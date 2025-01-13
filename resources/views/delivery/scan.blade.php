@extends('layouts.master')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="tool"></i></div>
                           Scan Delivery Note
                        </h1>
                        <div class="page-header-subtitle">Scan Delivery Note</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main page content-->
    <div class="container-fluid px-4 mt-n10">
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <section class="content">
                <div class="row">
                    <div class="col-12">
                        <!-- Scan Delivery Note Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Scan Delivery Note</h3>
                            </div>
                            <div class="card-body">
                                @include('partials.alert')
                                <form action="{{ url('/delivery/action') }}" method="POST">
                                    @csrf


                                    <div class="d-flex justify-content-center">
                                        <div id="qr-reader" style="width:500px"></div>
                                        <div id="qr-reader-results"></div>
                                    </div>
                                    <div class="d-flex justify-content-center mt-3">
                                        <input readonly type="text" name="delivery_note" id="qr-value" class="form-control" placeholder="Scan Delivery Note">
                                    </div>

                                </div>
                                <div class="card-footer d-flex justify-content-center">
                                    <button id="submitBtn" type="submit" class="btn btn-primary">Submit</button>
                                </div>
                                </form>
                        </div>
                            <!-- End of Scan Delivery Note Card -->
                    </div>
            </section>
        </div>
    </div>
</main>


<!-- DataTables and QR Scanner Scripts -->


<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    function docReady(fn) {
        if (document.readyState === "complete" || document.readyState === "interactive") {
            setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    docReady(function () {
        var resultContainer = document.getElementById('qr-reader-results');
        var inputField = document.getElementById('qr-value');
        var lastResult, countResults = 0;

        function onScanSuccess(decodedText, decodedResult) {
            if (decodedText !== lastResult) {
                console.log(`Decoded text: ${decodedText}`);
                lastResult = decodedText;
                inputField.value = decodedText;
            }
        }

        var html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    });
</script>

@endsection
