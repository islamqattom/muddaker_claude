@php
    $showRec = $showRecitation ?? true;
    $showAtt = $showAttendance ?? true;
@endphp
<div class="rpt-table-card" style="margin-bottom: 24px;">
    <div class="rpt-table-header">
        <h5><i class="fas fa-users"></i> ملخص الطلاب</h5>
        <span class="rpt-count-badge"><i class="fas fa-user-graduate"></i> {{ count($summary) }} طالب</span>
    </div>
    @if(empty($summary))
        <div class="rpt-empty"><p>لا توجد بيانات لعرضها</p></div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th data-sort>الطالب</th>
                        @if($showRec)
                            <th data-sort="num">الجلسات</th>
                            <th data-sort="num">ناجح</th>
                            <th data-sort="num">نسبة النجاح</th>
                            <th data-sort="num">الصفحات</th>
                            <th data-sort="num">أيام لم يسمع</th>
                        @endif
                        @if($showAtt)
                            <th data-sort="num">حضور</th>
                            <th data-sort="num">غياب</th>
                            <th data-sort="num">تأخير</th>
                            <th data-sort="num">نسبة الحضور</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary as $row)
                        <tr>
                            <td data-sort-value="{{ $row['name'] }}" style="text-align:right;">
                                <a href="{{ route($studentRouteName, $row['student_id']) }}" class="rpt-student">
                                    <span class="rpt-student-avatar"><i class="fas fa-user-graduate"></i></span>
                                    {{ $row['name'] }}
                                </a>
                            </td>
                            @if($showRec)
                                <td data-sort-value="{{ $row['sessions'] }}">{{ $row['sessions'] }}</td>
                                <td data-sort-value="{{ $row['passed'] }}" style="color:#27ae60;font-weight:700;">{{ $row['passed'] }}</td>
                                <td data-sort-value="{{ $row['pass_rate'] }}">
                                    <span class="rpt-score {{ $row['pass_rate'] >= 90 ? 'high' : ($row['pass_rate'] >= 75 ? 'medium' : 'low') }}">{{ $row['pass_rate'] }}%</span>
                                </td>
                                <td data-sort-value="{{ $row['pages'] }}">{{ $row['pages'] }}</td>
                                <td data-sort-value="{{ $row['not_recite_days'] ?? 0 }}">{{ $row['not_recite_days'] ?? 0 }}</td>
                            @endif
                            @if($showAtt)
                                <td data-sort-value="{{ $row['att_present'] }}" style="color:#27ae60;font-weight:700;">{{ $row['att_present'] }}</td>
                                <td data-sort-value="{{ $row['att_absent'] }}" style="color:#c0392b;font-weight:700;">{{ $row['att_absent'] }}</td>
                                <td data-sort-value="{{ $row['att_late'] }}" style="color:#d97706;font-weight:700;">{{ $row['att_late'] }}</td>
                                <td data-sort-value="{{ $row['attendance_rate'] ?? -1 }}">
                                    {{ is_null($row['attendance_rate']) ? '—' : $row['attendance_rate'] . '%' }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
