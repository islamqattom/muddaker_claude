@php
    use Illuminate\Support\Carbon;
    $today = \Carbon\Carbon::today()->toDateString();
@endphp
<div data-iman-card style="background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:24px;overflow:hidden;border:1px solid rgba(24,43,72,0.05);">
    <div style="background:linear-gradient(135deg,rgba(24,43,72,0.04),rgba(24,43,72,0.02));padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #eef1f6;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;">
                <i class="fas fa-seedling"></i>
            </div>
            <div>
                <h4 style="margin:0;color:#182B48;font-weight:800;font-size:18px;">
                    <a href="{{ isset($criteriaRoute) && $criteriaRoute ? $criteriaRoute : $editRoute }}" style="color:inherit;text-decoration:none;">{{ $program->name }}</a>
                </h4>
                <small style="color:#6b7a90;font-size:12px;">
                    أضافه: {{ $program->creator->name ?? '—' }}
                    @if($program->start_date) • يبدأ {{ $program->start_date->format('Y-m-d') }} @endif
                </small>
            </div>
        </div>
        @if($canManage ?? false)
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @if(isset($criteriaRoute) && $criteriaRoute)
                    <a href="{{ $criteriaRoute }}" style="background:linear-gradient(135deg,#d4a030,#e6b84d);border:none;padding:8px 14px;border-radius:10px;color:#fff;font-weight:600;text-decoration:none;font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-list-check"></i> إدارة معايير الطالب
                    </a>
                @endif
                <a href="{{ $editRoute }}" style="background:#f0f4f8;border:none;padding:8px 14px;border-radius:10px;color:#182B48;font-weight:600;text-decoration:none;font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-pen"></i> تعديل
                </a>
                <form method="POST" action="{{ $deleteRoute }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا البرنامج؟');" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:rgba(192,57,43,0.1);border:none;padding:8px 14px;border-radius:10px;color:#c0392b;font-weight:600;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash"></i> حذف
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if($program->description)
        <div style="padding:14px 22px;color:#6b7a90;font-size:14px;border-bottom:1px solid #eef1f6;">
            {{ $program->description }}
        </div>
    @endif

    @if($program->criteria->isEmpty())
        <div style="text-align:center;padding:50px 20px;color:#6b7a90;">
            <i class="fas fa-clipboard-list" style="font-size:40px;opacity:0.3;"></i>
            <p>لم يتم إضافة معايير بعد</p>
            @if($canManage ?? false)
                @if(isset($criteriaRoute) && $criteriaRoute)
                    <a href="{{ $criteriaRoute }}" style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;padding:10px 22px;border-radius:12px;font-weight:700;text-decoration:none;">
                        <i class="fas fa-plus-circle"></i> إضافة معايير للطالب
                    </a>
                @else
                    <a href="{{ $editRoute }}" style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;padding:10px 22px;border-radius:12px;font-weight:700;text-decoration:none;">
                        <i class="fas fa-plus-circle"></i> إضافة معايير
                    </a>
                @endif
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table style="width:100%;border-collapse:collapse;margin:0;">
                <thead>
                    <tr>
                        <th style="background:#f8f9fc;color:#182B48;font-weight:700;font-size:12px;padding:12px 10px;text-align:right;min-width:200px;">المعيار</th>
                        @foreach($weekDates as $date)
                            <th style="background:{{ $date->toDateString() === $today ? 'rgba(212,160,48,0.18)' : '#f8f9fc' }};color:#182B48;font-weight:700;font-size:12px;padding:12px 10px;text-align:center;min-width:90px;">
                                <span style="display:block;font-weight:700;">{{ ['الأحد','الإثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'][$date->dayOfWeek] ?? '' }}</span>
                                <span style="display:block;font-size:11px;color:#6b7a90;">{{ $date->format('m-d') }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($program->criteria as $criterion)
                        <tr>
                            <td style="padding:12px 10px;border-bottom:1px solid #eef1f6;text-align:right;">
                                <div style="color:#182B48;font-weight:700;">{{ $criterion->name }}</div>
                                <div style="color:#6b7a90;font-size:11px;">
                                    @if($criterion->type === 'check')
                                        <i class="fas fa-check-square"></i> علامة (تم/لم يتم)
                                    @else
                                        <i class="fas fa-hashtag"></i> رقم — الهدف: {{ $criterion->target_value ?? 0 }}
                                    @endif
                                </div>
                            </td>
                            @foreach($weekDates as $date)
                                @php
                                    $dateKey = $date->toDateString();
                                    $key = $program->id . '|' . $criterion->id . '|' . $dateKey;
                                    $entry = $entries->get($key)?->first();
                                    $isToday = $dateKey === $today;
                                @endphp
                                <td style="padding:6px;background:{{ $isToday ? 'rgba(212,160,48,0.05)' : 'transparent' }};border-bottom:1px solid #eef1f6;text-align:center;" data-criterion="{{ $criterion->id }}" data-date="{{ $dateKey }}">
                                    @if($criterion->type === 'check')
                                        @if($canEdit ?? false)
                                            <button type="button" class="check-cell {{ $entry && $entry->is_checked ? 'is-checked' : '' }}"
                                                    data-url="{{ $toggleRoute }}"
                                                    style="width:36px;height:36px;border-radius:10px;border:2px solid #d6dbe5;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @else
                                            <span class="check-cell-static {{ $entry && $entry->is_checked ? 'is-checked' : '' }}" style="display:inline-flex;width:30px;height:30px;border-radius:8px;align-items:center;justify-content:center;color:#fff;font-size:12px;transition:all 0.2s ease;">
                                                @if($entry && $entry->is_checked)<i class="fas fa-check"></i>@endif
                                            </span>
                                        @endif
                                    @else
                                        @php
                                            $currentValue = $entry?->value ?? 0;
                                            $isDone = $entry && (int) $entry->value >= (int) ($criterion->target_value ?? 0) && (int) $criterion->target_value > 0;
                                        @endphp
                                        @if($canEdit ?? false)
                                            <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                                                <input type="number" min="0" max="{{ ($criterion->target_value ?? 0) > 0 ? $criterion->target_value : '' }}" class="number-cell {{ $isDone ? 'is-done' : '' }}"
                                                       value="{{ $currentValue }}"
                                                       data-url="{{ $toggleRoute }}"
                                                       data-target="{{ $criterion->target_value ?? 0 }}"
                                                       style="width:80px;padding:6px 8px;border-radius:8px;border:1.5px solid #d6dbe5;text-align:center;font-weight:700;color:#182B48;background:#fff;transition:all 0.2s ease;">
                                            </div>
                                        @else
                                            <span class="number-cell-static {{ $isDone ? 'is-done' : '' }}" style="display:inline-block;min-width:40px;padding:4px 10px;border-radius:8px;font-weight:700;transition:all 0.2s ease;">
                                                {{ $currentValue }}
                                                @if($criterion->target_value) / {{ $criterion->target_value }} @endif
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(($canEdit ?? false) && ($showSaveButton ?? false))
            <div style="padding:12px 22px;border-top:1px solid #eef1f6;text-align:left;">
                <button type="button" class="save-table-btn"
                        style="background:linear-gradient(135deg,#27ae60,#2ecc71);color:#fff;border:none;padding:8px 20px;border-radius:10px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;font-size:14px;transition:all 0.2s ease;">
                    <i class="fas fa-save"></i> حفظ التغييرات
                </button>
            </div>
        @endif
    @endif
</div>
