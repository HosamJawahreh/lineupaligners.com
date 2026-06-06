@php
    $isMine = $message->user_id === auth()->id();
    $chatAuthor = app(\App\Services\CaseChatContacts::class)->messageAuthor(
        $message->user,
        $patient,
        $logoUrl ?? asset('assets/images/logo.svg')
    );
    $avatarUrl = $chatAuthor['avatar'];
    $showSeen = $isMine
        && $message->read_at
        && isset($latestSeenOwnMessageId)
        && (int) $message->id === (int) $latestSeenOwnMessageId;
@endphp
<div class="ig-msg {{ $isMine ? 'ig-msg--mine' : 'ig-msg--theirs' }}" data-msg-id="{{ $message->id }}">
    @if(!$isMine)
    <div class="ig-msg-avatar">
        <img src="{{ $avatarUrl }}" alt="">
    </div>
    @endif
    <div class="ig-msg-stack">
        <div class="ig-msg-bubbles">
            @if($message->hasAttachment())
                @include('theme.pages.partials.case-chat-attachment', ['message' => $message, 'patient' => $patient])
            @endif
            @if(filled($message->body))
            <div class="ig-bubble ig-bubble--text">
                <p>{{ $message->body }}</p>
            </div>
            @endif
        </div>
        <div class="ig-msg-meta">
            @if($showSeen)
            <span class="ig-msg-seen">Seen</span>
            @endif
            <span class="ig-msg-time">{{ $message->created_at?->format('g:i A') }}</span>
        </div>
    </div>
</div>
