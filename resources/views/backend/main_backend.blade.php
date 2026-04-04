@include('backend.layouts.header')
<!-- Navigasi -->
@include('backend.layouts.nav')
<!-- Sidebar -->
@include('backend.layouts.sidebar')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">


    <!-- Content -->
    @yield('content')

    <!-- /.content -->
</div>
<!-- Footer -->
@include('backend.layouts.footer')
