@if(($canMarkManufactured ?? false) && $patient->isReadyForManufacturedMark())
<div class="mfg-plan__mod-banner mfg-plan__mod-banner--manufactured mfg-plan__mod-banner--under-canvas" role="status">
    <i class="zmdi zmdi-check-circle" aria-hidden="true"></i>
    <div>
        <strong>Ready to mark as manufactured</strong>
        <p>The doctor approved the treatment plan{{ $patient->hasActiveRefinement() ? ' for this refinement cycle' : '' }}. Confirm manufacturing is complete to close modifications and allow refinement when the patient returns.</p>
        <form method="post" action="{{ route('patients.mark-manufactured', $patient) }}" class="mfg-plan__mark-form">
            @csrf
            <button type="submit" class="mfg-plan__btn mfg-plan__btn--manufactured">
                <i class="zmdi zmdi-check-circle"></i> Mark case as manufactured
            </button>
        </form>
    </div>
</div>
@elseif($patient->isManufactured())
<div class="mfg-plan__mod-banner mfg-plan__mod-banner--manufactured mfg-plan__mod-banner--under-canvas is-complete" role="status">
    <i class="zmdi zmdi-check-circle" aria-hidden="true"></i>
    <div>
        <strong>Manufactured · case cycle complete</strong>
        <p>
            @if($patient->manufactured_at)
                Marked {{ $patient->manufactured_at->format('M j, Y g:i A') }}.
            @else
                This case cycle is complete.
            @endif
            The doctor cannot request modifications; they may order refinement from the Order Refinement tab.
        </p>
    </div>
</div>
@endif
