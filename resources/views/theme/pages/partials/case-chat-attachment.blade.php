@php
    $downloadUrl = $message->attachmentDownloadUrl($patient);
    $thumbUrl = $message->attachmentPreviewUrl($patient);
    $ext = strtoupper($message->attachmentExtension());
    $size = $message->attachmentSizeLabel();
    $icon = $message->attachmentIconKind();
    $iconClasses = 'ig-attach-card__icon ig-attach-card__icon--'.$icon.($thumbUrl ? ' ig-attach-card__icon--has-thumb' : '');
@endphp
<div class="ig-bubble ig-bubble--attach">
    <a href="{{ $downloadUrl }}" class="ig-attach-card" download title="Download {{ $message->attachment_name }}">
        <span class="{{ $iconClasses }}" aria-hidden="true">
            @if($thumbUrl)
            <img src="{{ $thumbUrl }}" alt="" class="ig-attach-card__thumb" loading="lazy"
                 onerror="this.classList.add('is-broken');this.removeAttribute('src');">
            <i class="zmdi zmdi-image ig-attach-card__thumb-fallback" aria-hidden="true"></i>
            @elseif($icon === 'pdf')
            <i class="zmdi zmdi-collection-pdf"></i>
            @elseif($icon === 'word')
            <i class="zmdi zmdi-file-text"></i>
            @elseif($icon === 'sheet')
            <i class="zmdi zmdi-grid"></i>
            @elseif($icon === 'archive')
            <i class="zmdi zmdi-folder"></i>
            @elseif($icon === 'scan')
            <i class="zmdi zmdi-scanner"></i>
            @elseif($icon === 'image')
            <i class="zmdi zmdi-image"></i>
            @else
            <i class="zmdi zmdi-file"></i>
            @endif
            @if(!$thumbUrl)
            <span class="ig-attach-card__ext">{{ $ext }}</span>
            @endif
        </span>
        <span class="ig-attach-card__body">
            <span class="ig-attach-card__name">{{ $message->attachment_name }}</span>
            <span class="ig-attach-card__meta">
                {{ $size ? $size.' · ' : '' }}<span class="ig-attach-card__action-label">Download</span>
            </span>
        </span>
        <span class="ig-attach-card__dl" aria-hidden="true"><i class="zmdi zmdi-download"></i></span>
    </a>
</div>
