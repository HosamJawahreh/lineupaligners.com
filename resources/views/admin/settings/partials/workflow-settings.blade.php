@php
    $scanRequirement = old('scan_requirement', $settings['scan_requirement'] ?? 'optional');
@endphp

<div class="tab-pane" id="tab-workflow" role="tabpanel">
    <p class="settings-section-title">Case Workflow</p>
    <p class="text-muted m-b-25">Configure how cases move through the LineUp pipeline and what doctors must provide when submitting cases.</p>

    <div class="settings-panel-grid">
        <div class="inner-card">
            <h6>3D scan requirements</h6>
            <p class="text-muted small m-b-15">Applied when doctors or admins create or edit patient cases.</p>
            @foreach(config('settings.scan_requirements', []) as $key => $label)
                <label class="btn btn-default btn-round btn-block @if($scanRequirement === $key) active @endif m-b-10">
                    <input type="radio" name="scan_requirement" value="{{ $key }}" class="d-none" form="settings-form" @checked($scanRequirement === $key)>
                    {{ $label }}
                </label>
            @endforeach
        </div>

        <div class="inner-card">
            <h6>Workflow stages</h6>
            <p class="text-muted small m-b-15">Case lifecycle managed by the system (admin vs assigned doctor).</p>
            <ol class="settings-workflow-stages m-b-0 pl-3">
                <li><strong>Case created</strong> — Doctor submits case with patient data &amp; scans</li>
                <li><strong>Treatment plan</strong> — LineUp admin uploads manufacture plan</li>
                <li><strong>Modification</strong> — Optional step between plan and review. Doctor may request changes (new scans); LineUp uploads a revised plan before approval.</li>
                <li><strong>Doctor review</strong> — Assigned doctor approves or rejects the plan (may repeat after modification)</li>
                <li><strong>Approved</strong> — All stages approved; ready for manufacturing</li>
                <li><strong>Manufactured</strong> — LineUp admin confirms physical production (closes modifications)</li>
                <li><strong>Refinement</strong> — Doctor orders refinement for returning patients</li>
            </ol>
            <p class="text-muted small m-t-15 m-b-0">The progress bar shows Modification between Treatment plan and Doctor review. If no modification is requested, that step is marked as skipped. Doctor role permissions control workflow actions; plan upload and manufacturing remain admin-only.</p>
        </div>
    </div>
</div>

@push('settings-scripts')
<script>
$(function () {
    $('#tab-workflow label.btn').on('click', function () {
        $(this).siblings('label.btn').removeClass('active');
        $(this).addClass('active');
        $(this).find('input[type=radio]').prop('checked', true);
    });
});
</script>
@endpush
