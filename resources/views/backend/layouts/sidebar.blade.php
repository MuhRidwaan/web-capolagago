<aside class="main-sidebar sidebar-dark-success elevation-4 capolaga-sidebar">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="{{ asset('backend/dist/img/AdminLTELogo.png') }}" alt="Capolaga Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Capolaga Admin</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('backend/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2"
                    alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name ?? 'Administrator' }}</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard Utama</p>
                    </a>
                </li>

                <li class="nav-header">DATA MASTER</li>

                <li class="nav-item {{ request()->is('admin/master/wilayah*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/master/wilayah*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-map-marker-alt"></i>
                        <p>
                            Master Wilayah
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Spot Lokasi (Blok/Area)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Master Fasilitas</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item {{ request()->is('admin/master/ekosistem*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/master/ekosistem*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-network-wired"></i>
                        <p>
                            Master Ekosistem
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Kategori Produk</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Data Mitra Lokal</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Daftar Rekening Bank</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item {{ request()->is('admin/master/inventaris*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/master/inventaris*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box-open"></i>
                        <p>
                            Master Inventaris
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Produk Utama</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Aktivitas Add-on</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Paket Bundling</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fas fa-calendar-alt nav-icon text-warning"></i>
                                <p>Manajemen Slot/Kuota</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-header">TRANSAKSI & RESERVASI</li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-shopping-basket"></i>
                        <p>Semua Reservasi</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-camera-retro"></i>
                        <p>
                            Verifikasi Manual
                            <span class="right badge badge-danger">New</span>
                        </p>
                    </a>
                </li>

                <li class="nav-header">LAPORAN & KEUANGAN</li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-wallet"></i>
                        <p>Bagi Hasil Mitra</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>Analitik LOS</p>
                    </a>
                </li>

                <li class="nav-header">SYSTEM</li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Pengaturan Umum</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
