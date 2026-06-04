@extends('layouts.app')

@section('title', 'Doctors')

@section('content')
<section class="content cases-dashboard lineup-page">
    <div class="container-fluid">
        <div class="cases-panel">
            <div class="cases-panel-head">
                <h1>Doctors</h1>
                <div class="cases-panel-actions">
                    <a href="{{ route('doctors.create') }}" class="cases-btn cases-btn-primary">
                        <i class="zmdi zmdi-plus"></i> Add Doctor
                    </a>
                </div>
            </div>

            <div class="cases-table-wrap p-3">
                <table class="lineup-datatable table table-hover w-100" id="doctors-table"
                       data-order-col="0" data-order-dir="asc" data-no-sort-columns="6" data-page-length="20">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Specialty</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctors as $doctor)
                        <tr>
                            <td>
                                <strong>Dr. {{ $doctor->fullName() }}</strong>
                            </td>
                            <td>{{ $doctor->specialty ?? 'Orthodontist' }}</td>
                            <td>{{ $doctor->doctorRole?->name ?? '—' }}</td>
                            <td>{{ $doctor->phone ?? '—' }}</td>
                            <td>{{ $doctor->email ?? '—' }}</td>
                            <td>
                                <span class="cases-badge {{ $doctor->is_active ? 'cases-badge-active' : 'cases-badge-canceled' }}">
                                    {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="cases-actions justify-content-end">
                                    <a href="{{ route('doctors.show', $doctor) }}" class="cases-action-btn" title="View">
                                        <i class="zmdi zmdi-eye"></i>
                                    </a>
                                    <a href="{{ route('doctors.edit', $doctor) }}" class="cases-action-btn" title="Edit">
                                        <i class="zmdi zmdi-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">No doctors found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    if (window.LineUpInitDataTables) {
        window.LineUpInitDataTables('#doctors-table');
    }
});
</script>
@endpush
