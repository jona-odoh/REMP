<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Official &amp; Stakeholder Login — Kogi State REMP</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-social/bootstrap-social.css') }}">
  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.ico') }}" />
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row justify-content-center mb-4">
          <div class="col-12 text-center">
            <img alt="Kogi REMP Logo" src="{{ asset('assets/img/logo.png') }}" style="height: 48px;" class="mb-2">
            <h4 class="font-weight-bold text-dark mb-1">Kogi State Rural Electrification Decision-Support Platform</h4>
            <p class="text-muted mb-0">Official, LGA Officer, Field Collector, Energy Analyst &amp; Stakeholder Access</p>
          </div>
        </div>

        @if($errors->any())
          <div class="row justify-content-center">
            <div class="col-lg-10">
              <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                  <div>{{ $error }}</div>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        <div class="row justify-content-center">
          <!-- Standard Credentials Login -->
          <div class="col-12 col-md-6 col-lg-5">
            <div class="card card-primary">
              <div class="card-header">
                <h4>Official Portal Login</h4>
              </div>
              <div class="card-body">
                <form method="POST" action="{{ route('login.submit') }}" class="needs-validation" novalidate="">
                  @csrf
                  <div class="form-group">
                    <label for="email">Official Email Address</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email', 'admin@kogiremp.gov.ng') }}" tabindex="1" required autofocus>
                    <div class="invalid-feedback">Please fill in your official email.</div>
                  </div>
                  <div class="form-group">
                    <label for="password" class="control-label">Password</label>
                    <input id="password" type="password" class="form-control" name="password" value="password" tabindex="2" required>
                    <small class="form-text text-muted">Default seeded password for all demo accounts: <code>password</code></small>
                  </div>
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember-me">
                      <label class="custom-control-label" for="remember-me">Remember Me</label>
                    </div>
                  </div>
                  <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                      <i class="fas fa-sign-in-alt mr-1"></i> Sign In to Platform
                    </button>
                  </div>
                </form>
              </div>
              <div class="card-footer bg-whitesmoke text-center">
                <span class="text-muted">Are you a community resident?</span><br>
                <a href="{{ route('public.report.create') }}" class="btn btn-warning btn-sm mt-2">
                  <i class="fas fa-bullhorn mr-1"></i> Open Public Electricity Reporting Portal (No Login)
                </a>
              </div>
            </div>
          </div>

          <!-- One-Click Role Switcher -->
          <div class="col-12 col-md-6 col-lg-5">
            <div class="card card-success">
              <div class="card-header">
                <h4>Quick Role Demonstration Switcher</h4>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">
                  Evaluate all FRD Section 4 User Roles immediately with one click:
                </p>
                <div class="list-group">
                  @foreach($demoUsers as $u)
                    <form method="POST" action="{{ route('login.demo') }}" class="mb-2">
                      @csrf
                      <input type="hidden" name="email" value="{{ $u->email }}">
                      <button type="submit" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3">
                        <div class="text-left">
                          <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $u->name }}</div>
                          <small class="text-muted">{{ $u->email }}</small>
                        </div>
                        <span class="badge badge-primary badge-pill text-uppercase">
                          {{ str_replace('_', ' ', $u->role_enum) }}
                          @if($u->lga) &bull; {{ $u->lga->name }} @endif
                        </span>
                      </button>
                    </form>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="simple-footer text-center text-muted mt-3">
          Kogi State Rural Electrification Decision-Support Platform &bull; Okene, Idah &amp; Kabba/Bunu LGAs
        </div>
      </div>
    </section>
  </div>

  <!-- General JS Scripts -->
  <script src="{{ asset('assets/js/app.min.js') }}"></script>
  <script src="{{ asset('assets/js/scripts.js') }}"></script>
  <script src="{{ asset('assets/js/custom.js') }}"></script>
</body>
</html>
