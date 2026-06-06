{{-- Live Chat & Files tab --}}
<div class="ig-chat ig-chat--case-tab @if(!$canCaseChat) ig-chat--disabled @endif"
     id="case-chat"
     data-messages-url="{{ route('patients.messages.index', $patient) }}"
     data-send-url="{{ route('patients.messages.store', $patient) }}"
     data-csrf="{{ csrf_token() }}"
     data-poll-ms="3500"
     data-can-chat="{{ $canCaseChat && $chatDoctorName ? '1' : '0' }}"
     data-seen-msg-id="{{ $latestSeenOwnMessageId ?? 0 }}">
    <header class="ig-chat-toolbar ig-chat-toolbar--light">
        <div class="ig-chat-toolbar-left">
            <h2 class="ig-chat-title">Live Chat &amp; Files</h2>
            @if($canCaseChat && $chatDoctorName)
            <div class="ig-participants">
                <div class="ig-participant-pill">
                    <span class="ig-participant-avatar">
                        <img src="{{ $chatCounterparty['avatar'] }}" alt="">
                    </span>
                    <span class="ig-participant-text">
                        <span class="ig-participant-name">
                            @if(auth()->user()->isAdmin() && ($chatParticipants['doctor'] ?? null))
                                Dr. {{ $chatCounterparty['name'] }}
                            @else
                                {{ $chatCounterparty['name'] }}
                            @endif
                        </span>
                        <span class="ig-participant-role">{{ $chatCounterparty['role'] }}</span>
                    </span>
                </div>
            </div>
            @endif
        </div>
        <span class="ig-chat-live" id="case-chat-live" title="Live">
            <span class="ig-chat-live-dot"></span> Live
        </span>
    </header>

    @if($canCaseChat && $chatDoctorName)
    <div class="ig-chat-thread" id="case-chat-messages" aria-live="polite">
        @php $chatMessages = $patient->relationLoaded('caseMessages') ? $patient->caseMessages : collect(); @endphp
        @forelse($chatMessages as $message)
            @include('theme.pages.partials.case-chat-message', [
                'message' => $message,
                'patient' => $patient,
                'logoUrl' => $logoUrl,
                'latestSeenOwnMessageId' => $latestSeenOwnMessageId ?? 0,
            ])
        @empty
        <div class="ig-chat-empty" id="case-chat-empty">
            <span class="ig-chat-empty__icon"><i class="zmdi zmdi-comment-text"></i></span>
            <p>No messages yet</p>
            <span class="ig-chat-empty__hint">Say hello or attach a case file to get started.</span>
        </div>
        @endforelse
    </div>

    <div class="ig-compose-preview d-none" id="case-chat-preview">
        <div class="ig-compose-preview__thumb" id="case-chat-preview-thumb" aria-hidden="true"></div>
        <div class="ig-compose-preview__info">
            <span class="ig-compose-preview__name" id="case-chat-preview-name"></span>
            <span class="ig-compose-preview__meta" id="case-chat-preview-meta"></span>
        </div>
        <button type="button" class="ig-compose-preview__remove" id="case-chat-preview-remove" aria-label="Remove attachment">
            <i class="zmdi zmdi-close"></i>
        </button>
    </div>

    <form class="ig-chat-compose" id="case-chat-form" enctype="multipart/form-data" autocomplete="off">
        <input type="file" id="case-chat-file" name="attachment" class="d-none" accept="*/*">
        <button type="button" class="ig-chat-attach" id="case-chat-attach-btn" title="Attach a file (max 25 MB)">
            <i class="zmdi zmdi-attachment-alt"></i>
        </button>
        <div class="ig-chat-input-wrap">
            <input type="text" name="body" id="case-chat-input" class="ig-chat-input"
                   placeholder="Type a message…"
                   maxlength="5000">
        </div>
        <button type="submit" class="ig-chat-send" aria-label="Send message">
            <i class="zmdi zmdi-mail-send"></i>
        </button>
    </form>
    @else
    <p class="ig-chat-unavailable">Assign a doctor to this case to enable private chat with the administrator.</p>
    @endif
</div>
