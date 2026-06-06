<div class="page-loader-wrapper lineup-page-loader">
    <div class="loader">
        <div class="lineup-ios-spinner" role="status" aria-live="polite" aria-label="{{ $loaderAriaLabel ?? __('website.loading') }}">
            @for ($i = 0; $i < 12; $i++)
            <span></span>
            @endfor
        </div>
    </div>
</div>
