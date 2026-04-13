  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light capolaga-navbar border-bottom-0">
      <ul class="navbar-nav align-items-center">
          <li class="nav-item">
              <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle sidebar">
                  <i class="fas fa-bars"></i>
              </a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
              <a href="{{ route('admin.dashboard') }}" class="nav-link font-weight-medium">Dashboard</a>
          </li>
      </ul>

      <ul class="navbar-nav ml-auto align-items-center">
          <li class="nav-item">
              <a class="nav-link" data-widget="fullscreen" href="#" role="button" aria-label="Toggle fullscreen">
                  <i class="fas fa-expand-arrows-alt"></i>
              </a>
          </li>

          {{-- User Dropdown --}}
          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 px-3" href="#"
                  id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:.85rem;color:#fff;font-weight:700;flex-shrink:0;">
                      {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                  </div>
                  <span class="d-none d-md-inline font-weight-medium" style="font-size:.875rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                      {{ auth()->user()->name }}
                  </span>
              </a>
              <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="userDropdown"
                  style="min-width:200px;border-radius:.75rem;overflow:hidden;margin-top:.5rem;">
                  <div class="px-4 py-3 border-bottom">
                      <p class="mb-0 font-weight-bold text-dark" style="font-size:.875rem;">{{ auth()->user()->name }}</p>
                      <p class="mb-0 text-muted" style="font-size:.75rem;">{{ auth()->user()->email }}</p>
                      <div class="mt-1">
                          @foreach(auth()->user()->roles as $role)
                              <span class="badge badge-success" style="font-size:.65rem;">{{ $role->name }}</span>
                          @endforeach
                      </div>
                  </div>
                  <a class="dropdown-item py-2" href="{{ route('admin.profile') }}">
                      <i class="fas fa-user-circle mr-2 text-muted"></i>Profil Saya
                  </a>
                  <a class="dropdown-item py-2" href="{{ route('admin.settings.payment') }}">
                      <i class="fas fa-cog mr-2 text-muted"></i>Pengaturan
                  </a>
                  <div class="dropdown-divider"></div>
                  <form action="{{ route('logout') }}" method="POST">
                      @csrf
                      <button type="submit" class="dropdown-item py-2 text-danger">
                          <i class="fas fa-sign-out-alt mr-2"></i>Logout
                      </button>
                  </form>
              </div>
          </li>
      </ul>
  </nav>
  <!-- /.navbar -->
