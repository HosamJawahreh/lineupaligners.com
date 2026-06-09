@if($patient->hasCaseDataZip())
<a href="{{ $patient->caseDataZipDownloadUrl() }}"
   class="case-data-zip-chip case-scan-file-card case-scan-file-card--inline"
   download
   title="Download case data archive">
    <span class="case-scan-file__body">
        <span class="case-scan-file__info">
            <span class="case-scan-file__icon" aria-hidden="true">
                <i class="zmdi zmdi-archive"></i>
            </span>
            <span class="case-scan-file__details">
                <span class="case-scan-file__name">Case Data Archive</span>
                @if($size = $patient->caseDataZipSizeLabel())
                <span class="case-scan-file__size">{{ $size }}</span>
                @endif
            </span>
        </span>
        <span class="case-scan-file__actions">
            <span class="case-scan-file__action case-scan-file__action--download" aria-hidden="true">
                <i class="zmdi zmdi-download"></i>
            </span>
        </span>
    </span>
</a>
@endif
