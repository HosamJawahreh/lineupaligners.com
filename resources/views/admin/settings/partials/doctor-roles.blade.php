@php
    $groups = config('doctor-permissions.groups', []);
    $permissions = config('doctor-permissions.permissions', []);
    $permissionsByGroup = collect($permissions)->groupBy('group');
    $roleErrors = $errors->getBag('default');
    $hasRoleFormErrors = $roleErrors->isNotEmpty() && (
        $roleErrors->has('name')
        || $roleErrors->has('permissions')
        || $roleErrors->has('permissions.*')
        || $roleErrors->has('description')
    );
@endphp

<div class="tab-pane" id="tab-doctor-roles" role="tabpanel">
    <div class="doctor-roles-intro">
        <p class="settings-section-title m-b-5">Doctor Roles</p>
        <p class="text-muted doctor-roles-intro__text">Define what each doctor type can do across the LineUp case workflow. Assign a role when adding or editing a doctor.</p>
    </div>

    @if($hasRoleFormErrors)
    <div class="doctor-roles-alert doctor-roles-alert--error" role="alert">
        <i class="zmdi zmdi-alert-circle" aria-hidden="true"></i>
        <div>
            <strong>Could not save role.</strong>
            <ul class="m-b-0">
                @foreach($roleErrors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="doctor-roles-layout">
        <aside class="doctor-roles-create">
            <div class="doctor-role-card doctor-role-card--create">
                <div class="doctor-role-card__head">
                    <span class="doctor-role-card__icon" aria-hidden="true"><i class="zmdi zmdi-plus-circle"></i></span>
                    <div>
                        <h6 class="m-b-0">Add Role</h6>
                        <p class="doctor-role-card__sub m-b-0">Create a new permission profile</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('doctor-roles.store') }}" class="doctor-role-form">
                    @csrf
                    <div class="form-group">
                        <label for="new-role-name">Role Name</label>
                        <input type="text" name="name" id="new-role-name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Orthodontist" required>
                    </div>
                    <div class="form-group">
                        <label for="new-role-description">Description</label>
                        <textarea name="description" id="new-role-description" class="form-control" rows="2" placeholder="Optional notes about this role">{{ old('description') }}</textarea>
                    </div>

                    @include('admin.settings.partials._doctor-role-permission-fields', [
                        'prefix' => 'new',
                        'selected' => [],
                        'groups' => $groups,
                        'permissionsByGroup' => $permissionsByGroup,
                    ])

                    <div class="doctor-role-active">
                        <input type="hidden" name="is_active" value="0">
                        <label class="doctor-role-active__label" for="new-role-active">
                            <input type="checkbox" name="is_active" id="new-role-active" value="1" @checked(!session()->getOldInput() || filter_var(old('is_active', true), FILTER_VALIDATE_BOOLEAN))>
                            <span>Active role</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-round doctor-role-card__submit">
                        <i class="zmdi zmdi-plus m-r-5"></i> Create Role
                    </button>
                </form>
            </div>
        </aside>

        <div class="doctor-roles-list">
            @forelse($doctorRoles as $role)
                @php
                    $normalizedPermissions = \App\Models\DoctorRole::normalizePermissions($role->permissions ?? []);
                    $isEditingThisRole = (string) old('_role_id') === (string) $role->id;
                @endphp
                <article class="doctor-role-card @if(! $role->is_active) doctor-role-card--inactive @endif">
                    <form method="POST" action="{{ route('doctor-roles.update', $role) }}" class="doctor-role-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_role_id" value="{{ $role->id }}">

                        <header class="doctor-role-card__head">
                            <div class="doctor-role-card__identity">
                                <span class="doctor-role-card__icon doctor-role-card__icon--role" aria-hidden="true">
                                    <i class="zmdi zmdi-assignment-account"></i>
                                </span>
                                <div>
                                    <h6 class="m-b-0">{{ $role->name }}</h6>
                                    <p class="doctor-role-card__sub m-b-0">
                                        {{ $role->doctors_count ?? 0 }} doctor{{ ($role->doctors_count ?? 0) === 1 ? '' : 's' }} assigned
                                    </p>
                                </div>
                            </div>
                            <span class="doctor-role-status @if($role->is_active) doctor-role-status--active @else doctor-role-status--inactive @endif">
                                {{ $role->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </header>

                        <div class="doctor-role-card__body">
                            <div class="form-group">
                                <label for="role-name-{{ $role->id }}">Role Name</label>
                                <input type="text"
                                       name="name"
                                       id="role-name-{{ $role->id }}"
                                       class="form-control @if($isEditingThisRole && $roleErrors->has('name')) is-invalid @endif"
                                       value="{{ (string) old('_role_id') === (string) $role->id ? old('name', $role->name) : $role->name }}"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="role-description-{{ $role->id }}">Description</label>
                                <textarea name="description"
                                          id="role-description-{{ $role->id }}"
                                          class="form-control"
                                          rows="2"
                                          placeholder="Optional">{{ (string) old('_role_id') === (string) $role->id ? old('description', $role->description) : $role->description }}</textarea>
                            </div>

                            @include('admin.settings.partials._doctor-role-permission-fields', [
                                'prefix' => 'role-'.$role->id,
                                'roleId' => $role->id,
                                'selected' => $normalizedPermissions,
                                'groups' => $groups,
                                'permissionsByGroup' => $permissionsByGroup,
                            ])

                            <div class="doctor-role-active">
                                <input type="hidden" name="is_active" value="0">
                                <label class="doctor-role-active__label" for="role-active-{{ $role->id }}">
                                    @php
                                        $isActiveChecked = (string) old('_role_id') === (string) $role->id
                                            ? filter_var(old('is_active', $role->is_active), FILTER_VALIDATE_BOOLEAN)
                                            : $role->is_active;
                                    @endphp
                                    <input type="checkbox" name="is_active" id="role-active-{{ $role->id }}" value="1" @checked($isActiveChecked)>
                                    <span>Active role</span>
                                </label>
                            </div>
                        </div>

                        <footer class="doctor-role-card__foot">
                            <button type="submit" class="btn btn-primary btn-round btn-sm doctor-role-card__submit">
                                <i class="zmdi zmdi-check m-r-5"></i> Save Changes
                            </button>
                        </footer>
                    </form>

                    @if(($role->doctors_count ?? 0) === 0)
                    <form method="POST"
                          action="{{ route('doctor-roles.destroy', $role) }}"
                          class="doctor-role-delete-form"
                          data-role-name="{{ $role->name }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-round btn-sm btn-simple doctor-role-delete-btn">
                            <i class="zmdi zmdi-delete m-r-5"></i> Delete Role
                        </button>
                    </form>
                    @endif
                </article>
            @empty
                <div class="doctor-role-card doctor-role-card--empty text-center">
                    <i class="zmdi zmdi-assignment-account doctor-role-card__empty-icon" aria-hidden="true"></i>
                    <h6 class="m-b-5">No doctor roles yet</h6>
                    <p class="m-b-0 text-muted">Create your first role using the form on the left.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('settings-scripts')
<script>
$(function () {
    @if($hasRoleFormErrors || request('tab') === 'doctor-roles')
    $('.settings-page .nav-tabs a[href="#tab-doctor-roles"]').tab('show');
    @endif

    $('.doctor-role-delete-form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var name = form.getAttribute('data-role-name');
        if (confirm('Delete role "' + name + '"? This cannot be undone.')) {
            form.submit();
        }
    });
});
</script>
@endpush
