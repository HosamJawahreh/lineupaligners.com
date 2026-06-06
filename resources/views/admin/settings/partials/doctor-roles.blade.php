@php
    $groups = config('doctor-permissions.groups', []);
    $permissions = config('doctor-permissions.permissions', []);
    $permissionsByGroup = collect($permissions)->groupBy('group');
@endphp

<div class="tab-pane" id="tab-doctor-roles" role="tabpanel">
    <p class="settings-section-title">Doctor Roles</p>
    <p class="text-muted m-b-25">Permissions follow the LineUp case workflow: submit cases → review plans → request modifications → order refinements. Assign a role when adding or editing a doctor.</p>

    <div class="row clearfix">
        <div class="col-lg-5 col-md-12 m-b-30">
            <div class="inner-card">
                <h6><i class="zmdi zmdi-plus-circle m-r-5"></i> Add Role</h6>
                <form method="POST" action="{{ route('doctor-roles.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Role Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Orthodontist" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional">{{ old('description') }}</textarea>
                    </div>
                    @foreach($groups as $groupKey => $groupLabel)
                        @if($permissionsByGroup->has($groupKey))
                        <div class="form-group">
                            <label class="d-block m-b-10">{{ $groupLabel }}</label>
                            @foreach($permissionsByGroup[$groupKey] as $key => $meta)
                                <div class="checkbox">
                                    <input type="checkbox" name="permissions[]" id="new-perm-{{ $key }}" value="{{ $key }}" @checked(in_array($key, old('permissions', [])))>
                                    <label for="new-perm-{{ $key }}" title="{{ $meta['hint'] ?? '' }}">{{ $meta['label'] }}</label>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    @endforeach
                    <div class="checkbox m-b-20">
                        <input type="checkbox" name="is_active" id="new-role-active" value="1" @checked(old('is_active', true))>
                        <label for="new-role-active">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-round">
                        <i class="zmdi zmdi-plus m-r-5"></i> Create Role
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7 col-md-12">
            @forelse($doctorRoles as $role)
                <div class="inner-card doctor-role-card m-b-20">
                    <form method="POST" action="{{ route('doctor-roles.update', $role) }}">
                        @csrf
                        @method('PUT')
                        <div class="d-flex justify-content-between align-items-start flex-wrap m-b-15">
                            <div>
                                <h6 class="m-b-5">{{ $role->name }}</h6>
                                <small class="text-muted">{{ $role->doctors_count }} doctor(s) assigned</small>
                            </div>
                            <span class="badge @if($role->is_active) badge-success @else badge-default @endif">
                                {{ $role->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Role Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name.'.$role->id, $role->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description.'.$role->id, $role->description) }}</textarea>
                        </div>
                        @foreach($groups as $groupKey => $groupLabel)
                            @if($permissionsByGroup->has($groupKey))
                            <div class="form-group">
                                <label class="d-block m-b-10">{{ $groupLabel }}</label>
                                <div class="row">
                                    @foreach($permissionsByGroup[$groupKey] as $key => $meta)
                                        <div class="col-md-6">
                                            <div class="checkbox">
                                                <input type="checkbox" name="permissions[]" id="role-{{ $role->id }}-{{ $key }}" value="{{ $key }}"
                                                    @checked(in_array($key, old('permissions.'.$role->id, $role->permissions ?? [])))>
                                                <label for="role-{{ $role->id }}-{{ $key }}" title="{{ $meta['hint'] ?? '' }}">{{ $meta['label'] }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                        <div class="checkbox m-b-15">
                            <input type="checkbox" name="is_active" id="role-active-{{ $role->id }}" value="1" @checked(old('is_active.'.$role->id, $role->is_active))>
                            <label for="role-active-{{ $role->id }}">Active</label>
                        </div>
                        <div class="d-flex flex-wrap gap-actions">
                            <button type="submit" class="btn btn-primary btn-round btn-sm">
                                <i class="zmdi zmdi-check m-r-5"></i> Update
                            </button>
                        </div>
                    </form>
                    @if($role->doctors_count === 0)
                        <form method="POST" action="{{ route('doctor-roles.destroy', $role) }}" class="doctor-role-delete-form m-t-10"
                              data-role-name="{{ $role->name }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-round btn-sm btn-simple">
                                <i class="zmdi zmdi-delete m-r-5"></i> Delete Role
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="inner-card text-center p-4">
                    <i class="zmdi zmdi-account-box zmdi-hc-3x text-muted m-b-15"></i>
                    <p class="m-b-0 text-muted">No doctor roles yet. Create one using the form on the left.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('settings-scripts')
<script>
$(function () {
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
