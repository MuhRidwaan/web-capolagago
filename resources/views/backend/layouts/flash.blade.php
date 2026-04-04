@push('styles')
    <style>
        .capolaga-swal-popup {
            border-radius: 0.9rem;
            padding: 2.2rem 2rem 1.8rem;
        }

        .capolaga-swal-title {
            font-size: 1rem;
            font-weight: 700;
            color: #3d4650;
        }

        .capolaga-swal-html {
            font-size: 0.95rem;
            color: #6c7682;
            line-height: 1.65;
        }

        .capolaga-swal-confirm {
            min-width: 110px;
            border-radius: 0.45rem !important;
            font-weight: 600 !important;
        }
    </style>
@endpush

@if (session('success') || session('error'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') {
                    return;
                }

                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: @json(session('success')),
                        timer: 1800,
                        timerProgressBar: false,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'capolaga-swal-popup',
                            title: 'capolaga-swal-title',
                            htmlContainer: 'capolaga-swal-html'
                        }
                    });
                @elseif (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: @json(session('error')),
                        timer: 2200,
                        timerProgressBar: false,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'capolaga-swal-popup',
                            title: 'capolaga-swal-title',
                            htmlContainer: 'capolaga-swal-html'
                        }
                    });
                @endif
            });
        </script>
    @endpush
@endif
