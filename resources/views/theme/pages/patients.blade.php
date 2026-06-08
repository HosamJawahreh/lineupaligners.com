@extends('layouts.app')

@section('title', 'Cases')

@php
    $sortLink = function (string $column) use ($filters) {
        $dir = ($filters['sort'] === $column && $filters['dir'] === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $column, 'dir' => $dir, 'page' => 1]);
    };
    $exportQuery = request()->except(['page']);
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cases-dashboard.css') }}">
@endpush

@section('content')
<section class="content cases-dashboard">
    <div class="container-fluid">
        <div class="cases-panel">
            <div class="cases-panel-head">
                <h1>{{ $isAdmin ? 'Cases' : 'My Cases' }}</h1>
                <div class="cases-head-actions">
                    <a href="{{ route('patients.export', $exportQuery) }}" class="cases-btn cases-btn-outline">
                        <i class="zmdi zmdi-download"></i>
                        <span>Export</span>
                    </a>
                    @if($isAdmin || auth()->user()->isDoctor())
                    <a href="{{ route('patients.create') }}" class="cases-btn cases-btn-primary">
                        <i class="zmdi zmdi-plus"></i>
                        <span>New Case</span>
                    </a>
                    @endif
                </div>
            </div>

            <div class="cases-tabs-bar">
                <ul class="cases-status-tabs">
                    @foreach($statusTabs as $value => $label)
                    <li @class(['active' => (string) $filters['status'] === (string) $value])>
                        <a href="{{ request()->fullUrlWithQuery(['status' => $value, 'page' => 1]) }}">{{ $label }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>

            @php
                $activeFilterCount = collect($filters)
                    ->except(['status', 'sort', 'dir', 'per_page'])
                    ->filter(fn ($value) => filled($value))
                    ->count();
            @endphp

            <form method="GET" action="{{ route('patients.index') }}" class="cases-filters" id="cases-filter-form">
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
                <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                <input type="hidden" name="dir" value="{{ $filters['dir'] }}">

                <div class="cases-filters-mobile-bar">
                    <button type="button"
                            class="cases-filters-toggle"
                            id="cases-filters-toggle"
                            aria-expanded="false"
                            aria-controls="cases-filters-panel">
                        <i class="zmdi zmdi-filter-list" aria-hidden="true"></i>
                        <span>Filters</span>
                        @if($activeFilterCount > 0)
                        <span class="cases-filters-toggle__count">{{ $activeFilterCount }}</span>
                        @endif
                    </button>
                    @if($activeFilterCount > 0)
                    <span class="cases-filters-active-hint">{{ $activeFilterCount }} active filter{{ $activeFilterCount === 1 ? '' : 's' }}</span>
                    @endif
                </div>

                <div class="cases-filters-row" id="cases-filters-panel">
                    <div class="cases-field cases-field-search">
                        <i class="zmdi zmdi-search" aria-hidden="true"></i>
                        <input type="text" name="patient" class="form-control" value="{{ $filters['patient'] }}" placeholder="Patient Name">
                    </div>

                    @if($isAdmin)
                    <div class="cases-field cases-field-search">
                        <i class="zmdi zmdi-search" aria-hidden="true"></i>
                        <input type="text" name="doctor" class="form-control" value="{{ $filters['doctor'] }}" placeholder="Doctor's Name">
                    </div>
                    <div class="cases-field cases-field-search">
                        <i class="zmdi zmdi-search" aria-hidden="true"></i>
                        <input type="text" name="creator" class="form-control" value="{{ $filters['creator'] ?? '' }}" placeholder="Creator">
                    </div>
                    @endif

                    <div class="cases-field">
                        <select name="case_type" class="form-control" title="Case Type">
                            <option value="">Case Type</option>
                            @foreach($caseTypes as $value => $label)
                                <option value="{{ $value }}" @selected($filters['case_type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="cases-field cases-field-daterange">
                        <i class="zmdi zmdi-calendar" aria-hidden="true"></i>
                        <input type="date" name="date_from" class="form-control cases-date-from" value="{{ $filters['date_from'] }}" aria-label="From date">
                        <span class="cases-date-sep">-</span>
                        <input type="date" name="date_to" class="form-control cases-date-to" value="{{ $filters['date_to'] }}" aria-label="To date">
                    </div>

                    <div class="cases-filter-actions">
                        <a href="{{ route('patients.index', ['status' => $filters['status']]) }}" class="cases-btn cases-btn-outline">
                            <span>Reset</span>
                        </a>
                        <button type="submit" class="cases-btn cases-btn-primary">
                            <i class="zmdi zmdi-search"></i>
                            <span>Search</span>
                        </button>
                        <button type="button" class="cases-btn cases-btn-icon cases-view-toggle" title="List view" disabled>
                            <i class="zmdi zmdi-view-list"></i>
                        </button>
                    </div>
                </div>
            </form>

            <div class="cases-mobile-list" aria-label="Cases list">
                @forelse($patients as $patient)
                    @include('theme.pages.partials.cases-mobile-card', ['patient' => $patient, 'isAdmin' => $isAdmin])
                @empty
                    <div class="cases-mobile-empty">
                        <i class="zmdi zmdi-folder-outline" style="font-size: 40px; opacity: 0.4;"></i>
                        <p class="m-t-15 m-b-5"><strong>No cases found</strong></p>
                        <p class="m-b-0">Try adjusting your filters or create a new case.</p>
                    </div>
                @endforelse
            </div>

            <div class="cases-table-scroll">
            <div class="cases-table-wrap">
                <table class="cases-table lineup-datatable table table-hover w-100" id="cases-table"
                       data-order-col="6" data-order-dir="desc" data-no-sort-columns="8" data-page-length="20"
                       data-responsive="false">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ $sortLink('patient_id') }}">
                                    Case Number
                                    @if($filters['sort'] === 'patient_id')
                                        <i class="zmdi zmdi-chevron-{{ $filters['dir'] === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Doctor</th>
                            <th>Company</th>
                            <th>Case Type</th>
                            <th>Patient Name</th>
                            <th>Phone</th>
                            <th>
                                <a href="{{ $sortLink('created_at') }}">
                                    Added At
                                    @if($filters['sort'] === 'created_at')
                                        <i class="zmdi zmdi-chevron-{{ $filters['dir'] === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Workflow</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                        <tr>
                            <td><strong>{{ $patient->display_patient_id }}</strong></td>
                            <td>Dr. {{ $patient->doctor?->fullName() ?? '—' }}</td>
                            <td>{{ Str::limit($clinicName, 28) }}</td>
                            <td>
                                <span class="cases-type-pill {{ $patient->case_type ?? 'full_case' }}">
                                    <i class="zmdi {{ $patient->caseTypeIcon() }}"></i>
                                    {{ $patient->caseTypeLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="cases-patient-cell">
                                    <img src="{{ $patient->caseAvatarUrl() }}" alt="" class="cases-patient-avatar" width="40" height="40">
                                    <div>
                                        <a href="{{ route('patients.show', $patient) }}" class="name">{{ $patient->fullName() }}</a>
                                        @if($patient->email)
                                        <div class="sub">{{ $patient->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $patient->phone ?? '—' }}</td>
                            <td>
                                <span class="text-nowrap">{{ $patient->created_at?->format('Y/m/d H:i') ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="{{ $patient->workflowBadgeClass() }}" title="{{ $patient->workflowStageLabel() }}">{{ $patient->workflowStageLabel() }}</span>
                            </td>
                            <td>
                                <div class="cases-actions justify-content-end">
                                    @if($isAdmin && $patient->isReadyForManufacturedMark())
                                    <form method="POST" action="{{ route('patients.mark-manufactured', $patient) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="from_index" value="1">
                                        <button type="submit" class="cases-action-btn cases-action-btn--manufactured" title="Mark as manufactured">
                                            <i class="zmdi zmdi-check-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @if($patient->has3dScans())
                                        @if($patient->upper_jaw_scan && $patient->lower_jaw_scan)
                                    <div class="dropdown d-inline-block">
                                        <button type="button" class="cases-action-btn dropdown-toggle" data-toggle="dropdown" data-display="static" title="Download 3D files" aria-haspopup="true" aria-expanded="false">
                                            <i class="zmdi zmdi-download"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-left cases-dropdown-menu">
                                            <a href="{{ $patient->upperJawScanDownloadUrl() }}" download>{{ $patient->scanDisplayName($patient->upper_jaw_scan, 'upper_jaw_scan') }}</a>
                                            <a href="{{ $patient->lowerJawScanDownloadUrl() }}" download>{{ $patient->scanDisplayName($patient->lower_jaw_scan, 'lower_jaw_scan') }}</a>
                                        </div>
                                    </div>
                                        @elseif($patient->upper_jaw_scan)
                                    <a href="{{ $patient->upperJawScanUrl() }}" class="cases-action-btn" title="Download upper scan" download="{{ $patient->scanDownloadFilename('upper_jaw_scan') }}">
                                        <i class="zmdi zmdi-download"></i>
                                    </a>
                                        @else
                                    <a href="{{ $patient->lowerJawScanDownloadUrl() }}" class="cases-action-btn" title="Download lower scan" download="{{ $patient->scanDownloadFilename('lower_jaw_scan') }}">
                                        <i class="zmdi zmdi-download"></i>
                                    </a>
                                        @endif
                                    @else
                                    <span class="cases-action-btn disabled" title="No 3D files"><i class="zmdi zmdi-download"></i></span>
                                    @endif
                                    <a href="{{ route('patients.show', $patient) }}" class="cases-action-btn" title="View case">
                                        <i class="zmdi zmdi-eye"></i>
                                    </a>
                                    <div class="dropdown d-inline-block">
                                        <button type="button" class="cases-action-btn dropdown-toggle" data-toggle="dropdown" data-display="static" title="More" aria-haspopup="true" aria-expanded="false">
                                            <i class="zmdi zmdi-more-vert"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-left cases-dropdown-menu">
                                            <a href="{{ route('patients.edit', $patient) }}">Edit case</a>
                                            <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="cases-delete-form" data-name="{{ $patient->fullName() }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger">Delete case</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">
                                <div class="cases-empty">
                                    <i class="zmdi zmdi-folder-outline"></i>
                                    <p class="m-b-5"><strong>No cases found</strong></p>
                                    <p class="m-b-0">Try adjusting your filters or create a new patient case.</p>
                                    @if($isAdmin || auth()->user()->isDoctor())
                                    <a href="{{ route('patients.create') }}" class="cases-btn cases-btn-primary m-t-20">Create Case</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>

            <div class="cases-footer-meta m-t-10 px-3 pb-3">
                <span class="text-muted">Total <strong>{{ $patients->count() }}</strong> cases</span>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/cases-actions.js') }}"></script>
<script>
$(function () {
    $('.cases-delete-form').on('submit', function (e) {
        e.preventDefault();
        var name = this.getAttribute('data-name');
        if (confirm('Delete case for "' + name + '"? This cannot be undone.')) {
            this.submit();
        }
    });
    if (window.LineUpInitDataTables) {
        window.LineUpInitDataTables('#cases-table');
    }
    if (window.LineUpInitCasesActions) {
        window.LineUpInitCasesActions();
    }

    var $filterForm = $('#cases-filter-form');
    var $filterToggle = $('#cases-filters-toggle');
    if ($filterToggle.length) {
        $filterToggle.on('click', function () {
            var open = $filterForm.toggleClass('is-open').hasClass('is-open');
            $filterToggle.attr('aria-expanded', open ? 'true' : 'false');
        });
    }
});
</script>
@endpush
