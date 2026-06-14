@extends('layouts.app')

@section('title', 'تسميع - مفصل')
@section('page-title', 'جلسة مفصل'.' - '.$mafsilType)
@section('role-label', 'مشرف')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.rec-session {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --gold-soft: #fcf6e8;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --radius: 16px;
    --radius-sm: 12px;
    --shadow-sm: 0 2px 10px rgba(24,43,72,0.05);
    --shadow: 0 6px 24px rgba(24,43,72,0.08);
    --shadow-hover: 0 10px 32px rgba(24,43,72,0.12);
}

.rec-session .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius);
    padding: 22px 28px;
    margin-bottom: 22px;
    position: relative;
    overflow: hidden;
}

.rec-session .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.rec-session .hero-content { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }

.rec-session .student-info { display: flex; align-items: center; gap: 14px; }

.rec-session .student-avatar {
    width: 54px; height: 54px;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: white;
    box-shadow: 0 6px 18px rgba(231,76,60,0.3);
}

.rec-session .student-name { font-size: 20px; font-weight: 700; color: white; margin-bottom: 4px; }

.rec-session .hero-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.rec-session .compact-badge {
    font-size: 11px; padding: 5px 12px; border-radius: 8px;
}

.rec-session .type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 12px;
    font-size: 13px; font-weight: 600;
    background: rgba(231,76,60,0.25); color: var(--danger);
}

.rec-session .session-info-badge {
    background: rgba(255,255,255,0.15);
    color: white;
    padding: 8px 16px; border-radius: 12px;
    font-size: 13px;
    backdrop-filter: blur(5px);
}

.rec-session .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 14px;
    color: rgba(255,255,255,0.7);
    text-align: center;
    padding: 12px 20px 0;
    margin-top: 14px;
    position: relative; z-index: 1;
}

/* Cards */
.rec-session .page-card {
    background: white;
    border: 1px solid rgba(24,43,72,0.06);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 18px;
}

.rec-session .page-card-header {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    padding: 16px 20px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    flex-wrap: wrap; gap: 10px;
}

.rec-session .page-card-body { padding: 22px; }

.rec-session .section-title {
    color: var(--navy);
    font-size: 15px;
    font-weight: 700;
    display: flex; align-items: center; gap: 10px;
}

/* Progress */
.rec-session .selection-progress {
    background: white;
    border-radius: var(--radius);
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(24,43,72,0.06);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.rec-session .progress-track {
    flex: 1;
    min-width: 160px;
    height: 10px;
    background: #eef0f3;
    border-radius: 50px;
    overflow: hidden;
}

.rec-session .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 50px;
    transition: width 0.35s ease;
}

.rec-session .progress-text {
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    white-space: nowrap;
}

/* Part checkbox cards */
.rec-session .part-checkbox-card {
    position: relative;
    background: white;
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: var(--radius-sm);
    padding: 16px 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 12px;
    height: 100%;
    user-select: none;
}

.rec-session .part-checkbox-card:hover {
    border-color: rgba(24,43,72,0.2);
    box-shadow: var(--shadow);
    transform: translateY(-2px);
}

.rec-session .part-checkbox-card.selected {
    border-color: var(--navy);
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    box-shadow: 0 0 0 3px rgba(24,43,72,0.08);
}

.rec-session .part-checkbox-card .check-circle {
    width: 26px; height: 26px;
    border: 2px solid rgba(24,43,72,0.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: transparent;
    transition: all 0.2s ease;
    flex-shrink: 0;
    font-size: 12px;
}

.rec-session .part-checkbox-card.selected .check-circle {
    background: var(--navy);
    border-color: var(--navy);
    color: white;
}

.rec-session .part-checkbox-card .part-info { flex: 1; }

.rec-session .part-checkbox-card .part-number {
    font-size: 16px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 2px;
}

.rec-session .part-checkbox-card .part-range {
    font-size: 12px;
    color: #7f8c8d;
}

.rec-session .part-checkbox-card input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

/* Difficulty distribution */
.rec-session .distribution-card {
    background: white;
    border: 1px solid rgba(24,43,72,0.08);
    border-radius: var(--radius-sm);
    padding: 16px;
    box-shadow: var(--shadow-sm);
    height: 100%;
    transition: all 0.2s ease;
}

.rec-session .distribution-card:hover {
    box-shadow: var(--shadow);
}

.rec-session .distribution-card .card-part-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rec-session .difficulty-counter {
    text-align: center;
}

.rec-session .difficulty-counter .counter-label {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

.rec-session .difficulty-counter.easy .counter-label { color: var(--success); }
.rec-session .difficulty-counter.medium .counter-label { color: #d68a0a; }
.rec-session .difficulty-counter.hard .counter-label { color: var(--danger); }

.rec-session .difficulty-counter .counter-box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.rec-session .difficulty-counter input {
    width: 44px;
    height: 38px;
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: 8px;
    text-align: center;
    font-weight: 700;
    font-size: 15px;
    color: var(--navy);
    padding: 0;
}

.rec-session .difficulty-counter.easy input { border-color: rgba(39,174,96,0.25); }
.rec-session .difficulty-counter.medium input { border-color: rgba(243,156,18,0.25); }
.rec-session .difficulty-counter.hard input { border-color: rgba(192,57,43,0.25); }

.rec-session .difficulty-counter input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.12);
}

.rec-session .counter-btn {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all 0.15s ease;
    color: white;
}

.rec-session .counter-btn:active { transform: scale(0.94); }

.rec-session .difficulty-counter.easy .counter-btn { background: var(--success); }
.rec-session .difficulty-counter.medium .counter-btn { background: var(--warning); }
.rec-session .difficulty-counter.hard .counter-btn { background: var(--danger); }

.rec-session .distribution-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 10px;
    border-radius: 20px;
    margin-top: 10px;
}

.rec-session .distribution-status.valid {
    background: rgba(39,174,96,0.1);
    color: var(--success);
}

.rec-session .distribution-status.invalid {
    background: rgba(192,57,43,0.1);
    color: var(--danger);
}

/* Sticky action bar */
.rec-session .sticky-action-bar {
    position: sticky;
    bottom: 0;
    background: white;
    border-top: 1px solid rgba(24,43,72,0.08);
    padding: 14px 20px;
    margin: 0 -22px -22px;
    box-shadow: 0 -4px 20px rgba(24,43,72,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    z-index: 10;
}

.rec-session .save-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: var(--radius-sm);
    padding: 12px 28px;
    font-size: 14px; font-weight: 700;
    color: white;
    transition: all 0.25s ease;
    box-shadow: 0 4px 15px rgba(24,43,72,0.3);
}

.rec-session .save-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.4); }
.rec-session .save-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; box-shadow: none; }

.rec-session .btn-outline-secondary {
    border: 1px solid rgba(24,43,72,0.12);
    border-radius: var(--radius-sm);
    padding: 12px 22px;
    font-size: 13px; font-weight: 600;
    color: var(--navy);
    background: white;
    transition: all 0.25s ease;
}

.rec-session .btn-outline-secondary:hover { background: rgba(24,43,72,0.05); border-color: var(--navy); }

/* Info alert */
.rec-session .info-alert {
    border-radius: var(--radius-sm);
    border: none;
    padding: 14px 18px;
    background: linear-gradient(135deg, rgba(52,152,219,0.08) 0%, rgba(52,152,219,0.03) 100%);
    border-right: 4px solid var(--info);
    color: var(--navy);
}

/* Score summary (at bottom) */
.rec-session .score-summary {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid rgba(24,43,72,0.06);
    margin-bottom: 20px;
    overflow: hidden;
}

.rec-session .score-summary-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    padding: 16px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.rec-session .score-summary-header .summary-title {
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rec-session .score-summary-body {
    padding: 22px;
}

.rec-session .score-pills {
    display: flex;
    align-items: stretch;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}

.rec-session .score-pill {
    flex: 1 1 140px;
    min-width: 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 14px 12px;
    border-radius: var(--radius-sm);
    background: #f8f9fa;
    color: var(--navy);
    border: 1px solid rgba(24,43,72,0.05);
    transition: all 0.2s ease;
}

.rec-session .score-pill:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.rec-session .score-pill .pill-label {
    font-size: 12px;
    font-weight: 600;
    opacity: 0.75;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.rec-session .score-pill .pill-value {
    font-size: 22px;
    font-weight: 800;
    line-height: 1.1;
}

.rec-session .score-pill.errors { background: rgba(220,38,38,0.06); color: var(--danger); }
.rec-session .score-pill.hesitations { background: rgba(243,156,18,0.08); color: #b36b00; }
.rec-session .score-pill.deduction { background: rgba(108,117,125,0.1); color: #495057; }
.rec-session .score-pill.final { background: rgba(24,43,72,0.08); color: var(--navy); }

.rec-session .score-pill.final.success { background: rgba(39,174,96,0.1); color: var(--success); }
.rec-session .score-pill.final.warning { background: rgba(243,156,18,0.12); color: #b36b00; }
.rec-session .score-pill.final.danger { background: rgba(192,57,43,0.1); color: var(--danger); }

.rec-session .final-score-display {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border-radius: var(--radius-sm);
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    border: 1px solid rgba(24,43,72,0.06);
}

.rec-session .final-score-display .label {
    font-size: 13px;
    color: #7f8c8d;
    font-weight: 600;
}

.rec-session .final-score-display .value {
    font-size: 36px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1;
}

.rec-session .final-score-display .value.success { color: var(--success); }
.rec-session .final-score-display .value.warning { color: #b36b00; }
.rec-session .final-score-display .value.danger { color: var(--danger); }

.rec-session .final-score-display .threshold {
    font-size: 12px;
    color: #7f8c8d;
    background: white;
    padding: 6px 12px;
    border-radius: 20px;
    border: 1px solid rgba(24,43,72,0.08);
}

/* Part section */
.rec-session .part-section {
    margin-bottom: 28px;
}

.rec-session .part-section-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    padding: 14px 20px;
    border-radius: var(--radius) var(--radius) 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.rec-session .part-section-header .part-title {
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rec-session .part-section-header .part-meta {
    font-size: 12px;
    opacity: 0.85;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rec-session .part-section-body {
    background: white;
    border: 1px solid rgba(24,43,72,0.06);
    border-top: none;
    border-radius: 0 0 var(--radius) var(--radius);
    padding: 20px;
    box-shadow: var(--shadow-sm);
}

/* Position cards */
.rec-session .position-card {
    background: white;
    border: 1px solid rgba(24,43,72,0.08);
    border-radius: var(--radius-sm);
    margin-bottom: 12px;
    transition: all 0.2s ease;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.rec-session .position-card:hover {
    box-shadow: var(--shadow);
    border-color: rgba(212,160,48,0.3);
}

.rec-session .position-card:last-child { margin-bottom: 0; }

.rec-session .position-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    flex-wrap: wrap;
}

.rec-session .position-number {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(24,43,72,0.2);
}

.rec-session .position-range {
    font-size: 15px;
    font-weight: 700;
    color: var(--navy);
}

.rec-session .position-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-inline-start: auto;
}

.rec-session .position-card .difficulty-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.rec-session .position-card .page-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 20px;
    background: rgba(52,152,219,0.1);
    color: var(--info);
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.rec-session .btn-view-ayahs {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid rgba(52,152,219,0.25);
    background: white;
    color: var(--info);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 13px;
    flex-shrink: 0;
}

.rec-session .btn-view-ayahs:hover {
    background: var(--info);
    color: white;
    border-color: var(--info);
    transform: scale(1.05);
}

.rec-session .position-card-body {
    padding: 16px 18px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1.2fr;
    gap: 14px;
    align-items: center;
}

.rec-session .position-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
    padding: 8px;
    border-radius: 10px;
    background: #fafbfc;
    border: 1px solid rgba(24,43,72,0.04);
}

.rec-session .position-stat .stat-label {
    font-size: 11px;
    font-weight: 700;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.rec-session .scoring-input-group {
    display: flex;
    align-items: center;
    gap: 4px;
}

.rec-session .scoring-input-group button {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
    transition: all 0.15s ease;
    flex-shrink: 0;
}

.rec-session .scoring-input-group button:active { transform: scale(0.92); }

.rec-session .scoring-input-group .btn-errors { background: rgba(220,38,38,0.15); color: var(--danger); }
.rec-session .scoring-input-group .btn-errors:hover { background: var(--danger); color: white; }
.rec-session .scoring-input-group .btn-hesitations { background: rgba(243,156,18,0.15); color: #b36b00; }
.rec-session .scoring-input-group .btn-hesitations:hover { background: var(--warning); color: white; }

.rec-session .scoring-input-group input {
    width: 48px;
    height: 30px;
    border: 1px solid rgba(24,43,72,0.1);
    border-radius: 8px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    color: var(--navy);
    padding: 0;
    background: white;
}

.rec-session .scoring-input-group.errors input { border-color: rgba(220,38,38,0.2); }
.rec-session .scoring-input-group.hesitations input { border-color: rgba(243,156,18,0.2); }

.rec-session .scoring-input-group input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 2px rgba(212,160,48,0.15);
}

.rec-session .position-deduction {
    text-align: center;
    font-size: 18px;
    font-weight: 800;
    color: #6c757d;
}

.rec-session .position-score-box {
    background: linear-gradient(135deg, rgba(192,57,43,0.08) 0%, rgba(192,57,43,0.04) 100%);
    border-radius: 10px;
    padding: 10px 8px;
    text-align: center;
    border: 1px solid rgba(192,57,43,0.15);
    transition: all 0.25s ease;
}

.rec-session .position-score-box.success {
    background: linear-gradient(135deg, rgba(39,174,96,0.1) 0%, rgba(39,174,96,0.04) 100%);
    border-color: rgba(39,174,96,0.2);
}

.rec-session .position-score-box.warning {
    background: linear-gradient(135deg, rgba(243,156,18,0.1) 0%, rgba(243,156,18,0.04) 100%);
    border-color: rgba(243,156,18,0.2);
}

.rec-session .position-score-box .score-label {
    font-size: 10px;
    font-weight: 700;
    color: #7f8c8d;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.rec-session .position-score-box .score-value {
    font-size: 20px;
    font-weight: 800;
    color: var(--danger);
    line-height: 1.1;
}

.rec-session .position-score-box.success .score-value { color: var(--success); }
.rec-session .position-score-box.warning .score-value { color: #b36b00; }

.rec-session .notes-textarea {
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: var(--radius-sm);
    padding: 14px 16px;
    font-size: 14px;
    transition: all 0.25s ease;
}

.rec-session .notes-textarea:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.12);
}

/* Empty / warning states */
.rec-session .state-empty {
    text-align: center;
    padding: 40px 20px;
    color: #7f8c8d;
}

.rec-session .state-empty i {
    font-size: 42px;
    margin-bottom: 14px;
    color: #b0b7be;
}

/* Modal */
.rec-session .modal-content { border-radius: var(--radius); overflow: hidden; }
.rec-session .modal-header { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white; }

@media (max-width: 768px) {
    .rec-session .page-card-body { padding: 16px; }
    .rec-session .sticky-action-bar { margin: 0 -16px -16px; padding: 12px 16px; }
    .rec-session .score-summary-header { padding: 12px 16px; }
    .rec-session .score-summary-body { padding: 16px; }
    .rec-session .part-section-body { padding: 14px; }
    .rec-session .position-card-body {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .rec-session .position-card-head { padding: 12px 14px; gap: 8px; }
    .rec-session .position-range { font-size: 14px; }
    .rec-session .position-meta { width: 100%; margin-inline-start: 0; justify-content: flex-start; }
    .rec-session .hero-content { flex-direction: column; text-align: center; }
    .rec-session .student-info { flex-direction: column; }
    .rec-session .hero-meta { justify-content: center; }
    .rec-session .final-score-display { padding: 14px 16px; text-align: center; }
    .rec-session .final-score-display .value { font-size: 30px; }
}

@media (max-width: 480px) {
    .rec-session .position-card-body { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="rec-session">
    <div class="page-hero">
        <div class="hero-content">
            <div class="student-info">
                <div class="student-avatar">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <h2 class="student-name">{{ $student->user->name }}</h2>
                    <div class="hero-meta">
                        <span class="compact-badge bg-warning text-dark">
                            <i class="fas fa-layer-group me-1"></i>
                            {{ $student->level->name ?? '' }}
                        </span>
                        @if($student->student_number)
                        <span class="text-white opacity-75 small">
                            <i class="fas fa-id-card me-1"></i>
                            {{ $student->student_number }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="type-badge mafsil">
                    <i class="fas fa-star"></i>مفصل {{ $mafsilType }}
                </span>
                <span class="session-info-badge">
                    <i class="fas fa-percentage me-1"></i>علامة الرسوب: {{ $passingPercentage }}%
                    <span class="opacity-50 mx-1">|</span>
                    <i class="fas fa-chart-line me-1"></i>العلامة الحالية: <span id="currentScore">100%</span>
                </span>
            </div>
        </div>
        <div class="quran-verse">
            <i class="fas fa-book-quran me-2"></i>
            {{ 'إِنَّ هَذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ' }}
        </div>
    </div>

    <!-- Part Selection Mode -->
    @if($selectionMode)
    <div class="selection-progress">
        <div class="progress-text">
            <i class="fas fa-check-circle me-1" style="color: var(--gold);"></i>
            تم اختيار <span id="selectionCount">0</span> من {{ $partsCount }} أجزاء
        </div>
        <div class="progress-track">
            <div class="progress-fill" id="selectionProgressFill" style="width: 0%"></div>
        </div>
    </div>

    <div class="page-card">
        <div class="page-card-header">
            <div class="section-title">
                <i class="fas fa-hand-pointer" style="color: var(--gold);"></i>
                اختر الأجزاء المطلوب اختبارها
            </div>
            <span class="badge bg-light text-dark border" style="font-size: 12px;">
                مطلوب {{ $partsCount }} أجزاء
            </span>
        </div>
        <div class="page-card-body pb-0">
            <div class="info-alert d-flex gap-3 align-items-start mb-4">
                <i class="fas fa-info-circle fa-lg mt-1" style="color: var(--info);"></i>
                <div>
                    اختر <strong>{{ $partsCount }} أجزاء</strong> فقط، ثم حدد توزيع صعوبة المواضيع لكل جزء (3 مواضع لكل جزء).
                </div>
            </div>

            <form id="partSelectionForm" method="POST" action="{{ route('supervisor.recitation.mafsil.post') }}">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="mafsil_type" value="{{ $mafsilType }}">

                <div class="row g-3 mb-4">
                    @foreach($allParts as $part)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <label class="part-checkbox-card w-100" for="part-{{ $part->part_number }}">
                            <input class="form-check-input part-checkbox" type="checkbox"
                                   name="selected_parts[]"
                                   value="{{ $part->part_number }}"
                                   data-part-number="{{ $part->part_number }}"
                                   data-part-label="الجزء {{ $part->part_number }}"
                                   id="part-{{ $part->part_number }}">
                            <span class="check-circle"><i class="fas fa-check"></i></span>
                            <span class="part-info">
                                <div class="part-number">الجزء {{ $part->part_number }}</div>
                                <div class="part-range">صفحة {{ $part->start_page }} - {{ $part->end_page }}</div>
                            </span>
                        </label>
                    </div>
                    @endforeach
                </div>

                <div class="alert alert-warning d-none" id="selectionWarning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="selectionWarningText"></span>
                </div>

                <div id="difficultyDistributionContainer" class="d-none">
                    <hr class="my-4">
                    <div class="section-title mb-3">
                        <i class="fas fa-sliders-h" style="color: var(--gold);"></i>
                        توزيع المواضيع (3 مواضع لكل جزء)
                    </div>
                    <div class="alert alert-info mb-3" style="border-radius: var(--radius-sm);">
                        <i class="fas fa-info-circle me-2"></i>
                        حدد عدد المواضيع السهلة والمتوسطة والصعبة لكل جزء. يجب أن يكون المجموع = <strong>3</strong>.
                    </div>
                    <div id="difficultyDistributionRows" class="row g-3"></div>
                </div>

                <div class="sticky-action-bar">
                    <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>رجوع
                    </a>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small d-none d-md-inline">
                            <span id="stickySelectionCount">0</span> / {{ $partsCount }} جزء
                        </span>
                        <button type="submit" class="save-btn" id="confirmPartsBtn" disabled>
                            <i class="fas fa-check me-2"></i>
                            تأكيد الأجزاء
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const requiredParts = {{ $partsCount }};

    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.part-checkbox');
        const warningEl = document.getElementById('selectionWarning');
        const warningTextEl = document.getElementById('selectionWarningText');
        const countEl = document.getElementById('selectionCount');
        const stickyCountEl = document.getElementById('stickySelectionCount');
        const progressFill = document.getElementById('selectionProgressFill');
        const btn = document.getElementById('confirmPartsBtn');
        const distributionContainer = document.getElementById('difficultyDistributionContainer');
        const distributionRows = document.getElementById('difficultyDistributionRows');

        function updateDistributionInputs() {
            const checked = Array.from(document.querySelectorAll('.part-checkbox:checked'));
            const checkedCount = checked.length;

            countEl.textContent = checkedCount;
            stickyCountEl.textContent = checkedCount;
            progressFill.style.width = Math.min(100, (checkedCount / requiredParts) * 100) + '%';
            distributionRows.innerHTML = '';

            document.querySelectorAll('.part-checkbox-card').forEach(card => {
                const cb = card.querySelector('.part-checkbox');
                card.classList.toggle('selected', cb.checked);
            });

            if (checkedCount === requiredParts) {
                warningEl.classList.add('d-none');
                distributionContainer.classList.remove('d-none');

                checked.sort((a, b) => parseInt(a.dataset.partNumber) - parseInt(b.dataset.partNumber));

                checked.forEach(cb => {
                    const partNumber = cb.dataset.partNumber;
                    const partLabel = cb.dataset.partLabel;

                    const row = document.createElement('div');
                    row.className = 'col-lg-4 col-md-6';
                    row.innerHTML = `
                        <div class="distribution-card" data-part="${partNumber}">
                            <div class="card-part-title">
                                <span style="width: 28px; height: 28px; background: rgba(24,43,72,0.08); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">
                                    ${partNumber}
                                </span>
                                ${partLabel}
                            </div>
                            <div class="row g-2">
                                <div class="col-4 difficulty-counter easy">
                                    <label class="counter-label">سهل</label>
                                    <div class="counter-box">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 1, -1)">-</button>
                                        <input type="number" name="difficulty[${partNumber}][1]" value="1" min="0" max="3"
                                               class="difficulty-input" data-part="${partNumber}" data-difficulty="1"
                                               oninput="validateDistribution()">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 1, 1)">+</button>
                                    </div>
                                </div>
                                <div class="col-4 difficulty-counter medium">
                                    <label class="counter-label">متوسط</label>
                                    <div class="counter-box">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 2, -1)">-</button>
                                        <input type="number" name="difficulty[${partNumber}][2]" value="1" min="0" max="3"
                                               class="difficulty-input" data-part="${partNumber}" data-difficulty="2"
                                               oninput="validateDistribution()">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 2, 1)">+</button>
                                    </div>
                                </div>
                                <div class="col-4 difficulty-counter hard">
                                    <label class="counter-label">صعب</label>
                                    <div class="counter-box">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 3, -1)">-</button>
                                        <input type="number" name="difficulty[${partNumber}][3]" value="1" min="0" max="3"
                                               class="difficulty-input" data-part="${partNumber}" data-difficulty="3"
                                               oninput="validateDistribution()">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 3, 1)">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="distribution-status invalid" data-status="${partNumber}">
                                <i class="fas fa-exclamation-circle"></i>
                                المجموع: <span class="part-total" data-part="${partNumber}">3</span> / 3
                            </div>
                        </div>
                    `;
                    distributionRows.appendChild(row);
                });

                validateDistribution();
            } else {
                distributionContainer.classList.add('d-none');

                if (checkedCount > 0 && checkedCount < requiredParts) {
                    warningEl.classList.remove('d-none');
                    warningTextEl.textContent = 'باقٍ ' + (requiredParts - checkedCount) + ' أجزاء لإكمال الاختيار';
                } else if (checkedCount > requiredParts) {
                    warningEl.classList.remove('d-none');
                    warningTextEl.textContent = 'لقد تجاوزت العدد المطلوب بـ ' + (checkedCount - requiredParts) + ' أجزاء';
                } else {
                    warningEl.classList.add('d-none');
                }
                btn.disabled = true;
            }
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateDistributionInputs);
        });

        window.adjustDifficulty = function(partNumber, difficulty, delta) {
            const input = document.querySelector(`.difficulty-input[data-part="${partNumber}"][data-difficulty="${difficulty}"]`);
            if (!input) return;
            let val = parseInt(input.value || 0) + delta;
            val = Math.max(0, Math.min(3, val));
            input.value = val;
            validateDistribution();
        };
    });

    function validateDistribution() {
        const btn = document.getElementById('confirmPartsBtn');
        const inputs = document.querySelectorAll('.difficulty-input');
        const parts = {};

        inputs.forEach(input => {
            const partNumber = input.dataset.part;
            if (!parts[partNumber]) parts[partNumber] = 0;
            parts[partNumber] += parseInt(input.value) || 0;
        });

        let allValid = true;
        document.querySelectorAll('.part-total').forEach(el => {
            const partNumber = el.dataset.part;
            const total = parts[partNumber] || 0;
            el.textContent = total;

            const statusEl = document.querySelector(`.distribution-status[data-status="${partNumber}"]`);
            if (statusEl) {
                if (total === 3) {
                    statusEl.className = 'distribution-status valid';
                    statusEl.innerHTML = '<i class="fas fa-check-circle"></i> المجموع: <span class="part-total" data-part="' + partNumber + '">3</span> / 3';
                } else {
                    statusEl.className = 'distribution-status invalid';
                    statusEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> المجموع: <span class="part-total" data-part="' + partNumber + '">' + total + '</span> / 3';
                }
            }

            if (total !== 3) {
                allValid = false;
            }
        });

        const checkedCount = document.querySelectorAll('.part-checkbox:checked').length;
        btn.disabled = checkedCount !== requiredParts || !allValid;
    }
    </script>
    @endpush

    @else
    <!-- Scoring Mode -->

    <div class="info-alert d-flex gap-3 align-items-start mb-4">
        <i class="fas fa-info-circle fa-lg mt-1" style="color: var(--info);"></i>
        <div>
            <strong>نظام الخصم:</strong>
            كل خطأ = <span class="badge bg-danger">-3 علامات</span>
            | كل تردد = <span class="badge bg-warning text-dark">-0.33 علامة</span> (يُجبر عند 3+)
            | العلامة النهائية لا تقل عن <span class="badge bg-secondary">{{ $passingPercentage }}</span>
        </div>
    </div>

    <form id="mafsilForm">
        @csrf
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <input type="hidden" name="type" value="mafsil">
        <input type="hidden" name="mafsil_type" value="{{ $mafsilType }}">
        <input type="hidden" name="session_date" value="{{ request('session_date', date('Y-m-d')) }}">
        <input type="hidden" name="part_id" value="{{ $parts->first()->id ?? '' }}">

        @php $globalIndex = 0; @endphp
        @foreach($parts as $partIndex => $part)
        @php $juzPositions = $mafsilPositions[$part->part_number] ?? collect(); @endphp
        <div class="part-section">
            <div class="part-section-header">
                <div class="part-title">
                    <i class="fas fa-book-open"></i>
                    الجزء {{ $part->part_number }}
                </div>
                <div class="part-meta">
                    <span><i class="fas fa-file-alt me-1"></i>صفحة {{ $part->start_page }} - {{ $part->end_page }}</span>
                    <span><i class="fas fa-list-ol me-1"></i>{{ $juzPositions->count() }} مواضع</span>
                </div>
            </div>
            <div class="part-section-body">
                @if($juzPositions->isEmpty())
                <div class="state-empty">
                    <i class="fas fa-inbox"></i>
                    <p>لا توجد مواضع محددة لهذا الجزء</p>
                </div>
                @else
                @foreach($juzPositions as $posIndex => $mafsilPosition)
                @php $isPlaceholder = empty($mafsilPosition->start_ayah_id) || empty($mafsilPosition->end_ayah_id); @endphp
                <div class="position-card" id="mafsil-row-{{ $partIndex }}-{{ $posIndex }}" data-placeholder="{{ $isPlaceholder ? '1' : '0' }}">
                    <div class="position-card-head">
                        <span class="position-number">{{ $posIndex + 1 }}</span>
                        <span class="position-range">
                            @if($isPlaceholder)
                                <em class="text-muted">الموضع {{ $posIndex + 1 }}</em>
                            @else
                                {{ $mafsilPosition->positionRangeLabel() }}
                            @endif
                        </span>
                        <div class="position-meta">
                            @if(!$isPlaceholder)
                                @php
                                    $difficultyClass = match($mafsilPosition->difficulty) {
                                        1 => 'bg-success',
                                        3 => 'bg-danger',
                                        default => 'bg-warning text-dark',
                                    };
                                @endphp
                                <span class="difficulty-badge {{ $difficultyClass }}">
                                    @if($mafsilPosition->difficulty == 1)<i class="fas fa-feather-alt"></i>
                                    @elseif($mafsilPosition->difficulty == 3)<i class="fas fa-fire"></i>
                                    @else<i class="fas fa-balance-scale"></i>@endif
                                    {{ $mafsilPosition->difficultyLabel() }}
                                </span>
                                <span class="page-badge"><i class="fas fa-file-alt"></i> صفحة {{ $mafsilPosition->pageNumberLabel() }}</span>
                                <button type="button" class="btn-view-ayahs"
                                        onclick="showPositionAyahs({{ $mafsilPosition->id }})" title="عرض الآيات بالرسم العثماني">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="position-card-body">
                        <div class="position-stat">
                            <span class="stat-label"><i class="fas fa-times-circle" style="color: var(--danger);"></i>أخطاء</span>
                            <div class="scoring-input-group errors">
                                <button type="button" class="btn-errors" onclick="adjustMafsil('errors-{{ $partIndex }}-{{ $posIndex }}', -1, {{ $partIndex }}, {{ $posIndex }})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="pages[{{ $globalIndex }}][errors]" id="errors-{{ $partIndex }}-{{ $posIndex }}"
                                       value="0" min="0" max="50"
                                       oninput="calcMafsilRow({{ $partIndex }}, {{ $posIndex }})">
                                <button type="button" class="btn-errors" onclick="adjustMafsil('errors-{{ $partIndex }}-{{ $posIndex }}', 1, {{ $partIndex }}, {{ $posIndex }})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="position-stat">
                            <span class="stat-label"><i class="fas fa-question-circle" style="color: #b36b00;"></i>ترددات</span>
                            <div class="scoring-input-group hesitations">
                                <button type="button" class="btn-hesitations" onclick="adjustMafsil('hesitations-{{ $partIndex }}-{{ $posIndex }}', -1, {{ $partIndex }}, {{ $posIndex }})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="pages[{{ $globalIndex }}][hesitations]" id="hesitations-{{ $partIndex }}-{{ $posIndex }}"
                                       value="0" min="0" max="100"
                                       oninput="calcMafsilRow({{ $partIndex }}, {{ $posIndex }})">
                                <button type="button" class="btn-hesitations" onclick="adjustMafsil('hesitations-{{ $partIndex }}-{{ $posIndex }}', 1, {{ $partIndex }}, {{ $posIndex }})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="position-stat">
                            <span class="stat-label"><i class="fas fa-minus-circle" style="color: #6c757d;"></i>الخصم</span>
                            <span class="position-deduction" id="deduction-{{ $partIndex }}-{{ $posIndex }}">-0</span>
                        </div>
                        <div class="position-score-box" id="position-score-box-{{ $partIndex }}-{{ $posIndex }}">
                            <div class="score-label">العلامة</div>
                            <div class="score-value" id="part-score-{{ $partIndex }}-{{ $posIndex }}">0%</div>
                        </div>
                    </div>
                    <input type="hidden" name="pages[{{ $globalIndex }}][page_number]" value="{{ $part->part_number }}">
                    <input type="hidden" name="pages[{{ $globalIndex }}][position]" value="{{ $posIndex + 1 }}">
                    <input type="hidden" name="pages[{{ $globalIndex }}][is_placeholder]" value="{{ $isPlaceholder ? '1' : '0' }}">
                    @if(!$isPlaceholder)
                    <input type="hidden" name="pages[{{ $globalIndex }}][mafsil_position_id]" value="{{ $mafsilPosition->id }}">
                    <input type="hidden" name="pages[{{ $globalIndex }}][position_range]" value="{{ $mafsilPosition->positionRangeLabel() }}">
                    <input type="hidden" name="pages[{{ $globalIndex }}][page_number_in_mushaf]" value="{{ $mafsilPosition->pageNumberLabel() }}">
                    @else
                    <input type="hidden" name="pages[{{ $globalIndex }}][position_range]" value="">
                    <input type="hidden" name="pages[{{ $globalIndex }}][page_number_in_mushaf]" value="">
                    @endif
                </div>
                @php $globalIndex++; @endphp
                @endforeach
                @endif
            </div>
        </div>
        @endforeach

        <div class="score-summary">
            <div class="score-summary-header">
                <div class="summary-title">
                    <i class="fas fa-calculator" style="color: var(--gold-light);"></i>
                    ملخص النتيجة
                </div>
                <div class="small opacity-75">
                    <i class="fas fa-info-circle me-1"></i>
                    يتم التحديث تلقائياً مع كل تعديل
                </div>
            </div>
            <div class="score-summary-body">
                <div class="score-pills">
                    <div class="score-pill errors">
                        <span class="pill-label"><i class="fas fa-times-circle"></i>إجمالي الأخطاء</span>
                        <span class="pill-value" id="total-errors">0</span>
                    </div>
                    <div class="score-pill hesitations">
                        <span class="pill-label"><i class="fas fa-question-circle"></i>إجمالي الترددات</span>
                        <span class="pill-value" id="total-hesitations">0</span>
                    </div>
                    <div class="score-pill deduction">
                        <span class="pill-label"><i class="fas fa-minus-circle"></i>إجمالي الخصم</span>
                        <span class="pill-value" id="total-deduction">-0</span>
                    </div>
                    <div class="score-pill final" id="finalScorePill">
                        <span class="pill-label"><i class="fas fa-trophy"></i>العلامة النهائية</span>
                        <span class="pill-value" id="final-score">0%</span>
                    </div>
                </div>
                <div class="final-score-display">
                    <div>
                        <div class="label mb-1">المعدل العام للمفصل</div>
                        <div class="value" id="final-score-large">0%</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="threshold">
                            <i class="fas fa-flag-checkered me-1"></i>
                            علامة الرسوب: {{ $passingPercentage }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-card">
            <div class="page-card-body">
                <label class="form-label fw-bold mb-2" style="color: var(--navy); font-size: 14px;">
                    <i class="fas fa-sticky-note me-2" style="color: var(--gold);"></i>
                    ملاحظات (اختياري)
                </label>
                <textarea name="notes" class="form-control notes-textarea" rows="3" placeholder="أي ملاحظات حول الجلسة..."></textarea>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 justify-content-between mt-4 mb-5">
            <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i>رجوع
            </a>
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-secondary px-4" style="border-radius: var(--radius-sm); font-size: 13px; font-weight: 600;" onclick="submitMafsilIncomplete()">
                    <i class="fas fa-times-circle me-2"></i>
                    لم يكمل
                </button>
                <button type="button" class="save-btn" onclick="submitMafsil()">
                    <i class="fas fa-save me-2"></i>
                    حفظ نتيجة المفصل
                </button>
            </div>
        </div>
    </form>

    <!-- Failure Modal -->
    <div class="modal fade" id="failureModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تحذير - الطالب راسب</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-5">
                    <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                    <p class="fs-5">علامة الطالب أقل من <strong>{{ $passingPercentage }}%</strong></p>
                    <p class="text-muted">هل تريد الإكمال أم الإيقاف؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="stopForFailure()">
                        <i class="fas fa-stop me-2"></i>إيقاف
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="continueAfterFailure()">
                        <i class="fas fa-play me-2"></i>إكمال
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ayahs Modal -->
    <div class="modal fade" id="ayahsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>
                        عرض الآيات بالرسم العثماني
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="ayahsModalContent" style="min-height: 200px;">
                        <div class="d-flex justify-content-center align-items-center h-100 py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script>
const maxScore = {{ $maxScore }};
const partsCount = {{ $partsCount }};
const passingPercentage = {{ $passingPercentage }};
let hasInputBeenMade = false;
let failureShown = false;
let failedButContinuing = false;
let initialFailedRecordSaved = false;

function showPositionAyahs(mafsilPositionId) {
    const modal = new bootstrap.Modal(document.getElementById('ayahsModal'));
    const contentEl = document.getElementById('ayahsModalContent');

    contentEl.innerHTML = `
        <div class="d-flex justify-content-center align-items-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
        </div>
    `;

    modal.show();

    fetch(`{{ url('supervisor/recitation/mafsil-position') }}/${mafsilPositionId}/ayahs`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            contentEl.innerHTML = data.html || '<p class="text-muted text-center py-5">لا توجد آيات</p>';
        })
        .catch(error => {
            contentEl.innerHTML = '<p class="text-danger text-center py-5">حدث خطأ أثناء تحميل الآيات</p>';
            console.error('Error loading ayahs:', error);
        });
}

// Dynamic positions per part from server
const positionsPerPartByIndex = {
@foreach($parts as $partIndex => $part)
    {{ $partIndex }}: {{ ($mafsilPositions[$part->part_number] ?? collect())->count() }},
@endforeach
};

function getPositionsForPart(partIndex) {
    return positionsPerPartByIndex[partIndex] || 0;
}

document.addEventListener('DOMContentLoaded', function() {
    const currentScoreEl = document.getElementById('currentScore');
    if (currentScoreEl) currentScoreEl.textContent = '100%';
});

function adjustMafsil(id, delta, partIndex, posIndex) {
    const input = document.getElementById(id);
    let val = parseInt(input.value) + delta;
    val = Math.max(0, val);
    input.value = val;
    hasInputBeenMade = true;
    calcMafsilRow(partIndex, posIndex);
}

function calcMafsilRow(partIndex, posIndex) {
    const errors = parseInt(document.getElementById('errors-' + partIndex + '-' + posIndex).value) || 0;
    const hesitations = parseInt(document.getElementById('hesitations-' + partIndex + '-' + posIndex).value) || 0;

    hasInputBeenMade = true;

    const errorDeduction = errors * 3;
    const hesitationDeduction = Math.floor(hesitations / 3) + ((hesitations % 3) * 0.33);
    const totalDeduction = errorDeduction + hesitationDeduction;
    const rowScore = Math.max(0, maxScore - totalDeduction);

    document.getElementById('deduction-' + partIndex + '-' + posIndex).textContent = '-' + totalDeduction.toFixed(2);

    const scoreEl = document.getElementById('part-score-' + partIndex + '-' + posIndex);
    scoreEl.textContent = rowScore.toFixed(1) + '%';

    const scoreBox = document.getElementById('position-score-box-' + partIndex + '-' + posIndex);
    if (scoreBox) {
        scoreBox.className = 'position-score-box' + (rowScore >= 80 ? ' success' : rowScore >= 60 ? ' warning' : '');
    }

    updateTotals();
}

function updateTotals() {
    let totalErrors = 0, totalHesitations = 0, totalDeduction = 0;

    for (let partIndex = 0; partIndex < partsCount; partIndex++) {
        const positions = getPositionsForPart(partIndex);
        for (let posIndex = 0; posIndex < positions; posIndex++) {
            const errorsInput = document.getElementById('errors-' + partIndex + '-' + posIndex);
            const hesitationsInput = document.getElementById('hesitations-' + partIndex + '-' + posIndex);
            if (!errorsInput || !hesitationsInput) continue;

            const errors = parseInt(errorsInput.value) || 0;
            const hesitations = parseInt(hesitationsInput.value) || 0;
            totalErrors += errors;
            totalHesitations += hesitations;

            const errorDeduction = errors * 3;
            const hesitationDeduction = Math.floor(hesitations / 3) + ((hesitations % 3) * 0.33);
            totalDeduction += errorDeduction + hesitationDeduction;
        }
    }

    const finalScore = Math.max(0, maxScore - totalDeduction);

    document.getElementById('total-errors').textContent = totalErrors;
    document.getElementById('total-hesitations').textContent = totalHesitations;
    document.getElementById('total-deduction').textContent = '-' + totalDeduction.toFixed(2);

    const finalScoreEl = document.getElementById('final-score');
    const finalScorePill = document.getElementById('finalScorePill');
    const finalScoreLarge = document.getElementById('final-score-large');
    const currentScoreEl = document.getElementById('currentScore');

    if (!hasInputBeenMade) {
        finalScoreEl.textContent = passingPercentage + '% (راسب)';
        if (finalScoreLarge) {
            finalScoreLarge.textContent = '0%';
            finalScoreLarge.className = 'value danger';
        }
        if (currentScoreEl) currentScoreEl.textContent = '100%';
        finalScorePill.className = 'score-pill final danger';
        if (currentScoreEl) currentScoreEl.className = 'badge bg-primary score-badge';
        return;
    }

    const scoreText = finalScore.toFixed(1) + '%';
    finalScoreEl.textContent = scoreText;
    if (finalScoreLarge) {
        finalScoreLarge.textContent = scoreText;
    }
    if (currentScoreEl) currentScoreEl.textContent = scoreText;

    if (finalScore >= maxScore * 0.8) {
        finalScorePill.className = 'score-pill final success';
        if (finalScoreLarge) finalScoreLarge.className = 'value success';
        if (currentScoreEl) currentScoreEl.className = 'badge bg-success score-badge';
    } else if (finalScore >= maxScore * 0.6) {
        finalScorePill.className = 'score-pill final warning';
        if (finalScoreLarge) finalScoreLarge.className = 'value warning';
        if (currentScoreEl) currentScoreEl.className = 'badge bg-warning text-dark score-badge';
    } else {
        finalScorePill.className = 'score-pill final danger';
        if (finalScoreLarge) finalScoreLarge.className = 'value danger';
        if (currentScoreEl) currentScoreEl.className = 'badge bg-danger score-badge';
    }

    if (finalScore < passingPercentage && !failureShown && !failedButContinuing && hasInputBeenMade) {
        failureShown = true;
        new bootstrap.Modal(document.getElementById('failureModal')).show();
    }
}

function submitMafsil() {
    const form = document.getElementById('mafsilForm');
    const data = new FormData(form);
    for (let [key, value] of data.entries()) {
        if (key.includes('[errors]') || key.includes('[hesitations]') || key.includes('[page_number]') || key.includes('[position]')) {
            data.set(key, parseInt(value, 10) || 0);
        }
    }

    if (failedButContinuing && initialFailedRecordSaved) {
        data.append('is_final_submission', '1');
    }

    $.ajax({
        url: '{{ route('supervisor.recitation.save') }}',
        method: 'POST',
        data: data,
        processData: false,
        contentType: false,
        success: function() {
            toastr.success('تم حفظ نتيجة المفصل بنجاح');
            setTimeout(() => window.location.href = '{{ route('supervisor.recitation.index') }}', 1500);
        },
        error: function(xhr) {
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            }
            toastr.error(errorMsg);
        }
    });
}

function stopForFailure() {
    const form = document.getElementById('mafsilForm');

    let failedInput = form.querySelector('input[name="failed"]');
    if (!failedInput) {
        failedInput = document.createElement('input');
        failedInput.type = 'hidden';
        failedInput.name = 'failed';
        form.appendChild(failedInput);
    }
    failedInput.value = '1';

    bootstrap.Modal.getInstance(document.getElementById('failureModal')).hide();
    submitMafsil();
}

function continueAfterFailure() {
    const form = document.getElementById('mafsilForm');
    const data = new FormData(form);
    data.append('failed_continued', '1');

    failedButContinuing = true;

    $.ajax({
        url: '{{ route('supervisor.recitation.saveFailedContinue') }}',
        method: 'POST',
        data: data,
        processData: false,
        contentType: false,
        success: function() {
            initialFailedRecordSaved = true;
            toastr.info('تم تسجيل أن الطالب راسب وسيكمل');
        },
        error: function(xhr) {
            let errorMsg = 'حدث خطأ في حفظ السجل';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            }
            toastr.error(errorMsg);
        }
    });
}

function submitMafsilIncomplete() {
    if (!confirm('هل أنت متأكد من حفظ هذه الجلسة على أنها لم يكمل؟')) {
        return;
    }
    const form = document.getElementById('mafsilForm');
    const data = new FormData(form);
    for (let [key, value] of data.entries()) {
        if (key.includes('[errors]') || key.includes('[hesitations]') || key.includes('[page_number]') || key.includes('[position]')) {
            data.set(key, parseInt(value, 10) || 0);
        }
    }

    $.ajax({
        url: '{{ route('supervisor.recitation.saveIncomplete') }}',
        method: 'POST',
        data: data,
        processData: false,
        contentType: false,
        timeout: 30000,
        success: function() {
            toastr.success('تم حفظ الجلسة بنجاح (لم يكمل)');
            setTimeout(() => window.location.href = '{{ route('supervisor.recitation.index') }}', 1500);
        },
        error: function(xhr) {
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            }
            toastr.error(errorMsg);
        }
    });
}
</script>
@endpush
