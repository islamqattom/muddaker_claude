@if(!empty($breakdown))
<div class="rpt-chart-card" style="margin-bottom: 24px;">
    <h6><i class="fas fa-file-alt"></i> تقرير مكتوب — ملخص النشاط لكل {{ $studentLabel ?? 'طالب' }} في {{ $periodLabel ?? 'الربع' }}</h6>
    <div class="rpt-written-report" style="display:flex; flex-direction:column; gap:14px;">
        @foreach($breakdown as $row)
            <div class="rpt-written-row" style="background: linear-gradient(135deg, #f8f9fc 0%, #eef1f6 100%); border-right: 4px solid var(--primary, #182B48); border-radius: 10px; padding: 14px 16px;">
                <div style="font-weight: 700; color: var(--primary, #182B48); font-size: 15px; margin-bottom: 8px;">
                    <i class="fas fa-user-graduate me-1"></i> {{ $row['name'] }}
                </div>
                <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:6px; color: #182B48; font-size: 14px; line-height: 1.8;">
                    @if($row['mafsil_sessions'] > 0)
                        <div>
                            <span style="display:inline-block; min-width: 200px;">
                                <i class="fas fa-bookmark" style="color:#d4a030;"></i>
                                <strong>عدد الأجزاء المقدمة مفصل:</strong>
                            </span>
                            <span>
                                <strong style="color:#182B48;">{{ $row['mafsil_parts'] }}</strong> جزء
                                ({{ number_format($row['mafsil_pages']) }} صفحة)
                                في
                                <strong style="color:#182B48;">{{ $row['mafsil_sessions'] }}</strong>
                                {{ $row['mafsil_sessions'] == 1 ? 'جلسة' : 'جلسات' }}
                            </span>
                        </div>
                    @endif
                    @if($row['new_memorization_sessions'] > 0)
                        <div>
                            <span style="display:inline-block; min-width: 200px;">
                                <i class="fas fa-plus-circle" style="color:#27ae60;"></i>
                                <strong>إجمالي الأجزاء الجديدة المحفوظة:</strong>
                            </span>
                            <span>
                                <strong style="color:#182B48;">{{ $row['new_memorization_parts'] }}</strong> جزء
                                ({{ number_format($row['new_memorization_pages']) }} صفحة)
                                في
                                <strong style="color:#182B48;">{{ $row['new_memorization_sessions'] }}</strong>
                                {{ $row['new_memorization_sessions'] == 1 ? 'جلسة' : 'جلسات' }}
                            </span>
                        </div>
                    @endif
                    @if($row['review_sessions'] > 0)
                        <div>
                            <span style="display:inline-block; min-width: 200px;">
                                <i class="fas fa-redo" style="color:#2980b9;"></i>
                                <strong>المراجعة:</strong>
                            </span>
                            <span>
                                <strong style="color:#182B48;">{{ $row['review_sessions'] }}</strong>
                                {{ $row['review_sessions'] == 1 ? 'جلسة مراجعة' : 'جلسات مراجعة' }}
                                ({{ number_format($row['review_pages']) }} صفحة)
                            </span>
                        </div>
                    @endif
                    @if($row['recitation_sessions'] > 0)
                        <div>
                            <span style="display:inline-block; min-width: 200px;">
                                <i class="fas fa-microphone-alt" style="color:#16a085;"></i>
                                <strong>السرد:</strong>
                            </span>
                            <span>
                                <strong style="color:#182B48;">{{ $row['recitation_sessions'] }}</strong>
                                {{ $row['recitation_sessions'] == 1 ? 'جلسة سرد' : 'جلسات سرد' }}
                                ({{ number_format($row['recitation_pages']) }} صفحة)
                            </span>
                        </div>
                    @endif
                    @if($row['unmastered_sessions'] > 0)
                        <div>
                            <span style="display:inline-block; min-width: 200px;">
                                <i class="fas fa-exclamation-triangle" style="color:#c0392b;"></i>
                                <strong>الحفظ غير المتقن:</strong>
                            </span>
                            <span>
                                <strong style="color:#182B48;">{{ $row['unmastered_sessions'] }}</strong>
                                {{ $row['unmastered_sessions'] == 1 ? 'جلسة' : 'جلسات' }}
                                ({{ number_format($row['unmastered_pages'] ?? 0) }} صفحة)
                            </span>
                        </div>
                    @endif
                    <div style="margin-top: 4px; padding-top: 8px; border-top: 1px dashed #cfd6e0; color: #6b7a90; font-size: 13px;">
                        <i class="fas fa-list-ol"></i> إجمالي جلسات {{ $periodLabel ?? 'الربع' }}:
                        <strong style="color:#182B48;">{{ $row['total_sessions'] }}</strong>
                        {{ $row['total_sessions'] == 1 ? 'جلسة' : 'جلسة' }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
