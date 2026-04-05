<aside class="main-sidebar sidebar-dark-success elevation-4 capolaga-sidebar">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="{{ asset('backend/dist/img/AdminLTELogo.png') }}" alt="Capolaga Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Capolaga Admin</span>
    </a>

    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('backend/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2"
                    alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name ?? 'Administrator' }}</a>
                <small class="text-muted">
                    @foreach(Auth::user()->getRoleNames() as $role)
                        <span>{{ $role }}</span>@if(!$loop->last), @endif
                    @endforeach
                </small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu"
                data-accordion="false">

                {{-- ===================== UTAMA ===================== --}}
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                 {{-- ===================== PENGATURAN SISTEM ===================== --}}
                @can('manage_users')
                <li class="nav-header">PENGATURAN SISTEM</li>

                <li class="nav-item {{ request()->is('admin/users*') || request()->is('admin/roles*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/users*') || request()->is('admin/roles*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Pengguna & Akses <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}"
                                class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}"
                                class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Manajemen Role</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.payment-methods.index') }}"
                        class="nav-link {{ request()->routeIs('admin.payment-methods.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-credit-card"></i>
                        <p>Metode Pembayaran</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>Pengaturan <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.payment') }}"
                                class="nav-link {{ request()->routeIs('admin.settings.payment*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Midtrans</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.mail') }}"
                                class="nav-link {{ request()->routeIs('admin.settings.mail*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Email / SMTP</p>
                            </a>
                        </li>
                    </ul>
                </li>                @endcan

                {{-- ===================== RESERVASI & TRANSAKSI ===================== --}}
                @canany(['manage_transactions', 'manage_users'])
                <li class="nav-header">RESERVASI & TRANSAKSI</li>

                @can('manage_transactions')
                <li class="nav-item {{ request()->routeIs('admin.bookings.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>Pemesanan <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.bookings.index') }}"
                                class="nav-link {{ request()->routeIs('admin.bookings.index') && ! request('status') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Semua Booking</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}"
                                class="nav-link {{ request()->routeIs('admin.bookings.index') && request('status') === 'pending' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Menunggu Pembayaran</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.bookings.index', ['status' => 'confirmed']) }}"
                                class="nav-link {{ request()->routeIs('admin.bookings.index') && request('status') === 'confirmed' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Terkonfirmasi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.bookings.index', ['status' => 'checked_in']) }}"
                                class="nav-link {{ request()->routeIs('admin.bookings.index') && request('status') === 'checked_in' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Check-In Hari Ini</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.payments.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-money-bill-wave"></i>
                        <p>Pembayaran <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.payments.index') }}"
                                class="nav-link {{ request()->routeIs('admin.payments.index') && ! request('status') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Semua Transaksi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}"
                                class="nav-link {{ request()->routeIs('admin.payments.index') && request('status') === 'pending' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Menunggu Konfirmasi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.payments.index', ['status' => 'refunded']) }}"
                                class="nav-link {{ request()->routeIs('admin.payments.index') && request('status') === 'refunded' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon text-danger"></i>
                                <p>Refund</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="{{ url('admin/commissions') }}"
                        class="nav-link {{ request()->is('admin/commissions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-percentage"></i>
                        <p>Komisi Mitra</p>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ===================== PRODUK & LAYANAN ===================== --}}
                @can('manage_products')
                <li class="nav-header">PRODUK & LAYANAN</li>

                <li class="nav-item {{ request()->is('admin/products*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box-open"></i>
                        <p>Katalog Produk <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('admin/products') }}"
                                class="nav-link {{ request()->is('admin/products') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Semua Produk</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/products/create') }}"
                                class="nav-link {{ request()->is('admin/products/create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Tambah Produk</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/product-categories') }}"
                                class="nav-link {{ request()->is('admin/product-categories*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Kategori Produk</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/activity-tags') }}"
                                class="nav-link {{ request()->is('admin/activity-tags*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tag Aktivitas</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.slots.index') }}"
                        class="nav-link {{ request()->routeIs('admin.slots.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Ketersediaan Slot</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('admin/reviews') }}"
                        class="nav-link {{ request()->is('admin/reviews*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Ulasan & Rating</p>
                    </a>
                </li>
                @endcan

                {{-- ===================== MITRA ===================== --}}
                @canany(['manage_users', 'manage_products'])
                <li class="nav-header">MITRA</li>

                <li class="nav-item {{ request()->is('admin/mitra*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/mitra*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-store"></i>
                        <p>Manajemen Mitra <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('admin/mitra') }}"
                                class="nav-link {{ request()->is('admin/mitra') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Daftar Mitra</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/mitra?status=pending') }}"
                                class="nav-link">
                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Menunggu Verifikasi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/commission-tiers') }}"
                                class="nav-link {{ request()->is('admin/commission-tiers*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tier Komisi</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcanany

                {{-- ===================== PROMOSI ===================== --}}
                @can('manage_products')
                <li class="nav-header">PROMOSI</li>

                <li class="nav-item {{ request()->is('admin/promotions*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/promotions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-ticket-alt"></i>
                        <p>Voucher & Promo <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('admin/promotions') }}"
                                class="nav-link {{ request()->is('admin/promotions') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Semua Promo</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/promotions/create') }}"
                                class="nav-link {{ request()->is('admin/promotions/create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Buat Promo Baru</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/promo-types') }}"
                                class="nav-link {{ request()->is('admin/promo-types*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tipe Promo</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- ===================== LAPORAN ===================== --}}
                @can('view_reports')
                <li class="nav-header">LAPORAN</li>

                <li class="nav-item {{ request()->is('admin/reports*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Laporan <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.index') }}"
                                class="nav-link {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Penjualan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.commissions') }}"
                                class="nav-link {{ request()->is('admin/reports/commissions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Komisi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.products') }}"
                                class="nav-link {{ request()->is('admin/reports/products*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Performa Produk</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

               

                {{-- ===================== LOGOUT ===================== --}}
                <li class="nav-item mt-3 mb-5">
                    <a href="#" class="nav-link text-danger"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>

            </ul>
        </nav>
    </div>
</aside>
