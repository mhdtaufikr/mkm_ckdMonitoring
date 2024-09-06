<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- PWA  -->
        <meta name="theme-color" content="#6777ef"/>
        <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>MKM CKD Monitoring</title>
        <link href="{{asset('assets/css/styles.css')}}" rel="stylesheet" />
        <link rel="icon" href="{{ asset('assets/img/logo_kop2.gif') }}">
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.ckeditor.com/4.6.2/standard/ckeditor.js"></script>
        <script src="{{ url('https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js') }}"></script>

        <!-- DataTables CSS -->
        <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.css">

        <!-- DataTables JS -->
        <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

        <!-- DataTables Buttons JS -->
        <script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
        <script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

        <!-- Include the chart.js-adapter-date-fns -->
        <script src="{{ asset('plugins/chart.js/Chart.bundle.min.js') }}"></script>

        <!-- Include Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Include the chart.js-adapter-date-fns -->
        <script src="{{ asset('plugins/chart.js/Chart.bundle.min.js') }}"></script>

        <!-- Include Chosen CSS -->
        <link href="{{asset('chosen/chosen.min.css')}}" rel="stylesheet" />

        <!-- Include Chosen JS -->
        <script src="{{asset('chosen/chosen.jquery.min.js')}}"></script>

            <!-- Include cdn.amcharts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-trendline"></script>
        <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    </head>

    <body class="nav-fixed sidenav-toggled">
        @include('layouts.includes._topbar')
            <div id="layoutSidenav">
                @include('layouts.includes._sidebar')
                    <div id="layoutSidenav_content">
                        @yield('content')
                        <div id="layoutSidenav_content">
                            <footer class="footer-admin mt-auto footer-light">
                                <div class="container-xl px-4">
                                    <div class="row">
                                        <div class="col-md-6 small"></div>
                                        <div class="col-md-6 text-md-end small">
                                         Copyright PT Mitsubishi Krama Yudha Motors and Manufacturing&copy; 2023
                                        </div>
                                    </div>
                                </div>
                            </footer>
                        </div>
                    </div>
            </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src={{asset('assets/js/scripts.js')}} ></script>
    </body>
</html>
