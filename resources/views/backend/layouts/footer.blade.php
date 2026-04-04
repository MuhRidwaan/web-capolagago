  <!-- /.content-wrapper -->
  <footer class="main-footer">
      <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
      All rights reserved.
      <div class="float-right d-none d-sm-inline-block">
          <b>Version</b> 3.2.0
      </div>
  </footer>

  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script src="{{ asset('backend/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('backend/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('backend/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/dist/js/adminlte.min.js') }}"></script>
<script>
    window.capolagaSwal = window.capolagaSwal || {};

    capolagaSwal.customSkipSelectors = [
        '#form-status',
        '#form-confirm',
        '#form-refund',
        '#form-bulk',
        '.status-form',
        '.confirm-form',
        '.form-toggle',
        '.form-delete',
        '.form-delete-slot'
    ];

    capolagaSwal.showLoading = function (title, text) {
        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            title: title || 'Processing...',
            text: text || 'Mohon tunggu, data sedang diproses.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };

    capolagaSwal.shouldHandleForm = function (form) {
        if (!(form instanceof HTMLFormElement)) {
            return false;
        }

        const httpMethod = (form.querySelector('input[name="_method"]')?.value || form.getAttribute('method') || 'GET')
            .toUpperCase();

        if (httpMethod === 'GET' || form.id === 'logout-form' || form.dataset.noSwal === 'true') {
            return false;
        }

        if (form.dataset.swalManaged === 'custom' || form.dataset.swalConfirmed === 'true') {
            return false;
        }

        return ! capolagaSwal.customSkipSelectors.some((selector) => form.matches(selector));
    };

    capolagaSwal.buildSubmitConfig = function (form) {
        if (! capolagaSwal.shouldHandleForm(form)) {
            return null;
        }

        const httpMethod = (form.querySelector('input[name="_method"]')?.value || form.getAttribute('method') || 'GET')
            .toUpperCase();

        const inlineConfirm = form.dataset.swalInlineConfirm || form.getAttribute('onsubmit') || '';
        const inlineMatch = inlineConfirm.match(/confirm\((['"])(.*?)\1\)/);
        const customAction = form.dataset.swalAction;

        let action = customAction;
        if (! action) {
            if (httpMethod === 'DELETE' || inlineMatch) {
                action = 'delete';
            } else if (httpMethod === 'PUT' || httpMethod === 'PATCH') {
                action = 'update';
            } else {
                action = 'save';
            }
        }

        const defaults = {
            save: {
                confirmTitle: 'Simpan data?',
                confirmText: 'Perubahan data akan langsung disimpan.',
                confirmButtonText: 'Ya, simpan',
                loadingTitle: 'Saving...',
                loadingText: 'Mohon tunggu, data sedang diproses.'
            },
            update: {
                confirmTitle: 'Update data?',
                confirmText: 'Perubahan data akan langsung disimpan.',
                confirmButtonText: 'Ya, update',
                loadingTitle: 'Updating...',
                loadingText: 'Mohon tunggu, data sedang diproses.'
            },
            delete: {
                confirmTitle: 'Hapus data?',
                confirmText: 'Data yang dihapus tidak bisa dikembalikan.',
                confirmButtonText: 'Ya, hapus',
                loadingTitle: 'Deleting...',
                loadingText: 'Mohon tunggu, data sedang diproses.'
            }
        };

        const selected = defaults[action] || defaults.save;

        return {
            method: httpMethod,
            confirmTitle: form.dataset.swalConfirmTitle || selected.confirmTitle,
            confirmText: form.dataset.swalConfirmText || inlineMatch?.[2] || selected.confirmText,
            confirmButtonText: form.dataset.swalConfirmButton || selected.confirmButtonText,
            cancelButtonText: form.dataset.swalCancelButton || 'Batal',
            loadingTitle: form.dataset.swalLoadingTitle || selected.loadingTitle,
            loadingText: form.dataset.swalLoadingText || selected.loadingText
        };
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener('pageshow', function (event) {
            if (! event.persisted) {
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.close();
            }

            document.querySelectorAll('form[data-swal-confirmed="true"]').forEach((form) => {
                delete form.dataset.swalConfirmed;

                form.querySelectorAll('[type="submit"]').forEach((button) => {
                    button.disabled = false;
                });
            });
        });

        document.addEventListener('submit', function (event) {
            const form = event.target;

            const config = capolagaSwal.buildSubmitConfig(form);

            if (! config) {
                return;
            }

            const inlineConfirm = form.getAttribute('onsubmit') || '';

            if (inlineConfirm.includes('confirm(')) {
                form.dataset.swalInlineConfirm = inlineConfirm;
                form.removeAttribute('onsubmit');
            }

            event.preventDefault();

            if (typeof Swal === 'undefined') {
                form.dataset.swalConfirmed = 'true';
                form.submit();
                return;
            }

            Swal.fire({
                title: config.confirmTitle,
                text: config.confirmText,
                icon: config.method === 'DELETE' ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: config.confirmButtonText,
                cancelButtonText: config.cancelButtonText,
                reverseButtons: true,
                confirmButtonColor: config.method === 'DELETE' ? '#dc3545' : '#1f8fff',
                cancelButtonColor: '#6d7a86',
                showClass: {
                    popup: 'capolaga-swal-show'
                },
                hideClass: {
                    popup: 'capolaga-swal-hide'
                }
            }).then((result) => {
                if (! result.isConfirmed) {
                    return;
                }

                form.dataset.swalConfirmed = 'true';
                form.querySelectorAll('[type="submit"]').forEach((button) => {
                    button.disabled = true;
                });
                capolagaSwal.showLoading(config.loadingTitle, config.loadingText);
                form.submit();
            });
        }, true);
    });
</script>

@stack('scripts')
</body>
</html>
