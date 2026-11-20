<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Kogi State Rural Electrification Decision-Support Platform')</title>
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <meta name="theme-color" content="#6777ef">

  <!-- Otika General CSS Files -->
  <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
  <!-- Otika Template CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
  <!-- Otika Custom style CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.ico') }}" />

  <!-- Leaflet.js CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

  @livewireStyles
  <style>
    .sidebar-brand .logo-name {
      font-size: 15px;
      letter-spacing: 0.5px;
    }
    .leaflet-container {
      z-index: 1;
    }
    .table-sm td, .table-sm th {
      vertical-align: middle;
    }
  </style>
  @stack('styles')
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      <!-- Top Navbar -->
      <nav class="navbar navbar-expand-lg main-navbar sticky">
        <div class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li>
              <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                <i data-feather="align-justify"></i>
              </a>
            </li>
            <li>
              <a href="#" class="nav-link nav-link-lg fullscreen-btn">
                <i data-feather="maximize"></i>
              </a>
            </li>
            <li class="d-none d-md-flex align-items-center ml-2">
              <span class="font-weight-bold text-dark" style="font-size: 13px;">
                Kogi State Rural Electrification Decision-Support Platform
              </span>
            </li>
          </ul>
        </div>

        <ul class="navbar-nav navbar-right align-items-center">
          <li class="mr-3 d-none d-sm-block">
            <span id="network-badge" class="badge badge-success">
              <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Online
            </span>
          </li>

          @auth
            <li class="dropdown">
              <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img alt="image" src="{{ asset('assets/img/user.png') }}" class="user-img-radious-style">
                <span class="d-sm-none d-lg-inline-block text-dark ml-1">
                  {{ auth()->user()->name }}
                </span>
              </a>
              <div class="dropdown-menu dropdown-menu-right pullDown">
                <div class="dropdown-title">
                  {{ strtoupper(str_replace('_', ' ', auth()->user()->role_enum)) }}
                  @if(auth()->user()->lga)
                    ({{ auth()->user()->lga->name }})
                  @endif
                </div>
                <a href="{{ route('dashboard') }}" class="dropdown-item has-icon">
                  <i class="fas fa-tachometer-alt"></i> Executive Dashboard
                </a>
                <a href="{{ route('geography.index') }}" class="dropdown-item has-icon">
                  <i class="fas fa-map-marked-alt"></i> GIS Map &amp; LGAs
                </a>
                <a href="{{ route('planning.index') }}" class="dropdown-item has-icon">
                  <i class="fas fa-file-export"></i> Prioritisation Reports
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item has-icon text-danger border-0 bg-transparent w-100 text-left" style="cursor:pointer;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                  </button>
                </form>
              </div>
            </li>
          @else
            <li>
              <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-sign-in-alt mr-1"></i> Official / Staff Login
              </a>
            </li>
          @endauth
        </ul>
      </nav>

      <!-- Left Main Sidebar -->
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="{{ auth()->check() ? route('dashboard') : route('public.report.create') }}">
              <img alt="image" src="{{ asset('assets/img/logo.png') }}" class="header-logo" />
              <span class="logo-name">KOGI REMP</span>
            </a>
          </div>

          <ul class="sidebar-menu">
            <li class="menu-header">Overview &amp; GIS</li>
            @auth
              <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="nav-link">
                  <i data-feather="monitor"></i><span>1. Dashboard</span>
                </a>
              </li>
              <li class="{{ request()->routeIs('geography.*') || request()->routeIs('communities.*') ? 'active' : '' }}">
                <a href="{{ route('geography.index') }}" class="nav-link">
                  <i data-feather="map-pin"></i><span>2. GIS Map &amp; LGAs</span>
                </a>
              </li>
            @endauth

            <li class="menu-header">Public Participation</li>
            <li class="{{ request()->routeIs('public.report.create') ? 'active' : '' }}">
              <a href="{{ route('public.report.create') }}" class="nav-link">
                <i data-feather="alert-circle"></i><span>3. Report Challenge</span>
              </a>
            </li>
            <li class="{{ request()->routeIs('public.report.track') ? 'active' : '' }}">
              <a href="{{ route('public.report.track') }}" class="nav-link">
                <i data-feather="search"></i><span>Track Reference</span>
              </a>
            </li>

            @auth
              <li class="menu-header">Field &amp; Verification</li>
              @if(auth()->user()->hasRoleOrEnum(['admin', 'lga_officer', 'field_officer']))
                <li class="{{ request()->routeIs('reports.verification') ? 'active' : '' }}">
                  <a href="{{ route('reports.verification') }}" class="nav-link">
                    <i data-feather="check-square"></i><span>4a. Verification Queue</span>
                  </a>
                </li>
              @endif
              <li class="{{ request()->routeIs('assessments.index') || request()->routeIs('assessments.show') ? 'active' : '' }}">
                <a href="{{ route('assessments.index') }}" class="nav-link">
                  <i data-feather="clipboard"></i><span>4b. Field Assessments</span>
                </a>
              </li>
              @if(auth()->user()->hasRoleOrEnum(['admin', 'field_officer', 'lga_officer']))
                <li class="{{ request()->routeIs('assessments.create') ? 'active' : '' }}">
                  <a href="{{ route('assessments.create') }}" class="nav-link">
                    <i data-feather="plus-square"></i><span>New Assessment</span>
                  </a>
                </li>
              @endif

              <li class="menu-header">Energy &amp; Planning</li>
              <li class="{{ request()->routeIs('energy.*') ? 'active' : '' }}">
                <a href="{{ route('energy.index') }}" class="nav-link">
                  <i data-feather="zap"></i><span>5 &amp; 6. Energy &amp; Costing</span>
                </a>
              </li>
              <li class="{{ request()->routeIs('planning.*') ? 'active' : '' }}">
                <a href="{{ route('planning.index') }}" class="nav-link">
                  <i data-feather="bar-chart-2"></i><span>7. Prioritisation &amp; Reports</span>
                </a>
              </li>

              @if(auth()->user()->hasRoleOrEnum(['admin']))
                <li class="menu-header">Administration</li>
                <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                  <a href="{{ route('users.index') }}" class="nav-link">
                    <i data-feather="users"></i><span>User Roles &amp; Access</span>
                  </a>
                </li>
              @endif
            @else
              <li class="menu-header">Official Portal</li>
              <li>
                <a href="{{ route('login') }}" class="nav-link">
                  <i data-feather="lock"></i><span>Staff / Stakeholder Login</span>
                </a>
              </li>
            @endauth
          </ul>
        </aside>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <!-- Flash Messages -->
          @if(session('success'))
            <div class="alert alert-success alert-dismissible show fade">
              <div class="alert-body">
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
              </div>
            </div>
          @endif

          @if(session('duplicate_notice'))
            <div class="alert alert-warning alert-dismissible show fade">
              <div class="alert-body">
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('duplicate_notice') }}
              </div>
            </div>
          @endif

          @if($errors->any())
            <div class="alert alert-danger alert-dismissible show fade">
              <div class="alert-body">
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                <strong>Please review the following validation errors:</strong>
                <ul class="mb-0 mt-1 pl-3">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          @endif

          <div class="section-body">
            @yield('content')
          </div>
        </section>

        <!-- Otika Theme Setting Sidebar -->
        <div class="settingSidebar">
          <a href="javascript:void(0)" class="settingPanelToggle">
            <i class="fa fa-spin fa-cog"></i>
          </a>
          <div class="settingSidebar-body ps-container ps-theme-default">
            <div class="fade show active">
              <div class="setting-panel-header">Theme Settings</div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Select Layout</h6>
                <div class="selectgroup layout-color w-50">
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="1" class="selectgroup-input-radio select-layout" checked>
                    <span class="selectgroup-button">Light</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="2" class="selectgroup-input-radio select-layout">
                    <span class="selectgroup-button">Dark</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Sidebar Color</h6>
                <div class="selectgroup selectgroup-pills sidebar-color">
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="1" class="selectgroup-input select-sidebar">
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip" data-original-title="Light Sidebar"><i class="fas fa-sun"></i></span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="2" class="selectgroup-input select-sidebar" checked>
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip" data-original-title="Dark Sidebar"><i class="fas fa-moon"></i></span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Color Theme</h6>
                <div class="theme-setting-options">
                  <ul class="choose-theme list-unstyled mb-0">
                    <li title="white" class="active"><div class="white"></div></li>
                    <li title="cyan"><div class="cyan"></div></li>
                    <li title="black"><div class="black"></div></li>
                    <li title="purple"><div class="purple"></div></li>
                    <li title="orange"><div class="orange"></div></li>
                    <li title="green"><div class="green"></div></li>
                    <li title="red"><div class="red"></div></li>
                  </ul>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <div class="theme-setting-options">
                  <label class="m-b-0">
                    <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input" id="mini_sidebar_setting">
                    <span class="custom-switch-indicator"></span>
                    <span class="control-label p-l-10">Mini Sidebar</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <div class="theme-setting-options">
                  <label class="m-b-0">
                    <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input" id="sticky_header_setting">
                    <span class="custom-switch-indicator"></span>
                    <span class="control-label p-l-10">Sticky Header</span>
                  </label>
                </div>
              </div>
              <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele">
                <a href="#" class="btn btn-icon icon-left btn-primary btn-restore-theme">
                  <i class="fas fa-undo"></i> Restore Default
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="main-footer">
        <div class="footer-left">
          <strong>Kogi State Rural Electrification Decision-Support Platform</strong> &mdash; Initial Scope: Okene, Idah &amp; Kabba/Bunu LGAs (v1.0)
        </div>
        <div class="footer-right">
          Indicative Costing in Nigerian Naira (&#8358;)
        </div>
      </footer>
    </div>
  </div>

  <!-- Otika General JS Scripts -->
  <script src="{{ asset('assets/js/app.min.js') }}"></script>
  <!-- Leaflet.js & Chart.js -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <!-- Otika Template JS File -->
  <script src="{{ asset('assets/js/scripts.js') }}"></script>
  <script src="{{ asset('assets/js/custom.js') }}"></script>

  @livewireScripts
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
    function updateNetworkStatus() {
      const badge = document.getElementById('network-badge');
      if (!badge) return;
      if (navigator.onLine) {
        badge.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Online';
        badge.className = 'badge badge-success';
      } else {
        badge.innerHTML = '<i class="fas fa-bolt mr-1"></i> Offline Mode (Local Drafts)';
        badge.className = 'badge badge-warning';
      }
    }
    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);
  </script>
  @stack('scripts')
</body>
</html>
