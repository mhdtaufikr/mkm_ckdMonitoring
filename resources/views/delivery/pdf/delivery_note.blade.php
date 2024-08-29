<?php
$qrCode = QrCode::size(100)->margin(6)->generate("$deliveryNote->delivery_note_number");
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            text-align: center;
        }
        .header img {
            width: 100px; /* Adjust the width to make it smaller */
            height: auto; /* Maintain aspect ratio */
            margin-right: 20px; /* Space between logo and text */
        }
        .title {
            text-align: center;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }
        .footer-table {
            width: 100%;
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
        }
        .footer-table td {
            width: 20%;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('assets/img/logomkm.png') }}" alt="Logo" >
        <div class="title">
            <h3>PT. MITSUBISHI KRAMA YUDHA MOTORS AND MANUFACTURING</h3>
            <p>AUTOMOTIVE COMPONENT MANUFACTURER</p>
        </div>
    </div>
    <table width="100%">
        <tr>
            <td><strong>Delivery Note Number:</strong> {{ $deliveryNote->delivery_note_number }}</td>
            <td><strong>Customer PO Number:</strong> {{ $deliveryNote->customer_po_number }}</td>
            <td style="text-align: center" rowspan="5">
                <img src="data:image/png;base64, {!! base64_encode($qrCode) !!}" alt="QR Code">
            </td>
        </tr>
        <tr>
            <td><strong>Order Number:</strong> {{ $deliveryNote->order_number }}</td>
            <td><strong>Customer Number:</strong> {{ $deliveryNote->customer_number }}</td>
        </tr>
        <tr>
            <td><strong>Driver License:</strong> {{ $deliveryNote->driver_license }}</td>
            <td><strong>Destination:</strong> {{ $deliveryNote->destination }}</td>
        </tr>
        <tr>
           <td><strong>Date:</strong>  {{ date('d M Y', strtotime($deliveryNote->date)) }} </td>
            <td><strong>Plat No:</strong> {{ $deliveryNote->plat_no }}</td>
        </tr>
        <tr>
            <td><strong>Transportation:</strong> {{ $deliveryNote->transportation }}</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    <br><br>

    <table class="table">
        <thead>
            <tr>
                <th>NO</th>
                <th>PART NO</th>
                <th>PART NAME</th>
                <th>GROUP NO</th>
                <th>DVL-NO</th>
                <th>QTY</th>
                <th>REMARKS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($deliveryNoteDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->part_no }}</td>
                    <td>{{ $detail->part_name }}</td>
                    <td>{{ $detail->group_no }}</td>
                    <td>{{ $detail->delivery_no }}</td>
                    <td>{{ $detail->qty }}</td>
                    <td>{{ $detail->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table  class="footer-table">
        <tr>
            <td>Delivery Approval</td>
            <td>Driver</td>
            <td>Received By</td>
            <td>Security</td>
            <td>Operator WH</td>
        </tr>
        <tr>
            <td colspan="5" style="padding-top: 50px;"></td>
        </tr>
        <tr>
            <th>: _______________</th>
            <th>: _______________</th>
            <th>: _______________</th>
            <th>: _______________</th>
            <th>: _______________</th>
        </tr>
    </table>
</body>
</html>
