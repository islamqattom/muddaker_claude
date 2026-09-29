@if(!empty($breakdown))
<div class="rpt-chart-card" style="margin-bottom: 24px;">
    <h6><i class="fas fa-file-alt"></i> تقرير مكتوب — ملخص النشاط لكل {{ $studentLabel ?? 'طالب' }} في {{ $periodLabel ?? 'الربع' }}</h6>
    <div class="rpt-written-report" style="display:flex; flex-direction:column; gap:14px;">
        @foreach($breakdown as $row)
            <div class="rpt-written-row" style="background: linear-gradient(135deg, #f8f9fc 0%, #eef1f6 100%); border-right: 4px solid var(--primary, #182B48); border-radius: 10px; padding: 14px 16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; font-weight: 700; color: var(--primary, #182B48); font-size: 15px; margin-bottom: 8px;">
                    <span><i class="fas fa-user-graduate me-1"></i> {{ $row['name'] }}</span>
                    @if(!empty($pdfUrl) && !empty($row['student_id']))
                        <a href="{{ is_callable($pdfUrl) ? $pdfUrl($row['student_id']) : $pdfUrl }}"
                           class="rpt-btn rpt-btn-danger" style="padding: 6px 12px; font-size: 12px;" target="_blank">
                            <i class="fas fa-file-pdf"></i> تصدير PDF
                        </a>
                    @endif
                </div>
                <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:6px; color: #182B48; font-size: 14px; line-height: 1.8;">
                    <div>
                        <div style="margin-bottom: 4px;">
                            <i class="fas fa-calendar-check" style="color:#059669;"></i>
                            <strong>الحضور والغياب:</strong>
                        </div>
                        <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:4px; padding-right:22px; color:#182B48; font-size:14px; line-height:1.8;">
                            <div>1- العدد الكلي للجلسات: <strong style="color:#182B48;">{{ $row['att_total'] ?? 0 }}</strong></div>
                            <div>2- عدد أيام الحضور: <strong style="color:#27ae60;">{{ $row['att_days'] ?? 0 }}</strong>@if(!is_null($row['att_days_total'] ?? null)) من <strong style="color:#182B48;">{{ $row['att_days_total'] }}</strong>@endif</div>
                            <div>3- عدد أيام الغياب: <strong style="color:#c0392b;">{{ $row['att_absent'] ?? 0 }}</strong></div>
                            <div>4- عدد أيام التأخير: <strong style="color:#d97706;">{{ $row['att_late'] ?? 0 }}</strong></div>
                            <div>5- عدد أيام الغياب بعذر: <strong style="color:#3498db;">{{ $row['att_excused'] ?? 0 }}</strong></div>
                        </div>
                    </div>
                    <div>
                        <div style="margin-bottom: 4px;">
                            <i class="fas fa-user-slash" style="color:#6c757d;"></i>
                            <strong>التسميع:</strong>
                        </div>
                        <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:4px; padding-right:22px; color:#182B48; font-size:14px; line-height:1.8;">
                            <div>1- عدد أيام التسميع: <strong style="color:#16a085;">{{ $row['rec_days'] ?? 0 }}</strong>@if(!is_null($row['rec_days_total'] ?? null)) من <strong style="color:#182B48;">{{ $row['rec_days_total'] }}</strong>@endif</div>
                            <div>2- عدد الأيام التي لم يسمع بها: <strong style="color:#6c757d;">{{ $row['not_recite_days'] ?? 0 }}</strong></div>
                        </div>
                    </div>
                    <div>
                        <div style="margin-bottom: 4px;">
                            <i class="fas fa-plus-circle" style="color:#27ae60;"></i>
                            <strong>الحفظ الجديد:</strong>
                        </div>
                        <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:4px; padding-right:22px; color:#182B48; font-size:14px; line-height:1.8;">
                            <div>1- عدد جلسات الحفظ الجديد: <strong style="color:#182B48;">{{ $row['new_memorization_sessions'] }}</strong></div>
                            <div>2- عدد الجلسات النّاجحة: <strong style="color:#27ae60;">{{ $row['new_memorization_passed'] }}</strong></div>
                            <div>3- عدد الجلسات الرّاسبة: <strong style="color:#c0392b;">{{ $row['new_memorization_failed'] }}</strong></div>
                            <div>4- عدد صفحات الحفظ الجديد النّاجحة: <strong style="color:#27ae60;">{{ number_format($row['new_memorization_pages']) }}</strong></div>
                            <div>5- عدد صفحات الحفظ الجديد الرّاسبة: <strong style="color:#c0392b;">{{ number_format($row['new_memorization_failed_pages']) }}</strong></div>
                            <div>
                                6- عدد الأجزاء المحفوظة: <strong style="color:#182B48;">{{ $row['new_memorization_parts'] }}</strong>
                                <span style="color:#6b7a90; font-size:12px;">(تتطلب تسميع الصفحات الخاصة بالجزء ثم سردها والنّجاح بالسرد)</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div style="margin-bottom: 4px;">
                            <i class="fas fa-redo" style="color:#2980b9;"></i>
                            <strong>المراجعة:</strong>
                        </div>
                        <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:4px; padding-right:22px; color:#182B48; font-size:14px; line-height:1.8;">
                            <div>1- عدد جلسات المراجعة: <strong style="color:#182B48;">{{ $row['review_sessions'] }}</strong></div>
                            <div>2- عدد الجلسات النّاجحة: <strong style="color:#27ae60;">{{ $row['review_passed'] }}</strong></div>
                            <div>3- عدد الجلسات الرّاسبة: <strong style="color:#c0392b;">{{ $row['review_failed'] }}</strong></div>
                            <div>4- عدد صفحات المراجعة النّاجحة: <strong style="color:#27ae60;">{{ number_format($row['review_pages']) }}</strong></div>
                            <div>5- عدد صفحات المراجعة الرّاسبة: <strong style="color:#c0392b;">{{ number_format($row['review_failed_pages']) }}</strong></div>
                            <div>6- عدد الأجزاء المراجعة: <strong style="color:#182B48;">{{ $row['review_parts'] }}</strong>
                                <span style="color:#6b7a90; font-size:12px;">(لا تشترط سرد الجزء)</span>
                            </div>
                        </div>
                    </div>
                    @if($row['recitation_sessions'] > 0)
                        <div>
                            <div style="margin-bottom: 4px;">
                                <i class="fas fa-microphone-alt" style="color:#16a085;"></i>
                                <strong>السرد:</strong>
                            </div>
                            <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:4px; padding-right:22px; color:#182B48; font-size:14px; line-height:1.8;">
                                <div>1- عدد الجلسات: <strong style="color:#182B48;">{{ $row['recitation_sessions'] }}</strong></div>
                                <div>2- عدد الجلسات النّاجحة: <strong style="color:#27ae60;">{{ $row['recitation_passed'] }}</strong></div>
                                <div>3- عدد الجلسات الرّاسبة: <strong style="color:#c0392b;">{{ $row['recitation_failed'] }}</strong></div>
                            </div>
                        </div>
                    @endif
                    @if($row['unmastered_sessions'] > 0)
                        <div>
                            <div style="margin-bottom: 4px;">
                                <i class="fas fa-exclamation-triangle" style="color:#c0392b;"></i>
                                <strong>الحفظ غير المتقن:</strong>
                            </div>
                            <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:4px; padding-right:22px; color:#182B48; font-size:14px; line-height:1.8;">
                                <div>1- عدد جلسات الحفظ غير المتقن: <strong style="color:#182B48;">{{ $row['unmastered_sessions'] }}</strong></div>
                                <div>2- عدد الجلسات النّاجحة: <strong style="color:#27ae60;">{{ $row['unmastered_passed'] }}</strong></div>
                                <div>3- عدد الجلسات الرّاسبة: <strong style="color:#c0392b;">{{ $row['unmastered_failed'] }}</strong></div>
                                <div>4- عدد صفحات الحفظ غير المتقن النّاجحة: <strong style="color:#27ae60;">{{ number_format($row['unmastered_pages']) }}</strong></div>
                                <div>5- عدد صفحات الحفظ غير المتقن الرّاسبة: <strong style="color:#c0392b;">{{ number_format($row['unmastered_failed_pages']) }}</strong></div>
                                <div>6- عدد الأجزاء غير المتقنة: <strong style="color:#182B48;">{{ $row['unmastered_parts'] }}</strong></div>
                            </div>
                        </div>
                    @endif
                    @if($row['mafsil_sessions'] > 0)
                        <div>
                            <div style="margin-bottom: 4px;">
                                <i class="fas fa-bookmark" style="color:#d4a030;"></i>
                                <strong>المفصل:</strong>
                            </div>
                            <div class="rpt-written-lines" style="display:flex; flex-direction:column; gap:4px; padding-right:22px; color:#182B48; font-size:14px; line-height:1.8;">
                                @foreach($row['mafsil_details'] as $i => $m)
                                    <div>
                                        {{ $i + 1 }}- المفصل الذي تقدم له:
                                        <strong style="color:#182B48;">{{ $m['label'] }}</strong>
                                        —
                                        <strong style="color:{{ $m['status'] === 'passed' ? '#27ae60' : '#c0392b' }};">{{ $m['status_label'] }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div style="margin-top: 4px; padding-top: 8px; border-top: 1px dashed #cfd6e0; color: #6b7a90; font-size: 13px;">
                        <i class="fas fa-list-ol"></i> إجمالي جلسات {{ $periodLabel ?? 'الربع' }}:
                        <strong style="color:#182B48;">{{ $row['total_sessions'] }}</strong>
                        جلسة
                        @if(($row['not_recite_days'] ?? 0) > 0)
                            <span style="font-size:12px;">(تشمل {{ $row['not_recite_days'] }} يوماً لم يسمع بها)</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
