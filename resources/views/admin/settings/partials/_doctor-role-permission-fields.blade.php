@php
    $prefix = $prefix ?? 'new';
    $roleId = $roleId ?? null;
    $selected = $selected ?? [];
    $permissions = $permissions ?? config('doctor-permissions.permissions', []);
    $groups = $groups ?? config('doctor-permissions.groups', []);
    $checkedPermissions = $selected;

    if (! $roleId && session()->getOldInput()) {
        $checkedPermissions = old('permissions', []);
    } elseif ($roleId && (string) old('_role_id') === (string) $roleId) {
        $checkedPermissions = old('permissions', []);
    }
@endphp

<div class="doctor-role-permissions">
    @foreach($groups as $groupKey => $groupLabel)
        @php
            $groupPermissions = array_filter(
                $permissions,
                fn ($meta) => ($meta['group'] ?? '') === $groupKey
            );
        @endphp
        @if(count($groupPermissions) > 0)
        <section class="doctor-role-perm-group">
            <h4 class="doctor-role-perm-group__title">{{ $groupLabel }}</h4>
            <div class="doctor-role-perm-grid">
                @foreach($groupPermissions as $permKey => $meta)
                <label class="doctor-role-perm" for="{{ $prefix }}-perm-{{ $permKey }}" title="{{ $meta['hint'] ?? '' }}">
                    <input type="checkbox"
                           name="permissions[]"
                           id="{{ $prefix }}-perm-{{ $permKey }}"
                           value="{{ $permKey }}"
                           class="doctor-role-perm__input"
                           @checked(in_array($permKey, $checkedPermissions, true))>
                    <span class="doctor-role-perm__box" aria-hidden="true">
                        <i class="zmdi zmdi-check"></i>
                    </span>
                    <span class="doctor-role-perm__text">
                        <span class="doctor-role-perm__label">{{ $meta['label'] }}</span>
                        @if(! empty($meta['hint']))
                        <span class="doctor-role-perm__hint">{{ $meta['hint'] }}</span>
                        @endif
                    </span>
                </label>
                @endforeach
            </div>
        </section>
        @endif
    @endforeach
</div>
