<div id="layoutSidenav_nav">
    <nav class="sidenav shadow-right sidenav-light">
        <div class="sidenav-menu">
            <div class="nav accordion" id="accordionSidenav">
               <!-- Sidenav Menu Heading (Home)-->
                <div class="sidenav-menu-heading">Home</div>
                <!-- Sidenav Accordion (Home) -->
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseHome" aria-expanded="false" aria-controls="collapseHome">
                    <div class="nav-link-icon"><i class="fas fa-home"></i></div>
                    Home Sections
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseHome" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ url('/home') }}">Raw Material</a>
                        <a class="nav-link" href="{{ url('/home/ckd') }}">CKD Stamping</a>
                        <a class="nav-link" href="{{ url('/home/ckd/nouba') }}">CKD Nouba</a>
                        <a class="nav-link" href="{{ url('/home/l305') }}">L305</a>
                        <a class="nav-link" href="{{ url('/home/cvcL404') }}">CVC L404</a>
                    </nav>
                </div>

                <!-- Sidenav Accordion (Inventory) -->
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseInventory" aria-expanded="false" aria-controls="collapseInventory">
                    <div class="nav-link-icon"><i class="fas fa-warehouse"></i></div>
                    Inventory
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseInventory" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ url('/inventory/ckd') }}">CKD</a>
                        <a class="nav-link" href="{{ url('/inventory/raw-material') }}">Raw Material</a>
                    </nav>
                </div>

                @if(\Auth::user()->role === 'Super Admin' || \Auth::user()->role === 'IT')
                 <!-- Sidenav Menu Heading (Master)-->
                 <div class="sidenav-menu-heading">Master</div>
                 <!-- Sidenav Accordion (Master)-->
                 <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapsemaster" aria-expanded="false" aria-controls="collapsemaster">
                     <div class="nav-link-icon"><i class="fas fa-database"></i></div>
                     Master Data
                     <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                 </a>
                 <div class="collapse" id="collapsemaster" data-bs-parent="#accordionSidenav">
                     <nav class="sidenav-menu-nested nav">
                        <a class="nav-link" href="{{url('/master/mechine')}}">Mechine Checksheet</a>
                     </nav>
                 </div>
                 @endif
                @if(\Auth::user()->role === 'IT')
                <!-- Sidenav Menu Heading (Core)-->
                <div class="sidenav-menu-heading">Configuration</div>
                <!-- Sidenav Accordion (Utilities)-->
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseUtilities" aria-expanded="false" aria-controls="collapseUtilities">
                    <div class="nav-link-icon"><i data-feather="tool"></i></div>
                    Master Configuration
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseUtilities" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav">
                        <a class="nav-link" href="{{url('/dropdown')}}">Dropdown</a>
                        <a class="nav-link" href="{{url('/rule')}}">Rules</a>
                        <a class="nav-link" href="{{url('/user')}}">User</a>
                    </nav>
                </div>
                @endif
            </div>
        </div>
        <!-- Sidenav Footer-->
        <div class="sidenav-footer">
            <div class="sidenav-footer-content">
                <div class="sidenav-footer-subtitle">Logged in as:</div>
                <div class="sidenav-footer-title">{{ auth()->user()->name }}</div>
            </div>
        </div>
    </nav>
</div>
