@php
    $href        = $href ?? null;
    $sub         = $sub ?? null;
    $delta       = $delta ?? null;
    $deltaSuffix = $deltaSuffix ?? '%';
    $deltaInvert = $deltaInvert ?? false;
@endphp
@if($href)<a href="{{ $href }}" style="display:block;text-decoration:none;color:inherit;">@endif
<div class="rpt-kpi" style="--kpi-color:{{ $color }};--kpi-bg:{{ $bg }};">
    <div class="rpt-kpi-icon"><i class="fas {{ $icon }}"></i></div>
    <div class="rpt-kpi-value">{{ $value }}</div>
    <div class="rpt-kpi-label">{{ $label }}</div>
    @if(!is_null($sub) && $sub !== '')
        <div class="rpt-kpi-sub">{{ $sub }}</div>
    @endif
    @if(!is_null($delta))
        @php
            $good = $deltaInvert ? $delta < 0 : $delta > 0;
            $cls  = $delta == 0 ? 'flat' : ($good ? 'up' : 'down');
            $icon = $delta > 0 ? 'arrow-up' : ($delta < 0 ? 'arrow-down' : 'minus');
            $abs  = rtrim(rtrim(number_format(abs($delta), 1), '0'), '.');
        @endphp
        <div class="rpt-kpi-delta {{ $cls }}">
            <i class="fas fa-{{ $icon }}"></i>
            <span style="color:inherit;font-weight:700;">{{ $abs }}{{ $deltaSuffix }}</span>
            <span>عن الفترة السابقة</span>
        </div>
    @endif
</div>
@if($href)</a>@endif
