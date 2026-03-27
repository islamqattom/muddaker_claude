{{-- resources/views/components/page-difficulty-heatmap.blade.php --}}
@props(['studentId', 'heatmapData' => []])

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-th me-2"></i>@lang('Page Difficulty Heatmap')</h5>
    </div>
    <div class="card-body">
        <div class="heatmap-grid mb-2" style="display: grid; grid-template-columns: repeat(20, 1fr); gap: 2px;">
            @for ($page = 1; $page <= 604; $page++)
                @php
                    $errorRate = $heatmapData[$page] ?? null;
                    $color = is_null($errorRate) ? '#e9ecef' : ($errorRate < 0.2 ? '#198754' : ($errorRate < 0.5 ? '#ffc107' : ($errorRate < 0.8 ? '#fd7e14' : '#dc3545')));
                @endphp
                <div class="heatmap-cell"
                     style="background: {{ $color }}; width: 100%; aspect-ratio: 1; cursor: pointer; border-radius: 2px;"
                     data-bs-toggle="tooltip"
                     data-bs-title="{{ __('Page') }} {{ $page }}: {{ is_null($errorRate) ? __('No data') : round($errorRate * 100) . '%' }}"
                     title="{{ __('Page') }} {{ $page }}">
                </div>
            @endfor
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <span class="d-flex align-items-center gap-1">
                <span style="display:inline-block;width:12px;height:12px;background:#198754;border-radius:2px"></span>
                @lang('Easy')
            </span>
            <span class="d-flex align-items-center gap-1">
                <span style="display:inline-block;width:12px;height:12px;background:#ffc107;border-radius:2px"></span>
                @lang('Medium')
            </span>
            <span class="d-flex align-items-center gap-1">
                <span style="display:inline-block;width:12px;height:12px;background:#fd7e14;border-radius:2px"></span>
                @lang('Hard')
            </span>
            <span class="d-flex align-items-center gap-1">
                <span style="display:inline-block;width:12px;height:12px;background:#dc3545;border-radius:2px"></span>
                @lang('Needs Work')
            </span>
            <span class="d-flex align-items-center gap-1">
                <span style="display:inline-block;width:12px;height:12px;background:#e9ecef;border-radius:2px"></span>
                @lang('No Data')
            </span>
        </div>
    </div>
</div>
