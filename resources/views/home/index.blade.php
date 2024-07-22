@extends('layouts.master')

@section('content')
<style>
    #lblGreetings {
        font-size: 1rem; /* Adjust the base font size as needed */
    }

    @media only screen and (max-width: 600px) {
        #lblGreetings {
            font-size: 1rem; /* Adjust the font size for smaller screens */
        }
    }
    .page-header .page-header-content {
        padding-top: 0rem;
        padding-bottom: 1rem;
    }
    .chart-container {
        margin-top: 0px;
        position: relative;
        height: 275px; /* Adjust the height as needed */
        width: 100%;
    }
    .chart-custom {
        width: 100% !important;
        height: 100% !important; /* Let the canvas take the full height of the container */
    }
    .card-custom {
        height: 450px; /* Adjust the height as needed */
        width: 100%; /* Adjust the width as needed */
    }
    body {
        transform: scale(0.7);
        transform-origin: top left;
        width: 142.857%; /* 100 / 70 */
    }
    .nav-fixed #layoutSidenav #layoutSidenav_nav {
        width: 15rem;
        height: 250vh;
        z-index: 1038;
    }
    .indicator-table {
        width: 100%;
        margin-bottom: 10px;
        text-align: center;
        border-collapse: collapse;
    }
    .indicator-table th, .indicator-table td {
        border: 1px solid black;
        padding: 5px;
    }
    .signal {
        display: inline-block;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        line-height: 40px;
        text-align: center;
        color: white;
        font-weight: bold;
    }
    .green {
        background-color: green;
    }
    .yellow {
        background-color: yellow;
        color: black;
    }
    .red {
        background-color: red;
    }
</style>
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">
                        <h1 class="page-header-title">
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="content">
        <div class="container-fluid px-4 mt-n10">
            <div class="row">
                 <!-- Inventory Monitoring Carousel -->
                 <div class="col-md-6 mb-2">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>Inventory Monitoring</h4>
                        </div>
                        <div class="card-body">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($plannedData as $itemCode => $comparisons)
                                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner">
                                    @foreach ($plannedData as $itemCode => $comparisons)

                                    @php
                                    // Mengambil data dari database
                                    $comparisons = DB::table('inventory_comparison')
                                        ->where('id_location', $locationId)
                                        ->where('inventory_id', $comparisons[0]->inventory_id )
                                        ->get();

                                    // Inisialisasi variabel untuk menghitung total persentase dan jumlah entri
                                    $totalPercentage = 0;
                                    $count = 0;
                                    $today = now()->format('Y-m-d');

                                    // Loop melalui setiap entri dalam $comparisons
                                    foreach ($comparisons as $comparison) {
                                        // Memastikan bahwa hanya data hingga hari ini yang dihitung
                                        if ($comparison->receiving_date <= $today) {
                                            $totalPercentage += $comparison->percentage;
                                            $count++;
                                        }
                                    }
                                    // Menghitung rata-rata persentase
                                    $averagePercentage = ($count > 0) ? $totalPercentage / $count : 0;
                                @endphp

                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <table class="indicator-table mb-4">
                                                        <tr>
                                                            <th>Signal Indicator</th>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span class="signal green px-2">G</span> ≥ 95%
                                                                <span class="signal yellow">Y</span> ≥ 85%
                                                                <span class="signal red">R</span> < 85%
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-4">
                                                    <table class="indicator-table mb-4">
                                                        <tr>
                                                            <th>Average Inventory</th>
                                                            <th>Signal</th>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ number_format($averagePercentage, 2) }}%</td>
                                                            <td>
                                                                <span id="signal-inventory" class="signal
                                                                    {{ $averagePercentage >= 95 ? 'green' : ($averagePercentage >= 85 ? 'yellow' : 'red') }}">
                                                                    {{ $averagePercentage >= 95 ? 'G' : ($averagePercentage >= 85 ? 'Y' : 'R') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <p style="margin-top: -20px" class="text-center">{{ $itemCode }}</p>
                                            <div style="margin-top: -20px" class="chart-container">
                                                <canvas id="chart-{{ $itemCode }}" class="chart-custom"></canvas>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OTDC Chart Carousel -->
                <div class="col-md-6 mb-2">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>OTDC</h4>
                        </div>
                        <div class="card-body">


                            <div id="otdcCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($vendorData as $vendorName => $data)
                                        <button type="button" data-bs-target="#otdcCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner">
                                    @foreach ($vendorData as $vendorName => $data)
                                        @php
                                            $totalPercentage = 0;
                                            $count = 0;
                                            $today = now()->format('Y-m-d');
                                            foreach ($data as $entry) {
                                                if ($entry->date <= $today) {
                                                    $totalPercentage += $entry->percentage;
                                                    $count++;
                                                }
                                            }
                                            $averagePercentage = ($count > 0) ? $totalPercentage / $count : 0;
                                        @endphp
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <table class="indicator-table mb-4">
                                                        <tr>
                                                            <th>Signal Indicator</th>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span class="signal green px-2">G</span> ≥ 95%
                                                                <span class="signal yellow">Y</span> ≥ 85%
                                                                <span class="signal red">R</span> < 85%
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-4">
                                                    <table class="indicator-table mb-4">
                                                        <tr>
                                                            <th>Average OTDC</th>
                                                            <th>Signal</th>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ number_format($averagePercentage, 2) }}%</td>
                                                            <td>
                                                                <span id="signal-otdc" class="signal
                                                                    {{ $averagePercentage >= 95 ? 'green' : ($averagePercentage >= 85 ? 'yellow' : 'red') }}">
                                                                    {{ $averagePercentage >= 95 ? 'G' : ($averagePercentage >= 85 ? 'Y' : 'R') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <p style="margin-top: -20px" class="text-center">{{ $vendorName }}</p>
                                            <div style="margin-top: -20px" class="chart-container">
                                                <canvas id="otdc-chart-{{ $vendorName }}" class="chart-custom"></canvas>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#otdcCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#otdcCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- Vendor Monthly Summary Chart -->
                <div class="col-md-6 mb-2">
                    <div style="height: 375px" class="card card-custom">
                        <div class="card-header">
                            <h4>Vendor Monthly Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="vendorSummaryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item Code Quantity Carousel -->
                <div class="col-md-6 mb-2">
                    <div style="height: 375px" class="card card-custom">
                        <div class="card-header">
                            <h4>Item Code Quantities</h4>
                        </div>
                        <div class="card-body">
                            <div id="itemCodeQuantityCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($itemCodeQuantities as $groupIndex => $group)
                                        <button type="button" data-bs-target="#itemCodeQuantityCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div style="margin-top: -20px" class="carousel-inner">
                                    @foreach ($itemCodeQuantities as $groupIndex => $group)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <p class="text-center">Group {{ $groupIndex + 1 }}</p>
                                            <div class="chart-container">
                                                <canvas id="item-code-quantity-chart-{{ $groupIndex }}" class="chart-custom"></canvas>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#itemCodeQuantityCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#itemCodeQuantityCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header" data-bs-toggle="collapse" href="#collapseCard" role="button" aria-expanded="false" aria-controls="collapseCard">
                            <h3 class="card-title">List of Item Balance Item</h3>
                        </div>

                        <!-- /.card-header -->
                        <div id="collapseCard" class="collapse">
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-sm-12">
                                    </div>
                                    <div class="table-responsive">
                                        <table id="tableUser" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Item Code</th>
                                                    <th>Vendor Name</th>
                                                    <th>Planned Receiving Date</th>
                                                    <th>Planned Quantity</th>
                                                    <th>Received Quantity</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @foreach ($itemNotArrived as $notArrived)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $notArrived->item_code }}</td>
                                                        <td>{{ $notArrived->vendor_name }}</td>
                                                        <td>{{ date('d M Y', strtotime($notArrived->planned_receiving_date)) }}</td>
                                                        <td>{{ $notArrived->planned_qty }}</td>
                                                        <td>{{ $notArrived->received_qty }}</td>
                                                        <td>{{ $notArrived->balance }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>


            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
</main>
<script>
    $(document).ready(function() {
      var table = $("#tableUser").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        // "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
      });
    });
  </script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-trendline"></script>
<script>
document.addEventListener('DOMContentLoaded', (event) => {
    console.log('DOM fully loaded and parsed');

    const groupedComparisons = @json($itemCodes);
    const vendorData = @json($vendorData);
    const itemCodeQuantities = @json($itemCodeQuantities);
    const vendors = @json($vendors);
    const totalPlanned = @json($totalPlanned);
    const totalActual = @json($totalActual);
    const month = new Date().toLocaleString('default', { month: 'long' });
    const today = new Date().getDate(); // Get today's date
    const groupedPlannedData = @json($plannedData);
    const groupedActualData = @json($actualData);

    console.log('Grouped Comparisons:', groupedComparisons);
    console.log('Vendor Data:', vendorData);
    console.log('Item Code Quantities:', itemCodeQuantities);
    console.log('Vendors:', vendors);
    console.log('Total Planned:', totalPlanned);
    console.log('Total Actual:', totalActual);

    const getDefaultLabels = () => Array.from({ length: 31 }, (_, i) => (i + 1).toString());

    const addDottedLinePlugin = {
        id: 'dottedLinePlugin',
        beforeDraw: (chart) => {
            const ctx = chart.ctx;
            const yScale = chart.scales['y-axis-2'];
            const yValue = yScale.getPixelForValue(100);

            ctx.save();
            ctx.beginPath();
            ctx.setLineDash([5, 5]);
            ctx.moveTo(chart.chartArea.left, yValue);
            ctx.lineTo(chart.chartArea.right, yValue);
            ctx.lineWidth = 1;
            ctx.strokeStyle = 'black';
            ctx.stroke();
            ctx.restore();
        }
    };

    if (typeof groupedPlannedData === 'object' && typeof groupedActualData === 'object') {
        Object.keys(groupedPlannedData).forEach((itemCode) => {
            const plannedComparisons = groupedPlannedData[itemCode];
            const actualComparisons = groupedActualData[itemCode] || [];

            const plannedData = Array(31).fill(null);
            const actualData = Array(31).fill(null);
            const percentageDifference = Array(31).fill(0);
            const percentageTrendData = Array(today).fill(0); // Limited to today's date

            plannedComparisons.forEach((comparison) => {
                const plannedDay = new Date(comparison.planned_receiving_date).getDate() - 1;
                plannedData[plannedDay] = comparison.planned_qty;
            });

            actualComparisons.forEach((comparison) => {
                const actualDay = new Date(comparison.receiving_date).getDate() - 1;
                actualData[actualDay] = comparison.received_qty;
            });

            plannedData.forEach((value, index) => {
                if (value !== null && actualData[index] !== null) {
                    const percentage = Math.min((actualData[index] / value) * 100, 100);
                    percentageDifference[index] = isFinite(percentage) ? percentage : 0;
                    if (index < today) {
                        percentageTrendData[index] = percentageDifference[index];
                    }
                }
            });

            const ctx = document.getElementById(`chart-${itemCode}`).getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: getDefaultLabels(),
                    datasets: [{
                        label: 'Planned Stock',
                        data: plannedData,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 1
                    },
                    {
                        label: 'Actual Stock',
                        data: actualData,
                        backgroundColor: 'rgba(255, 159, 64, 0.8)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 2
                    },
                    {
                        label: 'Percentage Accuracy',
                        data: percentageDifference,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        type: 'line',
                        yAxisID: 'y-axis-2',
                        order: 0,
                        borderWidth: 2,

                    },
                    {
                        label: 'Trendline',
                        data: percentageTrendData,
                        borderColor: 'rgba(75, 192, 192, 1)', // Red trendline
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        type: 'line',
                        yAxisID: 'y-axis-2',
                        order: 3,
                        borderWidth: 2,
                        trendlineLinear: {
                            style: "rgba(75, 192, 192, 1)", // Change to red
                            lineStyle: "line",
                            width: 5
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: false,
                            categoryPercentage: 0.5,
                            barPercentage: 0.5,
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0,
                                minRotation: 0,
                                callback: function (value, index, values) {
                                    return (index + 1) % 4 === 0 || index === 0 ? (index + 1).toString() : '';
                                }
                            }
                        },
                        y: {
                            stacked: false,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Quantity'
                            }
                        },
                        'y-axis-2': {
                            stacked: false,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Percentage'
                            },
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function (tooltipItems) {
                                    let title = tooltipItems[0].label || '';
                                    title += ` ${month}`;
                                    return title;
                                },
                                label: function (context) {
                                    if (context.dataset.label === 'Percentage Accuracy') {
                                        return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) + '%' : '';
                                    }
                                    return context.raw;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                filter: function (item, chart) {
                                    return item.text !== 'Percentage Accuracy';
                                }
                            }
                        }
                    }
                },
                plugins: [addDottedLinePlugin]
            });
        });
    }

    if (typeof vendorData === 'object') {
        Object.keys(vendorData).forEach((vendorName) => {
            console.log(`Processing vendor: ${vendorName}`);

            const data = vendorData[vendorName];
            const plannedData = Array(31).fill(null);
            const actualData = Array(31).fill(null);
            const percentageAccuracy = Array(31).fill(0); // Default value set to 0%
            const percentageTrendData = Array(today).fill(0); // Limited to today's date

            let totalPercentage = 0;
            let count = 0;

            data.forEach((entry) => {
                const day = new Date(entry.date).getDate() - 1;
                plannedData[day] = entry.total_planned_qty;
                actualData[day] = entry.total_actual_qty;

                if (day < today) { // Ensure calculations are done only up to today's date
                    const percentage = entry.total_planned_qty > 0 ? Math.min((entry.total_actual_qty / entry.total_planned_qty) * 100, 100) : 0; // Ensure percentage does not exceed 100%
                    percentageAccuracy[day] = isFinite(percentage) ? percentage : 0;

                    totalPercentage += percentageAccuracy[day];
                    count++;

                    percentageTrendData[day] = percentageAccuracy[day]; // Add to trend data
                }
            });

            const averagePercentage = count > 0 ? totalPercentage / count : 0;

            console.log(`Planned Data for ${vendorName}:`, plannedData.slice(0, today));
            console.log(`Actual Data for ${vendorName}:`, actualData.slice(0, today));
            console.log(`Percentage Accuracy for ${vendorName}:`, percentageAccuracy.slice(0, today));
            console.log(`Average OTDC for ${vendorName}: ${averagePercentage.toFixed(2)}%`);

            createChart(vendorName, plannedData, actualData, percentageAccuracy, percentageTrendData, 31);
        });
    }

    function createChart(vendorName, plannedData, actualData, percentageAccuracy, percentageTrendData, endDate) {
        const ctx = document.getElementById(`otdc-chart-${vendorName}`).getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Array.from({ length: endDate }, (_, i) => i + 1),
                datasets: [
                    {
                        label: 'Planned Qty',
                        data: plannedData.slice(0, endDate),
                        backgroundColor: 'rgba(54, 162, 235, 0.8)', // Increase opacity
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 1
                    },
                    {
                        label: 'Actual Qty',
                        data: actualData.slice(0, endDate),
                        backgroundColor: 'rgba(255, 159, 64, 0.8)', // Increase opacity
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 2
                    },
                    {
                        label: 'Percentage Accuracy',
                        data: percentageAccuracy.slice(0, endDate).map(value => isFinite(value) ? value : 0),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        type: 'line',
                        yAxisID: 'y-axis-2',
                        order: 0,
                        borderWidth: 2,

                    },
                    {
                        label: 'Trendline',
                        data: percentageTrendData,
                        borderColor: 'rgba(75, 192, 192, 1)', // Red trendline
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        type: 'line',
                        yAxisID: 'y-axis-2',
                        order: 3,
                        borderWidth: 2,
                        trendlineLinear: {
                            style: "rgba(75, 192, 192, 1)", // Change to red
                            lineStyle: "line",
                            width: 5
                        }
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: false,
                        categoryPercentage: 0.5,
                        barPercentage: 0.5,
                        ticks: {
                            autoSkip: false,
                            maxRotation: 0,
                            minRotation: 0,
                            callback: function (value, index, values) {
                                return (index + 1) % 4 === 0 || index === 0 ? (index + 1).toString() : '';
                            }
                        }
                    },
                    y: {
                        stacked: false,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    },
                    'y-axis-2': {
                        stacked: false,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Percentage'
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: function (value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function (tooltipItems) {
                                let title = tooltipItems[0].label || '';
                                title += ` ${new Date().toLocaleString('default', { month: 'long' })}`;
                                return title;
                            },
                            label: function (context) {
                                if (context.dataset.label === 'Percentage Accuracy') {
                                    return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) + '%' : '';
                                }
                                return context.raw;
                            }
                        }
                    },
                    legend: {
                        labels: {
                            filter: function (item, chart) {
                                return item.text !== 'Percentage Accuracy';
                            }
                        }
                    }
                }
            }
        });
    }

    if (typeof itemCodeQuantities === 'object') {
        Object.keys(itemCodeQuantities).forEach((groupIndex) => {
            console.log('Processing item code group:', groupIndex);

            const group = itemCodeQuantities[groupIndex];
            const itemCodes = group.map(item => item.code);
            const quantities = group.map(item => item.qty);

            console.log(`Item Codes for Group ${groupIndex}:`, itemCodes);
            console.log(`Quantities for Group ${groupIndex}:`, quantities);

            const ctx = document.getElementById(`item-code-quantity-chart-${groupIndex}`).getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: itemCodes,
                    datasets: [{
                        label: 'Quantity',
                        data: quantities,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)', // Increase opacity
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: false,
                            categoryPercentage: 0.5,
                            barPercentage: 0.5,
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0,
                                minRotation: 0
                            }
                        },
                        y: {
                            stacked: false,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Quantity'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function (tooltipItems) {
                                    let title = tooltipItems[0].label || '';
                                    title += ` ${month}`;
                                    return title;
                                },
                                label: function (context) {
                                    return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) : '';
                                }
                            }
                        }
                    }
                }
            });
        });
    }

    // Vendor Monthly Summary Chart
    const vendorSummaryCtx = document.getElementById('vendorSummaryChart').getContext('2d');
    const vendorSummaryChart = new Chart(vendorSummaryCtx, {
        type: 'bar',
        data: {
            labels: vendors,
            datasets: [{
                label: 'Planned',
                data: totalPlanned,
                backgroundColor: 'rgba(54, 162, 235, 0.8)', // Increase opacity
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                type: 'bar'
            },
            {
                label: 'Actual',
                data: totalActual,
                backgroundColor: 'rgba(255, 159, 64, 0.8)', // Increase opacity
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1,
                type: 'bar'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Swapping the axes
            scales: {
                x: {
                    stacked: false,
                    categoryPercentage: 0.5,
                    barPercentage: 0.5,
                    title: {
                        display: true,
                        text: 'Quantity'
                    }
                },
                y: {
                    stacked: false,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Vendor'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function (tooltipItems) {
                            let title = tooltipItems[0].label || '';
                            title += ` ${month}`;
                            console.log('Tooltip Title:', title); // Debugging
                            return title;
                        },
                        label: function (context) {
                            console.log('Context raw value:', context.raw); // Debugging
                            let label = `${context.dataset.label || ''}: `;
                            if (typeof context.raw === 'number') {
                                label += context.raw.toFixed(0); // Show quantity with no decimal places
                            } else {
                                console.log('context.raw is not a number:', context.raw); // Debugging
                            }
                            console.log('Tooltip Label:', label); // Debugging
                            return label;
                        },
                        afterLabel: function (context) {
                            const plannedIndex = context.datasetIndex === 0 ? context.dataIndex : null;
                            const actualIndex = context.datasetIndex === 1 ? context.dataIndex : null;
                            if (plannedIndex !== null) {
                                const plannedValue = context.chart.data.datasets[0].data[plannedIndex];
                                const actualValue = context.chart.data.datasets[1].data[plannedIndex];
                                const difference = actualValue - plannedValue;
                                return ` ${plannedValue}`;
                            } else if (actualIndex !== null) {
                                const plannedValue = context.chart.data.datasets[0].data[actualIndex];
                                const actualValue = context.chart.data.datasets[1].data[actualIndex];
                                const difference = actualValue - plannedValue;
                                return ` ${actualValue}`;
                            }
                            return null;
                        }
                    }
                }
            }
        }
    });

});
</script>


        <script>
            function refreshPage() {
                setTimeout(function() {
                    location.reload();
                }, 200000); // 300000 milliseconds = 5 minutes
            }

            // Call the function when the page loads
            refreshPage();
</script>
@endsection
