@php
    $isDoctorCard = $doctor ?? null;
@endphp

@if($isDoctorCard)
<article class="cases-mobile-card cases-mobile-card--doctor">
    <div class="cases-mobile-card__head">
        <div class="cases-mobile-card__main">
            <a href="{{ route('doctors.show', $doctor) }}" class="cases-mobile-card__name">Dr. {{ $doctor->fullName() }}</a>
            <div class="cases-mobile-card__meta">
                <span class="cases-badge {{ $doctor->is_active ? 'cases-badge-active' : 'cases-badge-canceled' }}">
                    {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span>{{ $doctor->doctorRole?->name ?? '—' }}</span>
            </div>
        </div>
    </div>
    <div class="cases-mobile-card__body">
        <div class="cases-mobile-card__field">
            <span class="cases-mobile-card__label">Specialty</span>
            <span class="cases-mobile-card__value">{{ $doctor->specialty ?? 'Orthodontist' }}</span>
        </div>
        <div class="cases-mobile-card__field">
            <span class="cases-mobile-card__label">Phone</span>
            <span class="cases-mobile-card__value">{{ $doctor->phone ?? '—' }}</span>
        </div>
        <div class="cases-mobile-card__field cases-mobile-card__field--wide">
            <span class="cases-mobile-card__label">Email</span>
            <span class="cases-mobile-card__value">{{ $doctor->email ?? '—' }}</span>
        </div>
    </div>
    <div class="cases-mobile-card__foot">
        <a href="{{ route('doctors.show', $doctor) }}" class="cases-mobile-card__cta">
            <i class="zmdi zmdi-eye"></i>
            <span>View</span>
        </a>
        <div class="cases-mobile-card__actions">
            <a href="{{ route('doctors.edit', $doctor) }}" class="cases-action-btn" title="Edit">
                <i class="zmdi zmdi-edit"></i>
            </a>
        </div>
    </div>
</article>
@else
<article class="cases-mobile-card">
    <div class="cases-mobile-card__head">
        <img src="{{ $patient->caseAvatarUrl() }}" alt="" class="cases-mobile-card__avatar" width="44" height="44">
        <div class="cases-mobile-card__main">
            <a href="{{ route('patients.show', $patient) }}" class="cases-mobile-card__name">{{ $patient->fullName() }}</a>
            <div class="cases-mobile-card__meta">
                <span class="cases-mobile-card__case-id">#{{ $patient->display_patient_id }}</span>
                <span class="cases-type-pill {{ $patient->case_type ?? 'full_case' }}">
                    <i class="zmdi {{ $patient->caseTypeIcon() }}"></i>
                    {{ $patient->caseTypeLabel() }}
                </span>
            </div>
        </div>
    </div>
    <div class="cases-mobile-card__body">
        @if($isAdmin ?? false)
        <div class="cases-mobile-card__field">
            <span class="cases-mobile-card__label">Doctor</span>
            <span class="cases-mobile-card__value">Dr. {{ $patient->doctor?->fullName() ?? '—' }}</span>
        </div>
        @endif
        <div class="cases-mobile-card__field">
            <span class="cases-mobile-card__label">Added</span>
            <span class="cases-mobile-card__value">{{ $patient->created_at?->format('M j, Y') ?? '—' }}</span>
        </div>
        @if($patient->phone)
        <div class="cases-mobile-card__field cases-mobile-card__field--wide">
            <span class="cases-mobile-card__label">Phone</span>
            <span class="cases-mobile-card__value">{{ $patient->phone }}</span>
        </div>
        @endif
    </div>
    <div class="cases-mobile-card__foot">
        <span class="cases-mobile-card__workflow {{ $patient->workflowBadgeClass() }}">
            {{ $patient->workflowStageLabel() }}
        </span>
        <div class="cases-mobile-card__actions">
            <a href="{{ route('patients.show', $patient) }}" class="cases-mobile-card__cta">
                <i class="zmdi zmdi-eye"></i>
                <span>View</span>
            </a>
        </div>
    </div>
</article>
@endif
