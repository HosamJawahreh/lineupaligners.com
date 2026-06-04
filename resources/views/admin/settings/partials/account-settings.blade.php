@php
    $prefix = $prefix ?? 'settings';
    $bool = fn (string $key) => filter_var($settings[$key] ?? '0', FILTER_VALIDATE_BOOLEAN);
@endphp
<h6>Account Settings</h6>
<ul class="setting-list list-unstyled m-b-0">
    @foreach([
        'offline' => 'Offline',
        'location_permission' => 'Location Permission',
    ] as $key => $label)
        <li>
            <div class="checkbox">
                <input id="{{ $prefix }}-{{ $key }}" name="{{ $key }}" type="checkbox" value="1" form="settings-form" @checked(old($key, $bool($key)))>
                <label for="{{ $prefix }}-{{ $key }}">{{ $label }}</label>
            </div>
        </li>
    @endforeach
</ul>
