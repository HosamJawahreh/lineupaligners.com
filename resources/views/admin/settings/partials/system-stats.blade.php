@php
    $stats = $systemStats ?? ['memory_mb' => 0, 'cpu_percent' => 0, 'daily_traffic' => 0, 'disk_percent' => 0];
@endphp
<h6>Information Summary</h6>
<div class="row m-b-15">
    <div class="col-7">
        <small class="displayblock text-muted">MEMORY USAGE</small>
        <h5 class="m-b-0 h6">{{ $stats['memory_mb'] }} MB</h5>
    </div>
    <div class="col-5">
        <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="5" data-bar-Spacing="3" data-bar-Color="#00ced1">8,7,9,5,6,4,6,8</div>
    </div>
</div>
<div class="row m-b-15">
    <div class="col-7">
        <small class="displayblock text-muted">CPU USAGE</small>
        <h5 class="m-b-0 h6">{{ $stats['cpu_percent'] }}%</h5>
    </div>
    <div class="col-5">
        <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="5" data-bar-Spacing="3" data-bar-Color="#F15F79">6,5,8,2,6,4,6,4</div>
    </div>
</div>
<div class="row m-b-15">
    <div class="col-7">
        <small class="displayblock text-muted">DAILY TRAFFIC</small>
        <h5 class="m-b-0 h6">{{ number_format($stats['daily_traffic']) }}</h5>
    </div>
    <div class="col-5">
        <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="5" data-bar-Spacing="3" data-bar-Color="#78b83e">7,5,8,7,4,2,6,5</div>
    </div>
</div>
<div class="row">
    <div class="col-7">
        <small class="displayblock text-muted">DISK USAGE</small>
        <h5 class="m-b-0 h6">{{ $stats['disk_percent'] }}%</h5>
    </div>
    <div class="col-5">
        <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="5" data-bar-Spacing="3" data-bar-Color="#457fca">7,5,2,5,6,7,6,4</div>
    </div>
</div>
