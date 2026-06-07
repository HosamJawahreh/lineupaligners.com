@php
    $stageNumber = $stageNumber ?? null;
    $uploadId = 'modification-inline-'.($stageNumber ?? 'full');
@endphp

<div class="mfg-plan__mod-inline case-modification case-modification--inline">
    <p class="mfg-plan__mod-inline-lead">
        <i class="zmdi zmdi-refresh-sync" aria-hidden="true"></i>
        Prefer changes before approving? Upload revised 3D scans and notes — LineUp will submit an updated plan for this stage.
    </p>
    <form method="post"
          action="{{ route('patients.modifications.store', $patient) }}"
          class="case-modification__form case-modification__form--inline"
          enctype="multipart/form-data"
          data-scan-upload>
        @csrf
        @if($stageNumber !== null)
        <input type="hidden" name="stage_number" value="{{ $stageNumber }}">
        @endif

        <div class="case-modification__uploads">
            @include('theme.pages.partials.case-photos-upload', ['uploadId' => $uploadId])
            <div class="case-modification__field">
                <label for="{{ $uploadId }}-upper">Upper jaw 3D file</label>
                <input type="file" id="{{ $uploadId }}-upper" name="upper_jaw_scan" accept=".stl,.obj,.ply">
            </div>
            <div class="case-modification__field">
                <label for="{{ $uploadId }}-lower">Lower jaw 3D file</label>
                <input type="file" id="{{ $uploadId }}-lower" name="lower_jaw_scan" accept=".stl,.obj,.ply">
            </div>
        </div>

        <div class="case-modification__field">
            <label for="{{ $uploadId }}-notes">Modification notes</label>
            <textarea id="{{ $uploadId }}-notes"
                      name="notes"
                      rows="4"
                      required
                      minlength="10"
                      maxlength="10000"
                      placeholder="Describe what should change in the treatment plan…">{{ old('stage_number') == $stageNumber ? old('notes') : '' }}</textarea>
        </div>

        <button type="submit" class="case-modification__submit case-modification__submit--inline">
            <i class="zmdi zmdi-upload" aria-hidden="true"></i>
            Request modification instead
        </button>
    </form>
</div>
