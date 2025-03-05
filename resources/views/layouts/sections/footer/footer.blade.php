@php
    $containerFooter =
        isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
            ? 'container-xxl'
            : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-white bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container  d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body d-flex justify-content-center w-100 ">
                ©
                <script>
                    document.write(new Date().getFullYear())
                </script> Lawyer
            </div>
            {{-- <div class="d-none d-lg-inline-block">
                <a href="{{ config('variables.licenseUrl') ? config('variables.licenseUrl') : '#' }}"
                    class="footer-link me-4" target="_blank">License</a>
                <a href="{{ config('variables.moreThemes') ? config('variables.moreThemes') : '#' }}" target="_blank"
                    class="footer-link me-4">More Themes</a>
                <a href="{{ config('variables.documentation') ? config('variables.documentation') . '/laravel-introduction.html' : '#' }}"
                    target="_blank" class="footer-link me-4">Documentation</a>
                <a href="{{ config('variables.support') ? config('variables.support') : '#' }}" target="_blank"
                    class="footer-link d-none d-sm-inline-block">Support</a>
            </div> --}}
        </div>
    </div>
</footer>
<!--/ Footer-->
