@php
    $prefix = $prefix ?? 'new';
    $roleId = $roleId ?? null;
    $selected = $selected ?? [];
    $checkedPermissions = $selected;

    if (! $roleId && session()->getOldInput()) {
        $checkedPermissions = old('permissions', []);
    } elseif ($roleId && (string) old('_role_id') === (string) $roleId) {
        $checkedPermissions = old('permissions', []);
    }
@endphp

<div class="doctor-role-permissions">
    @foreach($groups as $groupKey => $groupLabel)
        @if($permissionsByGroup->has($groupKey))
        <section class="doctor-role-perm-group">
            <h4 class="doctor-role-perm-group__title">{{ $groupLabel }}</h4>
            <div class="doctor-role-perm-grid">
                @foreach($permissionsByGroup[$groupKey] as $key => $meta)
                <label class="doctor-role-perm" for="{{ $prefix }}-perm-{{ $key }}" title="{{ $meta['hint'] ?? '' }}">
                    <input type="checkbox"
                           name="permissions[]"
                           id="{{ $prefix }}-perm-{{ $key }}"
                           value="{{ $key }}"
                           class="doctor-role-perm__input"
                           @checked(in_array($key, $checkedPermissions, true))>
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
