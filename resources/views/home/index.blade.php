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
                            <div class="row">
                                <div class="col-md-8">
                                    <table style="margin-top: -20px" class="indicator-table mb-4">
                                        <tr>
                                            <th>Signal Indicator</th>
                                        </tr>
                                        <tr>
                                            <td>
                                                    <span class="signal green px-2">G</span> ≥ 95%
                                                    <span class="signal yellow">Y</span> ≥ 85%
                                                    <span class="signal red">R</span> < 85%
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <table style="margin-top: -20px" class="indicator-table mb-4">
                                        <tr>
                                            <th>Average Inventory</th>
                                            <th>Signal</th>
                                        </tr>
                                        <tr>
                                            <td>{{ number_format($averageInventoryMonitoring, 2) }}%</td>
                                            <td>
                                                <span id="signal-inventory" class="signal
                                                    {{ $averageInventoryMonitoring >= 95 ? 'green' : ($averageInventoryMonitoring >= 85 ? 'yellow' : 'red') }}">
                                                    {{ $averageInventoryMonitoring >= 95 ? 'G' : ($averageInventoryMonitoring >= 85 ? 'Y' : 'R') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>


                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($itemCodes as $itemCode => $comparisons)
                                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div style="margin-top: -20px" class="carousel-inner">
                                    @foreach ($itemCodes as $itemCode => $comparisons)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <p class="text-center">{{ $itemCode }}</p>
                                            <div class="chart-container">
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
                           <div class="row">
                                <div class="col-md-8">
                                    <table style="margin-top: -20px" class="indicator-table mb-4">
                                        <tr>
                                            <th>Signal Indicator</th>
                                        </tr>
                                        <tr>
                                            <td>
                                                    <span class="signal green px-2">G</span> ≥ 95%
                                                    <span class="signal yellow">Y</span> ≥ 85%
                                                    <span class="signal red">R</span> < 85%
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <table style="margin-top: -20px" class="indicator-table mb-4">
                                        <tr>
                                            <th>Average OTDC</th>
                                            <th>Signal</th>
                                        </tr>
                                        <tr>
                                            <td>{{ number_format($averageOTDC, 2) }}%</td>
                                            <td>
                                                <span id="signal-inventory" class="signal
                                                    {{ $averageOTDC >= 95 ? 'green' : ($averageOTDC >= 85 ? 'yellow' : 'red') }}">
                                                    {{ $averageOTDC >= 95 ? 'G' : ($averageOTDC >= 85 ? 'Y' : 'R') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div id="otdcCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($vendorData as $vendorName => $data)
                                        <button type="button" data-bs-target="#otdcCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div style="margin-top: -20px" class="carousel-inner">
                                    @foreach ($vendorData as $vendorName => $data)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <p class="text-center">{{ $vendorName }}</p>
                                            <div class="chart-container">
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

                <!-- Stock Level Carousel -->
                <div class="col-md-6 mb-2">
                    <div style="height: 375px" class="card card-custom">
                        <div class="card-header">
                            <h4>Stock Level</h4>
                        </div>
                        <div class="card-body">
                            <div id="stockLevelCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($stockLevels as $itemCode => $levels)
                                        <button type="button" data-bs-target="#stockLevelCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div style="margin-top: -20px" class="carousel-inner">
                                    @foreach ($stockLevels as $itemCode => $levels)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <p class="text-center">{{ $itemCode }}</p>
                                            <div class="chart-container">
                                                <canvas id="stock-level-{{ $itemCode }}" class="chart-custom"></canvas>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#stockLevelCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#stockLevelCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
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

            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
    console.log('DOM fully loaded and parsed');

    const groupedComparisons = @json($itemCodes);
    const groupedStockLevels = @json($stockLevels);
    const vendorData = @json($vendorData);
    const itemCodeQuantities = @json($itemCodeQuantities);
    const month = new Date().toLocaleString('default', { month: 'long' });

    console.log('Grouped Comparisons:', groupedComparisons);
    console.log('Grouped Stock Levels:', groupedStockLevels);
    console.log('Vendor Data:', vendorData);
    console.log('Item Code Quantities:', itemCodeQuantities);

    const getDefaultLabels = () => Array.from({ length: 31 }, (_, i) => (i + 1).toString());

    if (typeof groupedComparisons === 'object') {
        Object.keys(groupedComparisons).forEach((itemCode) => {
            console.log('Processing item code:', itemCode);

            const comparisons = groupedComparisons[itemCode];
            const plannedData = Array(31).fill(null);
            const actualData = Array(31).fill(null);
            const percentageDifference = Array(31).fill(0); // Default value set to 0%

            comparisons.forEach((comparison, index) => {
                const plannedDay = new Date(comparison.planned_receiving_date).getDate() - 1;
                const actualDay = new Date(comparison.receiving_date).getDate() - 1;
                plannedData[plannedDay] = comparison.planned_qty;
                actualData[actualDay] = comparison.received_qty;
            });

            console.log(`Planned Data for ${itemCode}:`, plannedData);
            console.log(`Actual Data for ${itemCode}:`, actualData);

            plannedData.forEach((value, index) => {
                if (value !== null && actualData[index] !== null) {
                    const percentage = ((actualData[index] / value) * 100);
                    percentageDifference[index] = percentage !== 0 ? percentage : 0; // Set to 0% if percentage is 0%
                }
            });

            console.log(`Percentage Difference for ${itemCode}:`, percentageDifference);

            const ctx = document.getElementById(`chart-${itemCode}`).getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: getDefaultLabels(),
                    datasets: [{
                        label: 'Planned Stock',
                        data: plannedData,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)', // Increase opacity
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 1
                    },
                    {
                        label: 'Actual Stock',
                        data: actualData,
                        backgroundColor: 'rgba(255, 159, 64, 0.8)', // Increase opacity
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
                        borderWidth: 2 // Increase the line thickness here
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
                                callback: function(value, index, values) {
                                    return (index + 1) % 4 === 0 || index === 0 ? (index + 1).toString() : '';
                                }
                            }
                        },
                        y: {
                            stacked: false,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Stock Quantity'
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
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    let title = tooltipItems[0].label || '';
                                    title += ` ${month}`;
                                    return title;
                                },
                                label: function(context) {
                                    if (context.dataset.label === 'Percentage Accuracy') {
                                        return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) + '%' : '';
                                    }
                                    return context.raw;
                                }
                            }
                        }
                    }
                }
            });
        });
    }

    if (typeof groupedStockLevels === 'object') {
        Object.keys(groupedStockLevels).forEach((itemCode) => {
            console.log('Processing stock level for item code:', itemCode);

            const levels = groupedStockLevels[itemCode];
            const stockData = Array(31).fill(null);

            levels.forEach((level, index) => {
                const day = new Date(level.date).getDate() - 1;
                stockData[day] = level.stock_level;
            });

            console.log(`Stock Data for ${itemCode}:`, stockData);

            const ctx = document.getElementById(`stock-level-${itemCode}`).getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: getDefaultLabels(),
                    datasets: [{
                        label: 'Stock Level',
                        data: stockData,
                        backgroundColor: 'rgba(75, 192, 192, 0.4)', // Increase opacity
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 3, // Increase line thickness
                        fill: true,
                        spanGaps: true // Ensure the line is continuous even with gaps
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            categoryPercentage: 0.5,
                            barPercentage: 0.5,
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0,
                                minRotation: 0,
                                callback: function(value, index, values) {
                                    return (index + 1) % 4 === 0 || index === 0 ? (index + 1).toString() : '';
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Stock Level'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(tooltipItems) {
                                    let title = tooltipItems[0].label || '';
                                    title += ` ${month}`;
                                    return title;
                                },
                                label: function(context) {
                                    return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) : '';
                                }
                            }
                        }
                    }
                }
            });
        });
    }

    if (typeof vendorData === 'object') {
        Object.keys(vendorData).forEach((vendorName) => {
            console.log('Processing vendor:', vendorName);

            const data = vendorData[vendorName];
            const plannedData = Array(31).fill(null);
            const actualData = Array(31).fill(null);
            const percentageAccuracy = Array(31).fill(0); // Default value set to 0%

            data.forEach((entry) => {
                const day = new Date(entry.date).getDate() - 1;
                plannedData[day] = entry.total_planned_qty;
                actualData[day] = entry.total_actual_qty;
                if (plannedData[day] !== null && actualData[day] !== null) {
                    const percentage = ((actualData[day] / plannedData[day]) * 100);
                    percentageAccuracy[day] = isNaN(percentage) ? 0 : percentage;
                }
            });

            console.log(`Planned Data for ${vendorName}:`, plannedData);
            console.log(`Actual Data for ${vendorName}:`, actualData);
            console.log(`Percentage Accuracy for ${vendorName}:`, percentageAccuracy);

            const ctx = document.getElementById(`otdc-chart-${vendorName}`).getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: getDefaultLabels(),
                    datasets: [{
                        label: 'Planned Qty',
                        data: plannedData,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)', // Increase opacity
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 1
                    },
                    {
                        label: 'Actual Qty',
                        data: actualData,
                        backgroundColor: 'rgba(255, 159, 64, 0.8)', // Increase opacity
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 2
                    },
                    {
                        label: 'Percentage Accuracy',
                        data: percentageAccuracy,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        type: 'line',
                        yAxisID: 'y-axis-2',
                        order: 0,
                        borderWidth: 2 // Increase the line thickness here
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
                                callback: function(value, index, values) {
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
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    let title = tooltipItems[0].label || '';
                                    title += ` ${month}`;
                                    return title;
                                },
                                label: function(context) {
                                    if (context.dataset.label === 'Percentage Accuracy') {
                                        return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) + '%' : '';
                                    }
                                    return context.raw;
                                }
                            }
                        }
                    }
                }
            });
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
                                title: function(tooltipItems) {
                                    let title = tooltipItems[0].label || '';
                                    title += ` ${month}`;
                                    return title;
                                },
                                label: function(context) {
                                    return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) : '';
                                }
                            }
                        }
                    }
                }
            });
        });
    }
});
</script>
@endsection
