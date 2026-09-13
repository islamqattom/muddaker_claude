@php
    // Shared report styles partial — used by admin + supervisor report pages.
    // Variables: $title (string), $accentColor (string, e.g. '#182B48'), $accentColor2 (string)
    $accentColor  = $accentColor  ?? '#182B48';
    $accentColor2 = $accentColor2 ?? '#2A4570';
    $accentSoft   = $accentSoft   ?? '#d4a030';
@endphp
<style>
:root {
    --primary: {{ $accentColor }};
    --primary-light: {{ $accentColor2 }};
    --gold: {{ $accentSoft }};
    --gold-dark: #B4802C;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
}

body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }

/* ========== Page header ========== */
.rpt-header {
    display: flex; justify-content: space-between; align-items: center;
    gap: 20px; margin-bottom: 24px; flex-wrap: wrap;
}
.rpt-header-content { display: flex; align-items: center; gap: 20px; }
.rpt-header-icon {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: var(--gold);
    box-shadow: 0 8px 22px rgba(24,43,72,0.22);
    flex-shrink: 0;
}
.rpt-header-text h4 { color: var(--primary); font-size: 22px; font-weight: 700; margin: 0 0 4px; }
.rpt-header-text p { color: #6c7a89; font-size: 13px; margin: 0; }
.rpt-header-text p::before { content: '✦'; color: var(--gold); margin-left: 6px; }

/* ========== Stat cards (KPI strip) ========== */
.rpt-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.rpt-kpi {
    background: #fff;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.04);
    position: relative;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease;
}
.rpt-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(24,43,72,0.12); }
.rpt-kpi::before {
    content: ''; position: absolute; top: 0; right: 0; left: 0; height: 3px;
    background: var(--kpi-color, var(--primary));
}
.rpt-kpi-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    background: var(--kpi-bg, rgba(24,43,72,0.08));
    color: var(--kpi-color, var(--primary));
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 18px; margin-bottom: 10px;
}
.rpt-kpi-value { font-size: 26px; font-weight: 800; color: var(--kpi-color, var(--primary)); line-height: 1; }
.rpt-kpi-label { font-size: 12px; color: #6c757d; font-weight: 600; margin-top: 4px; }
.rpt-kpi-sub { font-size: 11px; color: #94a3b8; margin-top: 2px; }

/* ========== Chart card ========== */
.rpt-chart-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 20px 22px;
    margin-bottom: 24px;
}
.rpt-chart-card h6 {
    color: var(--primary);
    font-size: 15px; font-weight: 700; margin: 0 0 12px;
    display: flex; align-items: center; gap: 8px;
}
.rpt-chart-card h6 i { color: var(--gold); }
.rpt-chart-wrap { position: relative; height: 220px; }

/* ========== Filter card ========== */
.rpt-filter {
    background: #fff;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.04);
    margin-bottom: 24px;
    overflow: hidden;
}
.rpt-filter-header {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 22px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; font-weight: 700; font-size: 14px;
}
.rpt-filter-header i { color: var(--gold); }
.rpt-filter-body { padding: 20px; }
.rpt-filter .form-control, .rpt-filter .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 9px 14px;
    font-family: inherit;
    font-size: 13px;
    transition: all .2s ease;
}
.rpt-filter .form-control:focus, .rpt-filter .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.12);
}
.rpt-filter label.form-label { font-size: 12px; color: var(--primary); font-weight: 700; margin-bottom: 4px; }
.rpt-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    border: 0; border-radius: 10px; padding: 10px 18px;
    font-weight: 700; font-size: 13px; color: #fff;
    transition: all .2s ease; text-decoration: none;
    font-family: inherit;
}
.rpt-btn:hover { color: #fff; transform: translateY(-2px); }
.rpt-btn-primary  { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); box-shadow: 0 4px 12px rgba(24,43,72,0.25); }
.rpt-btn-primary:hover  { box-shadow: 0 6px 20px rgba(24,43,72,0.35); }
.rpt-btn-danger   { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); box-shadow: 0 4px 12px rgba(192,57,43,0.25); }
.rpt-btn-danger:hover   { box-shadow: 0 6px 20px rgba(192,57,43,0.35); }
.rpt-btn-success  { background: linear-gradient(135deg, #059669 0%, #10b981 100%); box-shadow: 0 4px 12px rgba(5,150,105,0.25); }
.rpt-btn-success:hover  { box-shadow: 0 6px 20px rgba(5,150,105,0.35); }
.rpt-btn-ghost    { background: transparent; color: #7f8c8d; border: 2px solid #e9ecef; }
.rpt-btn-ghost:hover    { color: var(--primary); border-color: var(--primary); background: #fff; }

/* ========== Result table ========== */
.rpt-table-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}
.rpt-table-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; flex-wrap: wrap; gap: 10px;
}
.rpt-table-header h5 { margin: 0; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
.rpt-table-header h5 i { color: var(--gold); }
.rpt-count-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 700;
    background: rgba(255,255,255,0.18);
}
.rpt-table { width: 100%; border-collapse: collapse; }
.rpt-table thead th {
    background: #f8fafc;
    color: var(--primary);
    font-weight: 700; font-size: 11px;
    padding: 12px 10px;
    text-align: center;
    border-bottom: 2px solid rgba(24,43,72,0.06);
    cursor: pointer; user-select: none;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.rpt-table thead th[data-sort]:hover { background: #eef2f7; }
.rpt-table thead th[data-sort]::after { content: ' ⇅'; opacity: 0.35; font-size: 9px; }
.rpt-table thead th.sorted-asc::after  { content: ' ▲'; opacity: 0.9; }
.rpt-table thead th.sorted-desc::after { content: ' ▼'; opacity: 0.9; }
.rpt-table tbody td {
    padding: 12px 10px; font-size: 13px; color: #475569;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    text-align: center; vertical-align: middle;
}
.rpt-table tbody tr { transition: background .2s ease; }
.rpt-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04), rgba(24,43,72,0.02)); }
.rpt-table tbody tr:last-child td { border-bottom: 0; }

.rpt-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff; font-weight: 700; font-size: 12px;
    border-radius: 8px;
}
.rpt-student {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--primary); font-weight: 700; text-decoration: none;
}
.rpt-student:hover { color: var(--gold); }
.rpt-student-avatar {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, #8e44ad, #7d3c98);
    border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: 13px;
}

.rpt-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 999px;
    font-size: 11px; font-weight: 700;
}
.rpt-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.rpt-badge.passed        { background: rgba(39,174,96,0.15); color: var(--success); }
.rpt-badge.failed        { background: rgba(192,57,43,0.15); color: var(--danger); }
.rpt-badge.failed_continued { background: rgba(192,57,43,0.15); color: var(--danger); }
.rpt-badge.in_progress   { background: rgba(243,156,18,0.15); color: var(--warning); }
.rpt-badge.present       { background: rgba(5,150,105,0.15); color: #059669; }
.rpt-badge.absent        { background: rgba(192,57,43,0.15); color: var(--danger); }
.rpt-badge.late          { background: rgba(243,156,18,0.15); color: var(--warning); }
.rpt-badge.excused       { background: rgba(52,152,219,0.15); color: var(--info); }
.rpt-badge.holiday       { background: rgba(124,58,237,0.15); color: #7c3aed; }

.rpt-score {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 8px;
    font-size: 12px; font-weight: 700;
}
.rpt-score.high   { background: rgba(39,174,96,0.15); color: var(--success); }
.rpt-score.medium { background: rgba(243,156,18,0.15); color: var(--warning); }
.rpt-score.low    { background: rgba(192,57,43,0.15); color: var(--danger); }

.rpt-empty {
    text-align: center; padding: 60px 20px;
    color: #94a3b8;
}
.rpt-empty-icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, rgba(24,43,72,0.06), rgba(42,69,112,0.08));
    border-radius: 20px;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 16px;
    color: var(--gold);
    font-size: 32px;
}

.rpt-pagination { padding: 16px 22px; border-top: 1px solid rgba(24,43,72,0.05); display: flex; justify-content: center; }
.rpt-pagination .pagination { gap: 6px; margin: 0; }
.rpt-pagination .page-link {
    border-radius: 10px; border: 2px solid #e9ecef;
    color: var(--primary); padding: 8px 14px; font-size: 13px; font-weight: 600;
}
.rpt-pagination .page-link:hover { border-color: var(--gold); color: var(--gold); }
.rpt-pagination .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-color: var(--primary); color: #fff;
}

/* ========== Top-students mini list ========== */
.rpt-mini-list { display: flex; flex-direction: column; gap: 8px; }
.rpt-mini-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: 10px;
    background: #f8fafc;
}
.rpt-mini-rank {
    width: 24px; height: 24px; border-radius: 50%;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800;
}
.rpt-mini-row.r1 .rpt-mini-rank { background: linear-gradient(135deg, #f1c75b, var(--gold-dark)); }
.rpt-mini-row.r2 .rpt-mini-rank { background: linear-gradient(135deg, #b8c0c8, #6b7280); }
.rpt-mini-row.r3 .rpt-mini-rank { background: linear-gradient(135deg, #d97706, #92400e); }
.rpt-mini-name { flex: 1; font-weight: 700; color: var(--primary); font-size: 13px; }
.rpt-mini-bar {
    flex: 0 0 80px; height: 6px; background: #e9ecef; border-radius: 999px; overflow: hidden;
}
.rpt-mini-bar-fill { height: 100%; background: linear-gradient(90deg, var(--gold), var(--gold-dark)); }
.rpt-mini-score { font-size: 12px; font-weight: 700; color: var(--primary); min-width: 40px; text-align: end; }

/* Action buttons cluster */
.rpt-actions { display: flex; flex-wrap: wrap; gap: 8px; }

/* ========== KPI period-over-period deltas ========== */
.rpt-kpi-delta {
    display: flex; align-items: center; gap: 4px;
    margin-top: 6px; font-size: 11px; font-weight: 700;
}
.rpt-kpi-delta span { color: #94a3b8; font-weight: 500; }
.rpt-kpi-delta.up { color: #27ae60; }
.rpt-kpi-delta.down { color: #c0392b; }
.rpt-kpi-delta.flat { color: #94a3b8; }

/* ========== Active filter chips ========== */
.rpt-chips {
    display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
    margin: -8px 0 24px;
}
.rpt-chips-label { font-size: 12px; font-weight: 700; color: #6c7a89; }
.rpt-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 999px;
    background: rgba(24,43,72,0.06); color: var(--primary);
    font-size: 12px; font-weight: 700;
}
.rpt-chip i { color: var(--gold); }
.rpt-chip a { color: #c0392b; text-decoration: none; font-weight: 800; }
.rpt-chip-reset { background: rgba(192,57,43,0.10); color: #c0392b; text-decoration: none; }

/* ========== Responsive two-column report grid ========== */
.rpt-grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}
.rpt-grid-2 > .rpt-chart-card { margin-bottom: 0; }

@media (max-width: 992px) {
    .rpt-header { flex-direction: column; align-items: flex-start; }
    .rpt-header-content { margin-bottom: 10px; }
}
</style>

@once
@push('scripts')
<script>
window.ReportSortable = window.ReportSortable || (function () {
    function attach(table) {
        if (!table || table.dataset.sortableBound === '1') return;
        table.dataset.sortableBound = '1';
        const tbody = table.tBodies[0];
        if (!tbody) return;
        const headers = table.querySelectorAll('thead th[data-sort]');
        headers.forEach(function (th, idx) {
            th.addEventListener('click', function () {
                const dir = th.classList.contains('sorted-asc') ? 'desc' : 'asc';
                headers.forEach(function (other) { other.classList.remove('sorted-asc', 'sorted-desc'); });
                th.classList.add(dir === 'asc' ? 'sorted-asc' : 'sorted-desc');
                const rows = Array.from(tbody.rows);
                const type = th.dataset.sortType || 'text';
                rows.sort(function (a, b) {
                    const av = a.cells[idx] ? a.cells[idx].getAttribute('data-sort-value') ?? a.cells[idx].innerText.trim() : '';
                    const bv = b.cells[idx] ? b.cells[idx].getAttribute('data-sort-value') ?? b.cells[idx].innerText.trim() : '';
                    if (type === 'num') {
                        return (parseFloat(av) || 0) - (parseFloat(bv) || 0);
                    }
                    return av.localeCompare(bv, 'ar', { numeric: true });
                });
                rows.forEach(function (r) { tbody.appendChild(r); });
            });
        });
    }
    function init() {
        document.querySelectorAll('table.rpt-table').forEach(attach);
    }
    return { init: init };
})();
document.addEventListener('DOMContentLoaded', window.ReportSortable.init);
</script>
@endpush
@endonce
