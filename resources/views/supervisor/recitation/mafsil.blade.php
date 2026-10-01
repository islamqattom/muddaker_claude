@extends('layouts.app')

@section('title', 'تسميع - مفصل')
@section('page-title', 'جلسة مفصل'.' - '.$mafsilType)
@section('role-label', 'مشرف')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
:root {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --gold-soft: #fcf6e8;
    --navy: #182B48;
    --navy-light: #2A4570;
    --navy-deep: #0f1c33;
    --success: #16a34a;
    --success-soft: #dcfce7;
    --warning: #f59e0b;
    --warning-soft: #fef3c7;
    --danger: #dc2626;
    --danger-soft: #fee2e2;
    --info: #0ea5e9;
    --info-soft: #e0f2fe;
    --purple: #8b5cf6;
    --purple-soft: #ede9fe;
    --radius: 13px;
    --radius-sm: 13px;
    --radius-xs: 9px;
    --shadow-sm: 0 1px 3px rgba(15, 28, 51, 0.04), 0 2px 8px rgba(15, 28, 51, 0.04);
    --shadow: 0 4px 12px rgba(15, 28, 51, 0.06), 0 12px 28px rgba(15, 28, 51, 0.08);
    --shadow-lg: 0 12px 32px rgba(15, 28, 51, 0.12);
    --shadow-gold: 0 8px 24px rgba(212, 160, 48, 0.25);
}

.rec-session { color: var(--navy); overflow-x: clip; }

/* =========================
   PAGE HERO - Compact Modern
   ========================= */
.rec-session .session-hero {
    background:
        radial-gradient(circle at 0% 0%, rgba(212,160,48,0.18), transparent 45%),
        radial-gradient(circle at 100% 100%, rgba(14,165,233,0.12), transparent 50%),
        linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius);
    padding: 0;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.rec-session .session-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.rec-session .hero-top {
    padding: 16px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 18px;
    position: relative;
    z-index: 1;
}

.rec-session .student-block {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
}

.rec-session .student-avatar {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    background: linear-gradient(135deg, #f1c40f 0%, var(--gold) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--navy-deep);
    box-shadow: 0 8px 20px rgba(212, 160, 48, 0.4);
    flex-shrink: 0;
    position: relative;
}

.rec-session .student-avatar::after {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 16px;
    border: 2px solid rgba(255,255,255,0.25);
    pointer-events: none;
}

.rec-session .student-name {
    font-size: 18px;
    font-weight: 800;
    color: white;
    margin: 0 0 6px 0;
    line-height: 1.2;
}

.rec-session .student-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.rec-session .chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 999px;
    background: rgba(255,255,255,0.12);
    color: white;
    backdrop-filter: blur(8px);
}

.rec-session .chip.gold { background: rgba(212,160,48,0.25); color: #ffe9b3; }
.rec-session .chip.cyan { background: rgba(14,165,233,0.25); color: #bae6fd; }

.rec-session .hero-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.rec-session .stat-pill {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-sm);
    padding: 10px 16px;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s ease;
}

.rec-session .stat-pill:hover {
    background: rgba(255,255,255,0.16);
    transform: translateY(-1px);
}

.rec-session .stat-pill .stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.rec-session .stat-pill.pass .stat-icon { background: rgba(22,163,74,0.3); color: #bbf7d0; }
.rec-session .stat-pill.score .stat-icon { background: rgba(212,160,48,0.3); color: #fde68a; }

.rec-session .stat-pill .stat-text { display: flex; flex-direction: column; line-height: 1.1; }
.rec-session .stat-pill .stat-text .lbl { font-size: 10px; opacity: 0.75; font-weight: 600; }
.rec-session .stat-pill .stat-text .val { font-size: 16px; font-weight: 800; }

.rec-session .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 12.5px;
    color: rgba(255,255,255,0.85);
    text-align: center;
    padding: 12px 16px 14px;
    border-top: 1px solid rgba(255,255,255,0.1);
    margin: 0;
    position: relative;
    z-index: 1;
    background: rgba(0,0,0,0.12);
}

/* =========================
   CARDS
   ========================= */
.rec-session .card-modern {
    background: white;
    border: 1px solid rgba(15, 28, 51, 0.06);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.rec-session .card-modern.hoverable:hover {
    box-shadow: var(--shadow);
}

.rec-session .card-header-modern {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: linear-gradient(135deg, rgba(15, 28, 51, 0.02) 0%, rgba(42, 69, 112, 0.04) 100%);
    border-bottom: 1px solid rgba(15, 28, 51, 0.05);
}

.rec-session .card-header-modern.solid {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    border-bottom: none;
}

.rec-session .section-title {
    color: var(--navy);
    font-size: 14px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.rec-session .card-header-modern.solid .section-title { color: white; }

.rec-session .section-title .title-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    color: white;
    font-size: 13px;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(212, 160, 48, 0.3);
}

.rec-session .card-body-modern { padding: 16px; }

/* =========================
   INFO ALERT
   ========================= */
.rec-session .info-alert {
    border-radius: var(--radius-sm);
    border: 1px solid rgba(14, 165, 233, 0.15);
    padding: 14px 16px;
    background: linear-gradient(135deg, var(--info-soft) 0%, #f0f9ff 100%);
    color: var(--navy);
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.rec-session .info-alert i.fa-info-circle {
    color: var(--info);
    font-size: 18px;
    flex-shrink: 0;
    margin-top: 2px;
}

.rec-session .info-alert .alert-content { line-height: 1.6; font-size: 13.5px; }
.rec-session .info-alert .alert-content strong { color: var(--navy-deep); }
.rec-session .info-alert .badge { font-weight: 700; padding: 4px 8px; font-size: 11px; }

/* =========================
   SELECTION PROGRESS BAR
   ========================= */
.rec-session .progress-card {
    background: white;
    border-radius: var(--radius);
    padding: 14px 16px;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(15, 28, 51, 0.05);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.rec-session .progress-card .progress-icon {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    box-shadow: var(--shadow-gold);
    flex-shrink: 0;
}

.rec-session .progress-card .progress-text-block { flex: 1; min-width: 200px; }

.rec-session .progress-card .progress-text-block .label {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 4px;
}

.rec-session .progress-card .progress-text-block .label strong {
    color: var(--navy);
    font-size: 15px;
    margin-inline-start: 4px;
}

.rec-session .progress-card .progress-track {
    flex: 1;
    min-width: 180px;
    height: 10px;
    background: #eef0f3;
    border-radius: 50px;
    overflow: hidden;
    position: relative;
}

.rec-session .progress-card .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 50px;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.rec-session .progress-card .progress-fill::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.rec-session .progress-card .progress-percent {
    font-size: 16px;
    font-weight: 800;
    color: var(--gold);
    min-width: 50px;
    text-align: center;
}

/* =========================
   SELECTION MODE - Search & Quick Presets
   ========================= */
.rec-session .selection-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
    padding: 14px;
    background: #f8fafc;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(15, 28, 51, 0.05);
}

.rec-session .search-box {
    flex: 1;
    min-width: 200px;
    position: relative;
}

.rec-session .search-box i {
    position: absolute;
    inset-inline-start: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
}

.rec-session .search-box input {
    width: 100%;
    padding: 10px 14px 10px 38px;
    border: 1.5px solid #e2e8f0;
    border-radius: var(--radius-sm);
    background: white;
    font-size: 13px;
    color: var(--navy);
    transition: all 0.2s ease;
}

.rec-session .search-box input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 160, 48, 0.12);
}

.rec-session .quick-presets {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.rec-session .preset-btn {
    padding: 8px 14px;
    border: 1.5px solid #e2e8f0;
    background: white;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--navy);
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.rec-session .preset-btn:hover {
    border-color: var(--gold);
    color: var(--gold);
    background: var(--gold-soft);
    transform: translateY(-1px);
}

.rec-session .preset-btn.active {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    border-color: var(--navy);
}

/* =========================
   PART CHECKBOX CARDS
   ========================= */
.rec-session .part-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(132px, 1fr));
    gap: 10px;
}

.rec-session .part-checkbox-card {
    position: relative;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
    padding: 12px 12px 10px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    overflow: hidden;
    user-select: none;
    box-shadow: 0 1px 2px rgba(15, 28, 51, 0.03);
}

.rec-session .part-checkbox-card.hidden { display: none; }

.rec-session .part-checkbox-card::before {
    content: '';
    position: absolute;
    top: 0; left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
    transform: translateX(-50%);
    transition: width 0.3s ease;
    border-radius: 0 0 2px 2px;
}

.rec-session .part-checkbox-card:hover {
    border-color: var(--gold-light);
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(15, 28, 51, 0.08);
}

.rec-session .part-checkbox-card:hover::before { width: 40%; }

.rec-session .part-checkbox-card.selected {
    border-color: var(--navy);
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    box-shadow: 0 6px 18px rgba(24, 43, 72, 0.25);
    transform: translateY(-2px);
}

.rec-session .part-checkbox-card.selected::before {
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
}

.rec-session .part-checkbox-card .check-circle {
    position: absolute;
    top: 6px;
    inset-inline-end: 6px;
    width: 18px;
    height: 18px;
    border: 1.5px solid #cbd5e1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    transition: all 0.2s ease;
    background: white;
    z-index: 2;
    font-size: 9px;
}

.rec-session .part-checkbox-card:hover .check-circle { border-color: var(--gold); }

.rec-session .part-checkbox-card.selected .check-circle {
    background: var(--gold);
    border-color: var(--gold);
    color: white;
}

.rec-session .part-checkbox-card .part-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    color: var(--navy);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 800;
    flex-shrink: 0;
    transition: all 0.25s ease;
    border: 1px solid rgba(15, 28, 51, 0.04);
}

.rec-session .part-checkbox-card:hover .part-icon-wrap {
    background: linear-gradient(135deg, var(--gold-soft) 0%, #fef3c7 100%);
    color: var(--gold);
}

.rec-session .part-checkbox-card.selected .part-icon-wrap {
    background: rgba(255,255,255,0.15);
    color: var(--gold-light);
    border-color: rgba(255,255,255,0.2);
}

.rec-session .part-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    min-width: 0;
    width: 100%;
}

.rec-session .part-number {
    font-size: 13px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1.2;
    display: flex;
    align-items: baseline;
    gap: 3px;
    transition: color 0.25s ease;
}

.rec-session .part-number .num-en {
    font-size: 15px;
    font-weight: 800;
    color: var(--navy);
    letter-spacing: 0;
    font-feature-settings: "tnum";
}

.rec-session .part-checkbox-card.selected .part-number,
.rec-session .part-checkbox-card.selected .part-number .num-en { color: white; }

.rec-session .part-range {
    font-size: 10px;
    color: #94a3b8;
    font-weight: 600;
    line-height: 1.2;
    transition: color 0.25s ease;
    direction: ltr;
}

.rec-session .part-checkbox-card.selected .part-range { color: rgba(255,255,255,0.7); }

.rec-session .part-checkbox-card .card-footer {
    width: 100%;
    padding-top: 6px;
    border-top: 1px dashed rgba(15, 28, 51, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    transition: border-color 0.25s ease;
}

.rec-session .part-checkbox-card.selected .card-footer { border-top-color: rgba(255,255,255,0.15); }

.rec-session .part-pages-count {
    font-size: 9px;
    font-weight: 700;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    transition: color 0.25s ease;
}

.rec-session .part-pages-count i { font-size: 8px; }
.rec-session .part-checkbox-card.selected .part-pages-count { color: var(--gold-light); }

.rec-session .order-badge {
    background: var(--gold);
    color: white;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 800;
    line-height: 1;
    box-shadow: 0 2px 4px rgba(212, 160, 48, 0.4);
    animation: badgePop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes badgePop {
    0% { transform: scale(0); opacity: 0; }
    60% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

.rec-session .part-checkbox-card.selected .order-badge { display: inline-flex; }

.rec-session .part-checkbox-card input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    width: 0; height: 0;
}

/* =========================
   DIFFICULTY DISTRIBUTION
   ========================= */
.rec-session .distribution-container {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: var(--radius);
    padding: 16px;
    margin-top: 8px;
    border: 1px solid rgba(15, 28, 51, 0.06);
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.rec-session .distribution-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px dashed rgba(15, 28, 51, 0.1);
}

.rec-session .distribution-header .header-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--info) 0%, #38bdf8 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.rec-session .distribution-header .header-text h6 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: var(--navy);
}

.rec-session .distribution-header .header-text p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #64748b;
}

.rec-session .distribution-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    width: 100%;
    align-items: stretch;
}

.rec-session .distribution-grid > [class*="col-"] {
    display: flex;
    flex: 0 0 auto;
    width: 100%;
    padding: 0;
}

@media (min-width: 576px) {
    .rec-session .distribution-grid > [class*="col-"] { width: calc(50% - 6px); }
}
@media (min-width: 992px) {
    .rec-session .distribution-grid > [class*="col-"] { width: calc(33.333% - 8px); }
}

.rec-session .distribution-card {
    position: relative;
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
    background: white;
    border: 1.5px solid rgba(15, 28, 51, 0.08);
    border-radius: var(--radius-sm);
    padding: 12px;
    transition: all 0.2s ease;
    overflow: hidden;
}

.rec-session .distribution-card:hover { box-shadow: var(--shadow); border-color: var(--gold-light); }
.rec-session .distribution-card.complete {
    border-color: var(--success);
    background: linear-gradient(135deg, white 0%, var(--success-soft) 100%);
    box-shadow: 0 3px 8px rgba(22, 163, 74, 0.1);
    padding: 9px 10px;
    border-width: 1.5px;
}
.rec-session .distribution-card.complete::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 7px;
    inset-inline-end: 7px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--success);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    box-shadow: 0 1px 4px rgba(22, 163, 74, 0.35);
    z-index: 2;
    flex-shrink: 0;
}
.rec-session .distribution-card.complete .card-part-title {
    padding-inline-end: 24px;
    flex-wrap: wrap;
    min-width: 0;
    font-size: 13px;
    margin-bottom: 8px;
}
.rec-session .distribution-card.complete .card-part-title .part-num-badge {
    background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%);
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    font-size: 11px;
}
.rec-session .distribution-card.complete .difficulty-counter .counter-label {
    font-size: 10px;
    margin-bottom: 4px;
}
.rec-session .distribution-card.complete .difficulty-counter input {
    height: 30px;
    font-size: 12px;
    border-width: 1.5px;
}
.rec-session .distribution-card.complete .counter-btn {
    width: 24px;
    height: 24px;
    font-size: 10px;
    border-radius: 6px;
}
.rec-session .distribution-card.complete .distribution-status {
    font-size: 11px;
    margin-top: 8px;
    padding: 5px 8px;
}
.rec-session .distribution-card .card-part-title {
    min-width: 0;
    flex-wrap: wrap;
    word-break: break-word;
}
.rec-session .distribution-card .difficulty-counters {
    flex: 1 1 auto;
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 6px;
    align-items: stretch;
    min-width: 0;
    width: 100%;
}
.rec-session .distribution-card .difficulty-counter {
    flex: 1 1 0;
    min-width: 80px;
}
.rec-session .distribution-card.complete .difficulty-counter {
    flex: 1 1 0;
    min-width: 80px;
}
.rec-session .distribution-card.complete .difficulty-counter input {
    border-color: rgba(22, 163, 74, 0.4);
    background: rgba(255, 255, 255, 0.7);
    color: var(--success);
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
.rec-session .distribution-card.complete .difficulty-counter.easy input,
.rec-session .distribution-card.complete .difficulty-counter.medium input,
.rec-session .distribution-card.complete .difficulty-counter.hard input {
    border-color: rgba(22, 163, 74, 0.5);
    color: var(--success);
}
.rec-session .distribution-card.complete .difficulty-counter .counter-label { color: var(--success); }
.rec-session .distribution-card .distribution-status {
    margin-top: auto;
    flex-shrink: 0;
}

.rec-session .distribution-card .card-part-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--navy);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rec-session .distribution-card .card-part-title .part-num-badge {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
}

.rec-session .difficulty-counters {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 8px;
    width: 100%;
}

.rec-session .difficulty-counter { text-align: center; }

.rec-session .difficulty-counter .counter-label {
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 6px;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.rec-session .difficulty-counter.easy .counter-label { color: var(--success); }
.rec-session .difficulty-counter.medium .counter-label { color: var(--warning); }
.rec-session .difficulty-counter.hard .counter-label { color: var(--danger); }

.rec-session .counter-box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
    width: 100%;
    flex-wrap: nowrap;
}

.rec-session .difficulty-counter input {
    flex: 1 1 28px;
    min-width: 28px;
    width: auto;
    max-width: 100%;
    height: 34px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    text-align: center;
    font-weight: 800;
    font-size: 13px;
    color: var(--navy);
    padding: 0 2px;
    background: white;
    transition: all 0.2s ease;
    box-sizing: border-box;
    -moz-appearance: textfield;
    font-feature-settings: "tnum";
}

.rec-session .difficulty-counter.easy input { border-color: rgba(22, 163, 74, 0.25); }
.rec-session .difficulty-counter.medium input { border-color: rgba(245, 158, 11, 0.25); }
.rec-session .difficulty-counter.hard input { border-color: rgba(220, 38, 38, 0.25); }

.rec-session .difficulty-counter input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 160, 48, 0.12);
}

.rec-session .difficulty-counter input::-webkit-outer-spin-button,
.rec-session .difficulty-counter input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.rec-session .counter-btn {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 800;
    transition: all 0.15s ease;
    color: white;
    cursor: pointer;
}

.rec-session .counter-btn:active { transform: scale(0.92); }

.rec-session .difficulty-counter.easy .counter-btn { background: var(--success); }
.rec-session .difficulty-counter.medium .counter-btn { background: var(--warning); }
.rec-session .difficulty-counter.hard .counter-btn { background: var(--danger); }
.rec-session .difficulty-counter.easy .counter-btn:hover { background: #15803d; }
.rec-session .difficulty-counter.medium .counter-btn:hover { background: #d97706; }
.rec-session .difficulty-counter.hard .counter-btn:hover { background: #b91c1c; }

.rec-session .distribution-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 999px;
    margin-top: 12px;
    width: 100%;
    justify-content: center;
}

.rec-session .distribution-status.valid {
    background: var(--success-soft);
    color: var(--success);
}

.rec-session .distribution-status.invalid {
    background: var(--danger-soft);
    color: var(--danger);
}

/* =========================
   STICKY ACTION BAR
   ========================= */
.rec-session .action-bar-sticky {
    position: sticky;
    bottom: 0;
    background: white;
    border-top: 1px solid rgba(15, 28, 51, 0.08);
    padding: 14px 22px;
    margin: 22px -22px -22px;
    box-shadow: 0 -8px 24px rgba(15, 28, 51, 0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    z-index: 20;
    backdrop-filter: blur(10px);
    background: rgba(255,255,255,0.95);
}

.rec-session .btn-primary-modern {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: var(--radius-sm);
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 700;
    color: white;
    transition: all 0.25s ease;
    box-shadow: 0 4px 15px rgba(24, 43, 72, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.rec-session .btn-primary-modern:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.4);
    color: white;
}

.rec-session .btn-primary-modern:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

.rec-session .btn-outline-modern {
    border: 1.5px solid #e2e8f0;
    border-radius: var(--radius-sm);
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    background: white;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
}

.rec-session .btn-outline-modern:hover {
    background: #f8fafc;
    border-color: var(--navy);
    color: var(--navy);
}

/* =========================
   POSITION CARDS (Scoring Mode)
   ========================= */
.rec-session .part-section {
    background: white;
    border: 1px solid rgba(15, 28, 51, 0.06);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: var(--shadow-sm);
}

.rec-session .part-section-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    position: relative;
    overflow: hidden;
}

.rec-session .part-section-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.rec-session .part-section-header > * { position: relative; z-index: 1; }

.rec-session .part-section-header .part-title {
    font-size: 15px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
}

.rec-session .part-section-header .part-title .title-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

.rec-session .part-section-header .part-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.rec-session .part-section-header .meta-chip {
    background: rgba(255,255,255,0.15);
    color: white;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.rec-session .part-section-header .meta-chip strong { font-weight: 800; }

.rec-session .part-section-body { padding: 14px; }

.rec-session .position-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(280px, 2fr);
    gap: 0;
}

.rec-session .position-card:last-child { margin-bottom: 0; }
.rec-session .position-card:hover { border-color: var(--gold-light); box-shadow: 0 4px 12px rgba(15, 28, 51, 0.06); }
.rec-session .position-card.has-errors { border-color: var(--danger); }
.rec-session .position-card.has-hesitations { border-color: var(--warning); }

.rec-session .position-card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: none;
    border-inline-end: 1px solid #f1f5f9;
    flex-wrap: wrap;
    min-width: 0;
}

.rec-session .position-number {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(24, 43, 72, 0.2);
    font-feature-settings: "tnum";
}

.rec-session .position-info { flex: 1; min-width: 0; }

.rec-session .position-range {
    font-size: 13px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1.3;
    margin-bottom: 2px;
    font-family: 'Tajawal', sans-serif;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rec-session .position-sub {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 10px;
    color: #64748b;
    font-weight: 600;
}

.rec-session .position-sub .sub-item {
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.rec-session .position-sub i { font-size: 8px; color: #cbd5e1; }

.rec-session .position-meta {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}

.rec-session .difficulty-badge {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 999px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    line-height: 1;
}

.rec-session .difficulty-badge i { font-size: 9px; }

.rec-session .difficulty-badge.easy { background: var(--success-soft); color: var(--success); }
.rec-session .difficulty-badge.medium { background: var(--warning-soft); color: #b45309; }
.rec-session .difficulty-badge.hard { background: var(--danger-soft); color: var(--danger); }

.rec-session .page-badge {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 999px;
    background: var(--info-soft);
    color: var(--info);
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    line-height: 1;
}

.rec-session .btn-view-ayahs {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: 1px solid var(--info-soft);
    background: white;
    color: var(--info);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 11px;
    cursor: pointer;
    flex-shrink: 0;
}

.rec-session .btn-view-ayahs:hover {
    background: var(--info);
    color: white;
    border-color: var(--info);
    transform: scale(1.05);
}

.rec-session .position-card-body {
    padding: 8px 12px;
    display: grid;
    grid-template-columns: 1fr 1fr auto auto;
    gap: 8px;
    align-items: center;
    min-width: 0;
}

.rec-session .position-card-tajweed {
    grid-column: 1 / -1;
    padding: 8px 12px 10px;
    border-top: 1px dashed rgba(15, 28, 51, 0.08);
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    min-width: 0;
}

.rec-session .btn-tajweed-attach {
    background: linear-gradient(135deg, var(--gold) 0%, #b8941f 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(212, 160, 48, 0.25);
    flex-shrink: 0;
}
.rec-session .btn-tajweed-attach:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(212, 160, 48, 0.35); }
.rec-session .btn-tajweed-attach:active { transform: scale(0.97); }

.rec-session .tajweed-count-badge {
    background: white;
    color: var(--gold);
    border-radius: 10px;
    padding: 1px 7px;
    font-size: 10px;
    font-weight: 800;
    margin-inline-start: 4px;
}

.rec-session .tajweed-summary {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.rec-session .tajweed-summary .tajweed-chip {
    background: rgba(212, 160, 48, 0.12);
    color: #8a6a14;
    border: 1px solid rgba(212, 160, 48, 0.35);
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.rec-session .tajweed-summary .tajweed-chip .remove-chip {
    cursor: pointer;
    color: #b91c1c;
    font-weight: 800;
    margin-inline-start: 2px;
}

.rec-session .tajweed-errors-container { display: none; }

.rec-session .position-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 6px 5px 10px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #eef0f3;
    transition: all 0.2s ease;
    min-width: 0;
}

.rec-session .position-stat.has-value { background: white; }

.rec-session .position-stat.errors { color: var(--danger); }
.rec-session .position-stat.errors.has-value { background: var(--danger-soft); border-color: rgba(220, 38, 38, 0.2); }
.rec-session .position-stat.hesitations { color: #b45309; }
.rec-session .position-stat.hesitations.has-value { background: var(--warning-soft); border-color: rgba(245, 158, 11, 0.2); }
.rec-session .position-stat.deduction { color: #64748b; background: transparent; border-color: transparent; padding: 5px 4px; }
.rec-session .position-stat.score { padding: 0; background: transparent; border: none; }

.rec-session .position-stat .stat-label {
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    flex-shrink: 0;
}

.rec-session .position-stat.errors.has-value .stat-label,
.rec-session .position-stat.hesitations.has-value .stat-label { color: inherit; }

.rec-session .scoring-input-group {
    display: flex;
    align-items: center;
    gap: 3px;
    margin-inline-start: auto;
}

.rec-session .scoring-input-group button {
    width: 24px;
    height: 26px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 700;
    transition: all 0.15s ease;
    flex-shrink: 0;
    cursor: pointer;
    color: white;
}

.rec-session .scoring-input-group button:active { transform: scale(0.92); }

.rec-session .scoring-input-group .btn-errors { background: var(--danger); }
.rec-session .scoring-input-group .btn-errors:hover { background: #b91c1c; }
.rec-session .scoring-input-group .btn-hesitations { background: var(--warning); }
.rec-session .scoring-input-group .btn-hesitations:hover { background: #d97706; }

.rec-session .scoring-input-group input {
    width: 34px;
    height: 26px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    text-align: center;
    font-weight: 800;
    font-size: 12px;
    color: var(--navy);
    padding: 0;
    background: white;
    font-feature-settings: "tnum";
}

.rec-session .scoring-input-group.errors input { border-color: rgba(220, 38, 38, 0.3); }
.rec-session .scoring-input-group.hesitations input { border-color: rgba(245, 158, 11, 0.3); }

.rec-session .scoring-input-group input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 2px rgba(212, 160, 48, 0.15);
}

.rec-session .position-deduction {
    text-align: center;
    font-size: 13px;
    font-weight: 800;
    color: #94a3b8;
    padding: 0 6px;
    white-space: nowrap;
    font-feature-settings: "tnum";
}

.rec-session .position-deduction.has-value { color: var(--danger); }

.rec-session .position-score-box {
    background: linear-gradient(135deg, var(--danger-soft) 0%, #fef2f2 100%);
    border: 1px solid rgba(220, 38, 38, 0.2);
    border-radius: 10px;
    padding: 6px 12px;
    text-align: center;
    transition: all 0.3s ease;
    min-width: 72px;
}

.rec-session .position-score-box.success {
    background: linear-gradient(135deg, var(--success-soft) 0%, #f0fdf4 100%);
    border-color: rgba(22, 163, 74, 0.25);
}

.rec-session .position-score-box.warning {
    background: linear-gradient(135deg, var(--warning-soft) 0%, #fffbeb 100%);
    border-color: rgba(245, 158, 11, 0.25);
}

.rec-session .position-score-box .score-label {
    font-size: 9px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    margin-bottom: 0;
    letter-spacing: 0.3px;
    line-height: 1;
}

.rec-session .position-score-box .score-value {
    font-size: 15px;
    font-weight: 800;
    color: var(--danger);
    line-height: 1.2;
    transition: color 0.2s ease;
    font-feature-settings: "tnum";
}

.rec-session .position-score-box.success .score-value { color: var(--success); }
.rec-session .position-score-box.warning .score-value { color: #b45309; }

/* =========================
   SCORE SUMMARY (Sticky Dashboard)
   ========================= */
.rec-session .score-dashboard {
    position: sticky;
    top: 70px;
    z-index: 15;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(15, 28, 51, 0.06);
    margin-bottom: 16px;
    overflow: hidden;
}

.rec-session .score-dashboard-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.rec-session .score-dashboard-header .summary-title {
    font-size: 14px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
}

.rec-session .score-dashboard-header .summary-title .title-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

.rec-session .auto-update-chip {
    font-size: 11px;
    background: rgba(255,255,255,0.15);
    color: white;
    padding: 5px 12px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.rec-session .auto-update-chip .pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
    animation: pulse 1.6s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(74, 222, 128, 0); }
    100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
}

.rec-session .score-dashboard-body {
    padding: 16px;
    display: grid;
    grid-template-columns: 1.4fr 2fr;
    gap: 16px;
    align-items: center;
}

.rec-session .score-ring-wrapper {
    display: flex;
    align-items: center;
    gap: 18px;
}

.rec-session .score-ring {
    position: relative;
    width: 130px;
    height: 130px;
    flex-shrink: 0;
}

.rec-session .score-ring svg { transform: rotate(-90deg); width: 100%; height: 100%; }

.rec-session .score-ring .ring-bg {
    stroke: #e2e8f0;
    fill: none;
    stroke-width: 10;
}

.rec-session .score-ring .ring-fg {
    stroke: var(--danger);
    fill: none;
    stroke-width: 10;
    stroke-linecap: round;
    transition: stroke-dashoffset 0.5s cubic-bezier(0.4, 0, 0.2, 1), stroke 0.3s ease;
}

.rec-session .score-ring .ring-fg.success { stroke: var(--success); }
.rec-session .score-ring .ring-fg.warning { stroke: var(--warning); }
.rec-session .score-ring .ring-fg.danger { stroke: var(--danger); }

.rec-session .score-ring .ring-content {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.rec-session .score-ring .ring-value {
    font-size: 26px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1;
}

.rec-session .score-ring .ring-label {
    font-size: 10px;
    color: #64748b;
    font-weight: 700;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rec-session .score-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.rec-session .score-meta .threshold-display {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}

.rec-session .score-meta .threshold-display strong { color: var(--navy); font-weight: 800; }

.rec-session .passing-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 999px;
    background: var(--info-soft);
    color: var(--info);
}

.rec-session .score-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.rec-session .score-stat {
    background: #f8fafc;
    border: 1.5px solid transparent;
    border-radius: 13px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.25s ease;
}

.rec-session .score-stat.errors { color: var(--danger); }
.rec-session .score-stat.hesitations { color: #b45309; }
.rec-session .score-stat.deduction { color: #475569; }

.rec-session .score-stat.has-value { background: white; border-color: currentColor; box-shadow: var(--shadow-sm); }

.rec-session .score-stat .stat-icon-circle {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.6);
    color: inherit;
    font-size: 14px;
    flex-shrink: 0;
}

.rec-session .score-stat.has-value .stat-icon-circle { background: currentColor; color: white; }
.rec-session .score-stat.has-value .stat-icon-circle i { color: white; }

.rec-session .score-stat.errors .stat-icon-circle { background: rgba(220, 38, 38, 0.12); }
.rec-session .score-stat.hesitations .stat-icon-circle { background: rgba(245, 158, 11, 0.12); }
.rec-session .score-stat.deduction .stat-icon-circle { background: rgba(71, 85, 105, 0.12); }

.rec-session .score-stat .stat-text { display: flex; flex-direction: column; line-height: 1.1; }
.rec-session .score-stat .stat-text .lbl { font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
.rec-session .score-stat .stat-text .val { font-size: 18px; font-weight: 800; color: inherit; }

/* =========================
   NOTES
   ========================= */
.rec-session .notes-card { background: white; border-radius: var(--radius); border: 1px solid rgba(15, 28, 51, 0.06); padding: 14px 16px; box-shadow: var(--shadow-sm); }

.rec-session .notes-textarea {
    border: 1.5px solid #e2e8f0;
    border-radius: var(--radius-sm);
    padding: 10px 14px;
    font-size: 13px;
    transition: all 0.2s ease;
    width: 100%;
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
}

.rec-session .notes-textarea:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 160, 48, 0.12);
}

/* =========================
   EMPTY STATE
   ========================= */
.rec-session .state-empty {
    text-align: center;
    padding: 40px 16px;
    color: #94a3b8;
}

.rec-session .state-empty i { font-size: 42px; margin-bottom: 14px; color: #cbd5e1; }

/* =========================
   MODAL
   ========================= */
.rec-session .modal-content { border-radius: var(--radius); overflow: hidden; border: none; }

.rec-session .modal-header { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white; border-bottom: none; padding: 12px 16px; }
.rec-session .modal-header .modal-title { font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
.rec-session .modal-header.bg-danger { background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%) !important; }
.rec-session .modal-body { padding: 16px; }

.rec-session .failure-modal-body { text-align: center; padding: 24px 16px; }
.rec-session .failure-modal-body .failure-icon { font-size: 64px; color: var(--danger); margin-bottom: 16px; }
.rec-session .failure-modal-body h5 { color: var(--navy); font-weight: 800; margin-bottom: 8px; }
.rec-session .failure-modal-body p { color: #64748b; margin-bottom: 0; }

.rec-session .ayahs-modal-body { padding: 0; }
.rec-session .ayahs-content { min-height: 200px; padding: 16px; }
.rec-session .ayahs-content .amiri-text { font-family: 'Amiri', serif; font-size: 22px; line-height: 2.2; text-align: justify; color: var(--navy); }
.rec-session .ayahs-content .amiri-text .ayah-marker { color: var(--gold); font-weight: bold; margin: 0 4px; }
.rec-session .ayahs-content .surah-header { text-align: center; font-family: 'Amiri', serif; font-size: 20px; font-weight: 800; color: var(--navy); margin: 16px 0; padding: 10px; background: var(--gold-soft); border-radius: 8px; }

/* Ayahs modal: content + thin scoring rail */
.rec-session .ayahs-modal-layout {
    display: flex;
    align-items: stretch;
    height: min(72vh, 640px);
}
.rec-session .ayahs-modal-layout .ayahs-content {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
}
.rec-session .ayahs-scoring-tab {
    flex: 0 0 88px;
    width: 88px;
    background: #f8fafc;
    border-inline-start: 1px solid #eef0f3;
    padding: 12px 6px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
}
.rec-session .ayahs-tab-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 8px 4px;
    border-radius: 10px;
    background: #fff;
    border: 1px solid #eef0f3;
}
.rec-session .ayahs-tab-block.errors { color: var(--danger); }
.rec-session .ayahs-tab-block.hesitations { color: #b45309; }
.rec-session .ayahs-tab-label {
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}
.rec-session .ayahs-tab-block.errors .ayahs-tab-label { color: var(--danger); }
.rec-session .ayahs-tab-block.hesitations .ayahs-tab-label { color: #b45309; }
.rec-session .ayahs-tab-controls {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.rec-session .ayahs-tab-value {
    font-weight: 800;
    font-size: 15px;
    color: var(--navy);
    font-feature-settings: "tnum";
}
.rec-session .ayahs-tab-btn {
    width: 26px;
    height: 24px;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    transition: all 0.15s ease;
}
.rec-session .ayahs-tab-btn:active { transform: scale(0.92); }
.rec-session .ayahs-tab-block.errors .ayahs-tab-btn { background: var(--danger); }
.rec-session .ayahs-tab-block.errors .ayahs-tab-btn:hover { background: #b91c1c; }
.rec-session .ayahs-tab-block.hesitations .ayahs-tab-btn { background: var(--warning); }
.rec-session .ayahs-tab-block.hesitations .ayahs-tab-btn:hover { background: #d97706; }
.rec-session .ayahs-tab-score {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 4px;
    border-radius: 10px;
    background: var(--gold-soft);
    border: 1px solid rgba(212, 160, 48, 0.25);
    margin-top: auto;
}
.rec-session .ayahs-tab-score .ayahs-tab-value { color: #b45309; }

@media (max-width: 576px) {
    .rec-session .ayahs-modal-layout {
        flex-direction: column;
        height: min(82vh, 680px);
    }
    .rec-session .ayahs-scoring-tab {
        flex: 0 0 auto;
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-inline-start: none;
        border-top: 1px solid #eef0f3;
        padding: 8px 10px;
        overflow: visible;
    }
    .rec-session .ayahs-tab-block {
        flex: 1 1 calc(50% - 8px);
        flex-direction: row;
        justify-content: center;
        gap: 6px;
        padding: 6px 8px;
        margin: 0;
    }
    .rec-session .ayahs-tab-score {
        flex: 1 1 100%;
        flex-direction: row;
        justify-content: center;
        gap: 8px;
        padding: 6px 8px;
        margin: 0;
    }
    .rec-session .ayahs-tab-controls { flex-direction: row; }
    .rec-session .ayahs-tab-btn { width: 24px; height: 22px; }
    .rec-session .ayahs-tab-value { font-size: 13px; }
}

/* =========================
   WARNING ALERT
   ========================= */
.rec-session .selection-warning {
    background: linear-gradient(135deg, var(--warning-soft) 0%, #fffbeb 100%);
    border: 1px solid rgba(245, 158, 11, 0.3);
    color: #b45309;
    border-radius: var(--radius-sm);
    padding: 12px 16px;
    margin-top: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    font-weight: 600;
}

/* =========================
   RESPONSIVE
   ========================= */
@media (max-width: 991px) {
    .rec-session .hero-top { flex-direction: column; align-items: stretch; }
    .rec-session .hero-stats { justify-content: center; }
    .rec-session .student-block { justify-content: center; text-align: center; flex-direction: column; }
    .rec-session .student-meta { justify-content: center; }
    .rec-session .quran-verse { font-size: 12.5px; }
    .rec-session .part-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
    .rec-session .card-header-modern,
    .rec-session .part-section-header { flex-wrap: wrap; row-gap: 8px; }
    .rec-session .card-body-modern { padding: 14px; }
    .rec-session .action-bar-sticky { margin: 16px -16px -16px; padding: 12px 16px; }
    .rec-session .part-section-body { padding: 14px; }
    .rec-session .position-card { grid-template-columns: 1fr; }
    .rec-session .position-card-head { border-inline-end: none; border-bottom: 1px solid #f1f5f9; padding: 10px 12px; }
    .rec-session .position-card-body { padding: 8px 10px; gap: 6px; }
    .rec-session .position-stat { padding: 4px 4px 4px 8px; gap: 6px; }
    .rec-session .position-stat .stat-label { font-size: 9px; }
    .rec-session .scoring-input-group input { width: 30px; }
    .rec-session .position-score-box { min-width: 60px; padding: 5px 8px; }
    .rec-session .position-score-box .score-value { font-size: 13px; }
    .rec-session .score-dashboard-body { grid-template-columns: 1fr; gap: 18px; padding: 18px; }
    .rec-session .score-ring-wrapper { justify-content: center; }
    .rec-session .distribution-grid { flex-direction: column; }
    .rec-session .distribution-grid > [class*="col-"] { width: 100%; }
    .rec-session .score-stats-grid { grid-template-columns: 1fr; }
    .rec-session .score-dashboard { position: static; top: auto; }
}

@media (max-width: 480px) {
    .rec-session .part-grid { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .rec-session .part-checkbox-card { padding: 10px 8px 8px; }
    .rec-session .part-checkbox-card .part-icon-wrap { width: 32px; height: 32px; font-size: 12px; border-radius: 8px; }
    .rec-session .part-checkbox-card .part-number { font-size: 12px; }
    .rec-session .part-checkbox-card .part-number .num-en { font-size: 13px; }
    .rec-session .part-checkbox-card .part-range { font-size: 9px; }
    .rec-session .position-card-body { grid-template-columns: 1fr 1fr; }
    .rec-session .position-stat.deduction, .rec-session .position-stat.score { grid-column: span 1; }
    .rec-session .position-score-box { min-width: 64px; }
    .rec-session .hero-top { padding: 16px; }
    .rec-session .student-avatar { width: 44px; height: 44px; font-size: 18px; }
    .rec-session .student-name { font-size: 17px; }
    .rec-session .stat-pill { padding: 8px 12px; }
    .rec-session .stat-pill .stat-text .val { font-size: 14px; }
    .rec-session .btn-primary-modern, .rec-session .btn-outline-modern { padding: 8px 16px; font-size: 13px; }
}

/* Phones: compact layout, no horizontal overflow */
@media (max-width: 576px) {
    .rec-session .session-hero { margin-bottom: 12px; border-radius: 11px; }
    .rec-session .hero-top { padding: 12px; gap: 12px; }
    .rec-session .student-name { font-size: 16px; }
    .rec-session .chip { font-size: 11px; padding: 4px 9px; }
    .rec-session .stat-pill { padding: 8px 12px; }
    .rec-session .info-alert { padding: 10px 12px; font-size: 12.5px; }
    .rec-session .card-header-modern { padding: 10px 12px; }
    .rec-session .card-body-modern { padding: 12px; }
    .rec-session .part-section-body { padding: 12px; }

    /* Selection: drop forced min-widths that caused overflow */
    .rec-session .progress-card .progress-text-block { min-width: 0; }
    .rec-session .progress-card .progress-track { min-width: 0; flex-basis: 100%; }
    .rec-session .search-box { min-width: 0; width: 100%; }

    /* Position rows: two columns so the scoring controls fit */
    .rec-session .position-card-body { grid-template-columns: 1fr 1fr; gap: 6px; }
    .rec-session .position-stat.deduction,
    .rec-session .position-stat.score { grid-column: span 1; }
    .rec-session .scoring-input-group input { width: 28px; }
    .rec-session .scoring-input-group button { width: 22px; height: 24px; }

    /* Score dashboard: compact and non-sticky */
    .rec-session .score-dashboard { position: static; top: auto; }
    .rec-session .score-dashboard-body { padding: 12px; gap: 12px; }
    .rec-session .score-ring { width: 104px; height: 104px; }
    .rec-session .score-ring .ring-value { font-size: 22px; }
    .rec-session .score-ring-wrapper { flex-wrap: wrap; justify-content: center; gap: 12px; text-align: center; }
    .rec-session .score-stats-grid { grid-template-columns: repeat(3, 1fr); gap: 6px; }
    .rec-session .score-stat { flex-direction: column; text-align: center; padding: 8px; gap: 6px; }
    .rec-session .score-stat .stat-icon-circle { width: 30px; height: 30px; font-size: 12px; }
    .rec-session .score-stat .stat-text .lbl { font-size: 9px; }
    .rec-session .score-stat .stat-text .val { font-size: 15px; }

    /* Part grid: shrink cards so 3 fit per row on narrow phones */
    .rec-session .part-grid { grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); }

    /* Prevent flex/grid blowout in nested wrappers */
    .rec-session .session-hero,
    .rec-session .part-section,
    .rec-session .card-modern,
    .rec-session .part-checkbox-card { min-width: 0; }

    /* Break long Arabic strings so they don't push width */
    .rec-session .quran-verse,
    .rec-session .chip { overflow-wrap: anywhere; word-break: break-word; }

    /* Ayahs modal: full-screen sheet (overrides modal-xl) */
    .rec-session #ayahsModal .modal-dialog.modal-ayahs {
        max-width: 100vw;
        width: 100vw;
        height: 100vh;
        height: 100dvh;
        margin: 0;
        border-radius: 0;
    }
    .rec-session #ayahsModal .modal-content {
        height: 100vh;
        height: 100dvh;
        border-radius: 0;
        border: none;
    }
    .rec-session #ayahsModal .modal-header,
    .rec-session #ayahsModal .modal-footer { flex-shrink: 0; }
    .rec-session #ayahsModal .modal-body { padding: 0; overflow: hidden; flex: 1 1 auto; min-height: 0; }

    /* Ayahs layout: sticky scoring rail on top, scrolling content below */
    .rec-session .ayahs-modal-layout {
        flex-direction: column;
        height: 100%;
        min-height: 0;
    }
    .rec-session .ayahs-content {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    .rec-session .ayahs-scoring-tab {
        order: -1;
        position: sticky;
        top: 0;
        z-index: 3;
        flex: 0 0 auto;
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        padding: 6px 10px;
        background: #f8fafc;
        border-inline-start: none;
        border-bottom: 1px solid #eef0f3;
        box-shadow: 0 4px 12px rgba(15, 28, 51, 0.06);
        overflow: visible;
    }
    .rec-session .ayahs-tab-block {
        flex: 1 1 calc(50% - 6px);
        flex-direction: row;
        gap: 6px;
        padding: 4px 6px;
    }
    .rec-session .ayahs-tab-score {
        flex: 1 1 100%;
        flex-direction: row;
        gap: 6px;
        padding: 4px 6px;
        margin: 0;
    }

    /* Modal footer: allow button wrap so close isn't clipped */
    .rec-session #ayahsModal .modal-footer { flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
}

/* =========================
   ANIMATIONS
   ========================= */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.rec-session .part-section { animation: fadeInUp 0.4s ease backwards; }
.rec-session .part-section:nth-child(1) { animation-delay: 0.05s; }
.rec-session .part-section:nth-child(2) { animation-delay: 0.1s; }
.rec-session .part-section:nth-child(3) { animation-delay: 0.15s; }
.rec-session .part-section:nth-child(4) { animation-delay: 0.2s; }
.rec-session .part-section:nth-child(5) { animation-delay: 0.25s; }
.rec-session .part-section:nth-child(6) { animation-delay: 0.3s; }

@keyframes checkmarkPop {
    0% { transform: scale(0); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

.rec-session .position-number.changed { animation: checkmarkPop 0.4s ease; }
</style>
@endpush

@section('content')

<x-page-hero icon="fas fa-book-quran">
    <h1 class="page-banner-title">جلسة مفصل</h1>
</x-page-hero>
<div class="rec-session">
    {{-- ==================== HERO ==================== --}}
    <div class="session-hero">
        <div class="hero-top">
            <div class="student-block">
                <div class="student-avatar">
                    <i class="fas fa-quran"></i>
                </div>
                <div>
                    <h2 class="student-name">{{ $student->user->name }}</h2>
                    <div class="student-meta">
                        <span class="chip gold">
                            <i class="fas fa-layer-group"></i>
                            {{ $student->level->name ?? 'بدون مستوى' }}
                        </span>
                        @if($student->student_number)
                        <span class="chip">
                            <i class="fas fa-id-card"></i>
                            {{ $student->student_number }}
                        </span>
                        @endif
                        <span class="chip cyan">
                            <i class="fas fa-calendar-day"></i>
                            {{ date('Y-m-d') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="hero-stats">
                <div class="stat-pill pass">
                    <div class="stat-icon"><i class="fas fa-flag-checkered"></i></div>
                    <div class="stat-text">
                        <span class="lbl">علامة الرسوب</span>
                        <span class="val">{{ $passingPercentage }}%</span>
                    </div>
                </div>
                <div class="stat-pill score">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-text">
                        <span class="lbl">العلامة الحالية</span>
                        <span class="val" id="currentScore">100%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="quran-verse">
            <i class="fas fa-book-quran" style="margin-inline-end: 8px; color: var(--gold);"></i>
            {{ 'إِنَّ هَذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ' }}
        </div>
    </div>

    {{-- ==================== SELECTION MODE ==================== --}}
    @if($selectionMode)
    <div class="progress-card">
        <div class="progress-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="progress-text-block">
            <div class="label">
                تم اختيار <strong><span id="selectionCount">0</span> من {{ $partsCount }} أجزاء</strong>
            </div>
            <div class="progress-track">
                <div class="progress-fill" id="selectionProgressFill" style="width: 0%"></div>
            </div>
        </div>
        <div class="progress-percent" id="progressPercent">0%</div>
    </div>

    <div class="card-modern">
        <div class="card-header-modern">
            <div class="section-title">
                <span class="title-icon"><i class="fas fa-hand-pointer"></i></span>
                اختر الأجزاء المطلوب اختبارها
            </div>
            <span class="chip gold">
                <i class="fas fa-bullseye"></i>
                مطلوب {{ $partsCount }} أجزاء
            </span>
        </div>
        <div class="card-body-modern">
            <div class="info-alert mb-4">
                <i class="fas fa-info-circle"></i>
                <div class="alert-content">
                    اختر <strong>{{ $partsCount }} أجزاء</strong> من المصحف، ثم حدد توزيع صعوبة المواضع لكل جزء
                    (<strong>3 مواضع</strong> لكل جزء، مجموع السهل + المتوسط + الصعب = 3).
                </div>
            </div>

            <form id="partSelectionForm" method="POST" action="{{ route('supervisor.recitation.mafsil.post') }}">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="mafsil_type" value="{{ $mafsilType }}">

                <div class="selection-toolbar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="partSearch" placeholder="ابحث عن جزء (مثلاً: 1، 15، البقرة...)">
                    </div>
                    <div class="quick-presets">
                        <button type="button" class="preset-btn" data-preset="first">
                            <i class="fas fa-arrow-down-1-9"></i> الأولى
                        </button>
                        <button type="button" class="preset-btn" data-preset="last">
                            <i class="fas fa-arrow-up-9-1"></i> الأخيرة
                        </button>
                        <button type="button" class="preset-btn" data-preset="random">
                            <i class="fas fa-shuffle"></i> عشوائي
                        </button>
                        <button type="button" class="preset-btn" data-preset="clear">
                            <i class="fas fa-eraser"></i> مسح
                        </button>
                    </div>
                </div>

                <div class="part-grid mb-2" id="partGrid">
                    @foreach($allParts as $part)
                    <label class="part-checkbox-card" for="part-{{ $part->part_number }}" data-part="{{ $part->part_number }}">
                        <input class="form-check-input part-checkbox" type="checkbox"
                               name="selected_parts[]"
                               value="{{ $part->part_number }}"
                               data-part-number="{{ $part->part_number }}"
                               data-part-label="الجزء {{ $part->part_number }}"
                               id="part-{{ $part->part_number }}">
                        <span class="check-circle"><i class="fas fa-check"></i></span>
                        <span class="part-icon-wrap">
                            <i class="fas fa-book-quran"></i>
                        </span>
                        <div class="part-info">
                            <div class="part-number">الجزء <span class="num-en">{{ $part->part_number }}</span></div>
                            <div class="part-range">{{ $part->start_page }} - {{ $part->end_page }}</div>
                        </div>
                        <div class="card-footer">
                            <span class="part-pages-count">
                                <i class="fas fa-layer-group"></i>
                                {{ $part->pages_count ?? ($part->end_page - $part->start_page + 1) }} ص
                            </span>
                            <span class="order-badge"></span>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="selection-warning d-none" id="selectionWarning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="selectionWarningText"></span>
                </div>

                <div id="difficultyDistributionContainer" class="d-none mt-4">
                    <div class="distribution-container">
                        <div class="distribution-header">
                            <div class="header-icon"><i class="fas fa-sliders-h"></i></div>
                            <div class="header-text">
                                <h6>توزيع المواضيع (3 مواضع لكل جزء)</h6>
                                <p>حدد عدد المواضيع السهلة والمتوسطة والصعبة لكل جزء</p>
                            </div>
                        </div>
                        <div id="difficultyDistributionRows" class="distribution-grid"></div>
                    </div>
                </div>

                <div class="action-bar-sticky">
                    <a href="{{ route('supervisor.recitation.index') }}" class="btn-outline-modern">
                        <i class="fas fa-arrow-right"></i> رجوع
                    </a>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small d-none d-md-inline">
                            <span id="stickySelectionCount">0</span> / {{ $partsCount }} جزء
                        </span>
                        <button type="submit" class="btn-primary-modern" id="confirmPartsBtn" disabled>
                            <i class="fas fa-check-circle"></i>
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
        const progressPercent = document.getElementById('progressPercent');
        const btn = document.getElementById('confirmPartsBtn');
        const distributionContainer = document.getElementById('difficultyDistributionContainer');
        const distributionRows = document.getElementById('difficultyDistributionRows');
        const searchInput = document.getElementById('partSearch');

        function updateDistributionInputs() {
            const checked = Array.from(document.querySelectorAll('.part-checkbox:checked'));
            const checkedCount = checked.length;

            countEl.textContent = checkedCount;
            stickyCountEl.textContent = checkedCount;
            const pct = Math.min(100, (checkedCount / requiredParts) * 100);
            progressFill.style.width = pct + '%';
            progressPercent.textContent = Math.round(pct) + '%';
            distributionRows.innerHTML = '';

            let order = 1;
            document.querySelectorAll('.part-checkbox-card').forEach(card => {
                const cb = card.querySelector('.part-checkbox');
                const isSelected = cb.checked;
                card.classList.toggle('selected', isSelected);
                if (isSelected) {
                    card.querySelector('.order-badge').textContent = order++;
                }
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
                                <span class="part-num-badge">${partNumber}</span>
                                ${partLabel}
                            </div>
                            <div class="difficulty-counters">
                                <div class="difficulty-counter easy">
                                    <label class="counter-label">سهل</label>
                                    <div class="counter-box">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 1, -1)">−</button>
                                        <input type="number" name="difficulty[${partNumber}][1]" value="1" min="0" max="3"
                                               class="difficulty-input" data-part="${partNumber}" data-difficulty="1"
                                               oninput="validateDistribution()">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 1, 1)">+</button>
                                    </div>
                                </div>
                                <div class="difficulty-counter medium">
                                    <label class="counter-label">متوسط</label>
                                    <div class="counter-box">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 2, -1)">−</button>
                                        <input type="number" name="difficulty[${partNumber}][2]" value="1" min="0" max="3"
                                               class="difficulty-input" data-part="${partNumber}" data-difficulty="2"
                                               oninput="validateDistribution()">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 2, 1)">+</button>
                                    </div>
                                </div>
                                <div class="difficulty-counter hard">
                                    <label class="counter-label">صعب</label>
                                    <div class="counter-box">
                                        <button type="button" class="counter-btn" onclick="adjustDifficulty('${partNumber}', 3, -1)">−</button>
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
                distributionContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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

        // Search functionality
        searchInput.addEventListener('input', function() {
            const q = this.value.trim();
            document.querySelectorAll('.part-checkbox-card').forEach(card => {
                const num = card.dataset.part;
                const matches = !q || num.includes(q) || ('الجزء ' + num).includes(q);
                card.classList.toggle('hidden', !matches);
            });
        });

        // Quick presets
        document.querySelectorAll('.preset-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const preset = this.dataset.preset;
                const all = Array.from(document.querySelectorAll('.part-checkbox'));

                all.forEach(cb => cb.checked = false);

                if (preset === 'first') {
                    all.slice(0, requiredParts).forEach(cb => cb.checked = true);
                } else if (preset === 'last') {
                    all.slice(-requiredParts).forEach(cb => cb.checked = true);
                } else if (preset === 'random') {
                    const shuffled = all.sort(() => Math.random() - 0.5);
                    shuffled.slice(0, requiredParts).forEach(cb => cb.checked = true);
                }
                // 'clear' leaves them all unchecked

                updateDistributionInputs();
            });
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
            const cardEl = document.querySelector(`.distribution-card[data-part="${partNumber}"]`);
            if (statusEl) {
                if (total === 3) {
                    statusEl.className = 'distribution-status valid';
                    statusEl.innerHTML = '<i class="fas fa-check-circle"></i> المجموع: <span class="part-total" data-part="' + partNumber + '">3</span> / 3';
                } else {
                    statusEl.className = 'distribution-status invalid';
                    statusEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> المجموع: <span class="part-total" data-part="' + partNumber + '">' + total + '</span> / 3';
                }
            }

            if (cardEl) {
                cardEl.classList.toggle('complete', total === 3);
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
    {{-- ==================== SCORING MODE ==================== --}}

    <div class="info-alert mb-4">
        <i class="fas fa-info-circle"></i>
        <div class="alert-content">
            <strong>نظام الخصم:</strong>
            كل خطأ = <span class="badge bg-danger">-3 علامات</span>
            &nbsp;|&nbsp;
            كل تردد = <span class="badge bg-warning text-dark">-0.33 علامة</span> (يُحسب كل 3 ترددات كخصم كامل)
            &nbsp;|&nbsp;
            العلامة النهائية لا تقل عن <span class="badge bg-secondary">{{ $passingPercentage }}%</span>
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
                    <span class="title-icon"><i class="fas fa-book-open"></i></span>
                    الجزء {{ $part->part_number }}
                </div>
                <div class="part-meta">
                    <span class="meta-chip">
                        <i class="fas fa-file-alt"></i>
                        صفحة {{ $part->start_page }} - {{ $part->end_page }}
                    </span>
                    <span class="meta-chip">
                        <i class="fas fa-list-ol"></i>
                        <strong>{{ $juzPositions->count() }}</strong> مواضع
                    </span>
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
                        <div class="position-info">
                            <div class="position-range">
                                @if($isPlaceholder)
                                    <em class="text-muted">الموضع {{ $posIndex + 1 }}</em>
                                @else
                                    {{ $mafsilPosition->positionRangeLabel() }}
                                @endif
                            </div>
                            @if(!$isPlaceholder)
                            <div class="position-sub">
                                <span class="sub-item"><i class="fas fa-file-alt"></i> ص {{ $mafsilPosition->pageNumberLabel() }}</span>
                                @php
                                    $difficultyClass = match($mafsilPosition->difficulty) {
                                        1 => 'easy',
                                        3 => 'hard',
                                        default => 'medium',
                                    };
                                    $difficultyIcon = match($mafsilPosition->difficulty) {
                                        1 => 'fa-feather-alt',
                                        3 => 'fa-fire',
                                        default => 'fa-balance-scale',
                                    };
                                @endphp
                                <span class="difficulty-badge {{ $difficultyClass }}">
                                    <i class="fas {{ $difficultyIcon }}"></i>
                                    {{ $mafsilPosition->difficultyLabel() }}
                                </span>
                            </div>
                            @endif
                        </div>
                        @if(!$isPlaceholder)
                        <button type="button" class="btn-view-ayahs"
                                onclick="showPositionAyahs({{ $mafsilPosition->id }}, {{ $partIndex }}, {{ $posIndex }})" title="عرض الآيات بالرسم العثماني">
                            <i class="fas fa-eye"></i>
                        </button>
                        @endif
                    </div>
                    <div class="position-card-body">
                        <div class="position-stat errors" id="stat-errors-{{ $partIndex }}-{{ $posIndex }}">
                            <span class="stat-label"><i class="fas fa-times-circle"></i> أخطاء</span>
                            <div class="scoring-input-group errors">
                                <button type="button" class="btn-errors" onclick="adjustMafsil('errors-{{ $partIndex }}-{{ $posIndex }}', -1, {{ $partIndex }}, {{ $posIndex }})" aria-label="إنقاص">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="pages[{{ $globalIndex }}][errors]" id="errors-{{ $partIndex }}-{{ $posIndex }}"
                                       value="0" min="0" max="50"
                                       oninput="calcMafsilRow({{ $partIndex }}, {{ $posIndex }})">
                                <button type="button" class="btn-errors" onclick="adjustMafsil('errors-{{ $partIndex }}-{{ $posIndex }}', 1, {{ $partIndex }}, {{ $posIndex }})" aria-label="زيادة">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="position-stat hesitations" id="stat-hes-{{ $partIndex }}-{{ $posIndex }}">
                            <span class="stat-label"><i class="fas fa-question-circle"></i> ترددات</span>
                            <div class="scoring-input-group hesitations">
                                <button type="button" class="btn-hesitations" onclick="adjustMafsil('hesitations-{{ $partIndex }}-{{ $posIndex }}', -1, {{ $partIndex }}, {{ $posIndex }})" aria-label="إنقاص">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="pages[{{ $globalIndex }}][hesitations]" id="hesitations-{{ $partIndex }}-{{ $posIndex }}"
                                       value="0" min="0" max="100"
                                       oninput="calcMafsilRow({{ $partIndex }}, {{ $posIndex }})">
                                <button type="button" class="btn-hesitations" onclick="adjustMafsil('hesitations-{{ $partIndex }}-{{ $posIndex }}', 1, {{ $partIndex }}, {{ $posIndex }})" aria-label="زيادة">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="position-stat deduction">
                            <span class="position-deduction" id="deduction-{{ $partIndex }}-{{ $posIndex }}">-0.00</span>
                            <span class="stat-label">الخصم</span>
                        </div>
                        <div class="position-stat score">
                            <div class="position-score-box" id="position-score-box-{{ $partIndex }}-{{ $posIndex }}">
                                <div class="score-value" id="part-score-{{ $partIndex }}-{{ $posIndex }}">100.0%</div>
                                <div class="score-label">العلامة</div>
                            </div>
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

        {{-- ==================== STICKY SCORE DASHBOARD ==================== --}}
        <div class="score-dashboard">
            <div class="score-dashboard-header">
                <div class="summary-title">
                    <span class="title-icon"><i class="fas fa-calculator"></i></span>
                    ملخص النتيجة
                </div>
                <div class="auto-update-chip">
                    <span class="pulse-dot"></span>
                    يتم التحديث تلقائياً
                </div>
            </div>
            <div class="score-dashboard-body">
                <div class="score-ring-wrapper">
                    <div class="score-ring">
                        <svg viewBox="0 0 120 120">
                            <circle class="ring-bg" cx="60" cy="60" r="52"></circle>
                            <circle class="ring-fg" id="scoreRing" cx="60" cy="60" r="52"
                                    stroke-dasharray="326.7" stroke-dashoffset="0"></circle>
                        </svg>
                        <div class="ring-content">
                            <div class="ring-value" id="final-score-large">100%</div>
                            <div class="ring-label">المعدل العام</div>
                        </div>
                    </div>
                    <div class="score-meta">
                        <div class="threshold-display">
                            <i class="fas fa-flag-checkered"></i>
                            علامة الرسوب: <strong>{{ $passingPercentage }}%</strong>
                        </div>
                        <span class="passing-pill" id="statusPill">
                            <i class="fas fa-spinner"></i> جاهز للتسجيل
                        </span>
                    </div>
                </div>
                <div class="score-stats-grid">
                    <div class="score-stat errors" id="sumStat-errors">
                        <div class="stat-icon-circle"><i class="fas fa-times-circle"></i></div>
                        <div class="stat-text">
                            <span class="lbl">إجمالي الأخطاء</span>
                            <span class="val" id="total-errors">0</span>
                        </div>
                    </div>
                    <div class="score-stat hesitations" id="sumStat-hesitations">
                        <div class="stat-icon-circle"><i class="fas fa-question-circle"></i></div>
                        <div class="stat-text">
                            <span class="lbl">إجمالي الترددات</span>
                            <span class="val" id="total-hesitations">0</span>
                        </div>
                    </div>
                    <div class="score-stat deduction" id="sumStat-deduction">
                        <div class="stat-icon-circle"><i class="fas fa-minus-circle"></i></div>
                        <div class="stat-text">
                            <span class="lbl">إجمالي الخصم</span>
                            <span class="val" id="total-deduction">-0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="notes-card mt-4">
            <label class="form-label fw-bold mb-2 d-flex align-items-center gap-2" style="color: var(--navy); font-size: 14px;">
                <span class="title-icon" style="width: 30px; height: 30px; font-size: 12px;"><i class="fas fa-sticky-note"></i></span>
                ملاحظات (اختياري)
            </label>
            <textarea name="notes" class="notes-textarea" rows="3" placeholder="أي ملاحظات حول جلسة المفصل..."></textarea>
        </div>

        <div class="d-flex flex-wrap gap-3 justify-content-between mt-4 mb-5">
            <a href="{{ route('supervisor.recitation.index') }}" class="btn-outline-modern">
                <i class="fas fa-arrow-right"></i> رجوع
            </a>
            <div class="d-flex gap-3 flex-wrap">
                <button type="button" class="btn-outline-modern" style="border-color: var(--warning); color: var(--warning);" onclick="submitMafsilIncomplete()">
                    <i class="fas fa-times-circle"></i> لم يكمل
                </button>
                <button type="button" class="btn-primary-modern" onclick="submitMafsil()">
                    <i class="fas fa-save"></i> حفظ نتيجة المفصل
                </button>
            </div>
        </div>
    </form>

    {{-- ==================== FAILURE MODAL ==================== --}}
    <div class="modal fade" id="failureModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> تحذير - الطالب راسب</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body failure-modal-body">
                    <div class="failure-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <h5>علامة الطالب أقل من {{ $passingPercentage }}%</h5>
                    <p>هل تريد إيقاف التسميع أم إكمال الجلسة؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="stopForFailure()">
                        <i class="fas fa-stop me-2"></i> إيقاف
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="continueAfterFailure()">
                        <i class="fas fa-play me-2"></i> إكمال
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== AYAHS MODAL ==================== --}}
    <div class="modal fade" id="ayahsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-ayahs">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye"></i>
                        عرض الآيات بالرسم العثماني
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body ayahs-modal-body p-0">
                    <div class="ayahs-modal-layout">
                        <div id="ayahsModalContent" class="ayahs-content">
                            <div class="d-flex justify-content-center align-items-center h-100 py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">جاري التحميل...</span>
                                </div>
                            </div>
                        </div>
                        <aside class="ayahs-scoring-tab">
                            <div class="ayahs-tab-block errors">
                                <span class="ayahs-tab-label"><i class="fas fa-times-circle"></i> أخطاء</span>
                                <div class="ayahs-tab-controls">
                                    <button type="button" class="ayahs-tab-btn" onclick="ayahsAdjustStat('errors', 1)" aria-label="زيادة الأخطاء"><i class="fas fa-plus"></i></button>
                                    <span class="ayahs-tab-value" id="ayahsTabErrors">0</span>
                                    <button type="button" class="ayahs-tab-btn" onclick="ayahsAdjustStat('errors', -1)" aria-label="إنقاص الأخطاء"><i class="fas fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="ayahs-tab-block hesitations">
                                <span class="ayahs-tab-label"><i class="fas fa-question-circle"></i> ترددات</span>
                                <div class="ayahs-tab-controls">
                                    <button type="button" class="ayahs-tab-btn" onclick="ayahsAdjustStat('hesitations', 1)" aria-label="زيادة الترددات"><i class="fas fa-plus"></i></button>
                                    <span class="ayahs-tab-value" id="ayahsTabHes">0</span>
                                    <button type="button" class="ayahs-tab-btn" onclick="ayahsAdjustStat('hesitations', -1)" aria-label="إنقاص الترددات"><i class="fas fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="ayahs-tab-score">
                                <span class="ayahs-tab-label">العلامة</span>
                                <span class="ayahs-tab-value" id="ayahsTabScore">100.0%</span>
                            </div>
                        </aside>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tajweedModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--gold) 0%, #b8941f 100%); color: white; border: none;">
                    <h5 class="modal-title">
                        <i class="fas fa-book-quran me-2"></i>
                        أخطاء التجويد - <span id="tajweedModalPositionLabel"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="mb-3">
                        <label for="tajweedRuleSelect" class="form-label fw-bold">القاعدة</label>
                        <select id="tajweedRuleSelect" class="form-select">
                            <option value="">— اختر قاعدة —</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="tajweedSeverityInput" class="form-label fw-bold">الشدة (1-5)</label>
                            <input type="number" id="tajweedSeverityInput" class="form-control" value="1" min="1" max="5">
                        </div>
                        <div class="col-6">
                            <label for="tajweedOccurrencesInput" class="form-label fw-bold">عدد التكرارات</label>
                            <input type="number" id="tajweedOccurrencesInput" class="form-control" value="1" min="1" max="50">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="tajweedNoteInput" class="form-label fw-bold">ملاحظة (اختياري)</label>
                        <textarea id="tajweedNoteInput" class="form-control" rows="2" maxlength="500" placeholder="مثال: لم يُظهر الغنة في النون الساكنة"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-warning text-white" id="tajweedAddBtn" onclick="addTajweedError()">
                        <i class="fas fa-plus me-1"></i>إضافة الخطأ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    const TAJWEED_RULES = @json($tajweedRules ?? []);
    </script>

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

const RING_CIRCUMFERENCE = 2 * Math.PI * 52; // ~326.7

let ayahsContext = { partIndex: null, posIndex: null };

function showPositionAyahs(mafsilPositionId, partIndex, posIndex) {
    ayahsContext = { partIndex: partIndex, posIndex: posIndex };
    ayahsSyncTab();

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

function ayahsAdjustStat(kind, delta) {
    const { partIndex, posIndex } = ayahsContext;
    if (partIndex === null || posIndex === null) return;

    const id = (kind === 'errors' ? 'errors-' : 'hesitations-') + partIndex + '-' + posIndex;
    if (!document.getElementById(id)) return;

    adjustMafsil(id, delta, partIndex, posIndex);
    ayahsSyncTab();
}

function ayahsSyncTab() {
    const { partIndex, posIndex } = ayahsContext;
    if (partIndex === null || posIndex === null) return;

    const errorsInput = document.getElementById('errors-' + partIndex + '-' + posIndex);
    const hesInput = document.getElementById('hesitations-' + partIndex + '-' + posIndex);
    const scoreEl = document.getElementById('part-score-' + partIndex + '-' + posIndex);

    const errorsEl = document.getElementById('ayahsTabErrors');
    const hesEl = document.getElementById('ayahsTabHes');
    const scoreTabEl = document.getElementById('ayahsTabScore');

    if (errorsEl) errorsEl.textContent = errorsInput ? (parseInt(errorsInput.value) || 0) : 0;
    if (hesEl) hesEl.textContent = hesInput ? (parseInt(hesInput.value) || 0) : 0;
    if (scoreTabEl) scoreTabEl.textContent = scoreEl ? scoreEl.textContent : '—';
}

const positionsPerPartByIndex = {
@foreach($parts as $partIndex => $part)
    {{ $partIndex }}: {{ ($mafsilPositions[$part->part_number] ?? collect())->count() }},
@endforeach
};

function getPositionsForPart(partIndex) {
    return positionsPerPartByIndex[partIndex] || 0;
}

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

    const deductionEl = document.getElementById('deduction-' + partIndex + '-' + posIndex);
    deductionEl.textContent = '-' + totalDeduction.toFixed(2);
    deductionEl.classList.toggle('has-value', totalDeduction > 0);

    const scoreEl = document.getElementById('part-score-' + partIndex + '-' + posIndex);
    scoreEl.textContent = rowScore.toFixed(1) + '%';

    const scoreBox = document.getElementById('position-score-box-' + partIndex + '-' + posIndex);
    if (scoreBox) {
        scoreBox.className = 'position-score-box' + (rowScore >= 80 ? ' success' : rowScore >= 60 ? ' warning' : '');
    }

    // Mark stat boxes as having values
    const statErrors = document.getElementById('stat-errors-' + partIndex + '-' + posIndex);
    const statHes = document.getElementById('stat-hes-' + partIndex + '-' + posIndex);
    if (statErrors) statErrors.classList.toggle('has-value', errors > 0);
    if (statHes) statHes.classList.toggle('has-value', hesitations > 0);

    // Add visual feedback animation
    scoreEl.style.transform = 'scale(1.1)';
    setTimeout(() => { scoreEl.style.transform = 'scale(1)'; }, 200);
    scoreEl.style.transition = 'transform 0.2s ease';

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

    // Mark summary stats
    const sumErrors = document.getElementById('sumStat-errors');
    const sumHes = document.getElementById('sumStat-hesitations');
    const sumDed = document.getElementById('sumStat-deduction');
    if (sumErrors) sumErrors.classList.toggle('has-value', totalErrors > 0);
    if (sumHes) sumHes.classList.toggle('has-value', totalHesitations > 0);
    if (sumDed) sumDed.classList.toggle('has-value', totalDeduction > 0);

    // Update ring
    const ring = document.getElementById('scoreRing');
    const ringLarge = document.getElementById('final-score-large');
    const statusPill = document.getElementById('statusPill');
    const currentScoreEl = document.getElementById('currentScore');

    if (ring) {
        const pct = Math.max(0, Math.min(100, finalScore));
        const offset = RING_CIRCUMFERENCE - (pct / 100) * RING_CIRCUMFERENCE;
        ring.style.strokeDashoffset = offset;
    }

    if (!hasInputBeenMade) {
        if (ringLarge) ringLarge.textContent = '100%';
        if (statusPill) {
            statusPill.className = 'passing-pill';
            statusPill.style.background = '#f1f5f9';
            statusPill.style.color = '#64748b';
            statusPill.innerHTML = '<i class="fas fa-hand-pointer"></i> ابدأ بإدخال العلامات';
        }
        if (currentScoreEl) currentScoreEl.textContent = '100%';
        if (ring) ring.className = 'ring-fg';
        return;
    }

    if (ringLarge) ringLarge.textContent = finalScore.toFixed(1) + '%';
    if (currentScoreEl) currentScoreEl.textContent = finalScore.toFixed(1) + '%';

    if (finalScore >= passingPercentage) {
        if (ring) ring.className = 'ring-fg success';
        if (statusPill) {
            statusPill.className = 'passing-pill';
            statusPill.style.background = 'var(--success-soft)';
            statusPill.style.color = 'var(--success)';
            statusPill.innerHTML = '<i class="fas fa-check-circle"></i> ناجح';
        }
    } else {
        if (ring) ring.className = 'ring-fg danger';
        if (statusPill) {
            statusPill.className = 'passing-pill';
            statusPill.style.background = 'var(--danger-soft)';
            statusPill.style.color = 'var(--danger)';
            statusPill.innerHTML = '<i class="fas fa-exclamation-triangle"></i> راسب';
        }
    }

    if (finalScore < passingPercentage && !failureShown && !failedButContinuing && hasInputBeenMade) {
        failureShown = true;
        new bootstrap.Modal(document.getElementById('failureModal')).show();
    }
}

// Initialize the dashboard on load
document.addEventListener('DOMContentLoaded', function() {
    const ring = document.getElementById('scoreRing');
    if (ring) {
        ring.style.strokeDasharray = RING_CIRCUMFERENCE;
        ring.style.strokeDashoffset = '0';
    }
    updateTotals();
});

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

    const submitBtn = document.querySelector('[onclick="submitMafsil()"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
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
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> حفظ نتيجة المفصل';
            }
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

// ====== Tajweed attach UI ======
let tajweedContext = { globalIndex: null, partIndex: null, posIndex: null, positionLabel: '' };

function openTajweedModal(globalIndex, partIndex, posIndex, positionLabel) {
    tajweedContext = { globalIndex, partIndex, posIndex, positionLabel };
    document.getElementById('tajweedModalPositionLabel').textContent = positionLabel;

    const select = document.getElementById('tajweedRuleSelect');
    select.innerHTML = '<option value="">— اختر قاعدة —</option>';
    if (Array.isArray(window.TAJWEED_RULES)) {
        const grouped = {};
        window.TAJWEED_RULES.forEach(r => {
            if (!grouped[r.category]) grouped[r.category] = [];
            grouped[r.category].push(r);
        });
        Object.entries(grouped).forEach(([cat, list]) => {
            const og = document.createElement('optgroup');
            og.label = cat;
            list.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = r.name;
                opt.dataset.category = cat;
                og.appendChild(opt);
            });
            select.appendChild(og);
        });
    }

    document.getElementById('tajweedSeverityInput').value = 1;
    document.getElementById('tajweedOccurrencesInput').value = 1;
    document.getElementById('tajweedNoteInput').value = '';

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('tajweedModal'));
    modal.show();
}

function addTajweedError() {
    const ctx = tajweedContext;
    const select = document.getElementById('tajweedRuleSelect');
    const ruleId = select.value;
    if (!ruleId) { toastr.warning('يرجى اختيار قاعدة'); return; }

    const ruleName = select.options[select.selectedIndex].textContent;
    const ruleCategory = select.options[select.selectedIndex].dataset.category || '';
    const severity = Math.max(1, Math.min(5, parseInt(document.getElementById('tajweedSeverityInput').value, 10) || 1));
    const occurrences = Math.max(1, parseInt(document.getElementById('tajweedOccurrencesInput').value, 10) || 1);
    const note = document.getElementById('tajweedNoteInput').value.trim();

    const container = document.getElementById('tajweed-errors-' + ctx.globalIndex);
    const idx = container.children.length;

    const row = document.createElement('div');
    row.innerHTML = `
        <input type="hidden" name="pages[${ctx.globalIndex}][tajweed_errors][${idx}][tajweed_rule_id]" value="${ruleId}">
        <input type="hidden" name="pages[${ctx.globalIndex}][tajweed_errors][${idx}][category]" value="${ruleCategory}">
        <input type="hidden" name="pages[${ctx.globalIndex}][tajweed_errors][${idx}][severity]" value="${severity}">
        <input type="hidden" name="pages[${ctx.globalIndex}][tajweed_errors][${idx}][occurrences]" value="${occurrences}">
        <input type="hidden" name="pages[${ctx.globalIndex}][tajweed_errors][${idx}][note]" value="${note.replace(/"/g, '&quot;')}">
    `;
    container.appendChild(row);

    const summary = document.getElementById('tajweed-summary-' + ctx.partIndex + '-' + ctx.posIndex);
    const chip = document.createElement('span');
    chip.className = 'tajweed-chip';
    chip.innerHTML = `<i class="fas fa-book-quran"></i> ${ruleName} ${occurrences > 1 ? `×${occurrences}` : ''} <span class="remove-chip" onclick="removeTajweedError(this, ${ctx.partIndex}, ${ctx.posIndex})" title="حذف">&times;</span>`;
    summary.appendChild(chip);

    updateTajweedCountBadge(ctx.partIndex, ctx.posIndex);

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('tajweedModal'));
    modal.hide();
    toastr.success('تم إضافة خطأ التجويد');
}

function removeTajweedError(el, partIndex, posIndex) {
    const chip = el.closest('.tajweed-chip');
    if (!chip) return;
    const summary = chip.parentElement;
    const index = Array.from(summary.children).indexOf(chip);

    const container = chip.closest('.position-card-tajweed').querySelector('.tajweed-errors-container');
    const rows = container.querySelectorAll(':scope > div');
    if (rows[index]) {
        rows[index].remove();
        const globalIndex = container.id.replace('tajweed-errors-', '');
        const remaining = container.querySelectorAll(':scope > div');
        remaining.forEach((row, newIdx) => {
            row.innerHTML = row.innerHTML.replace(/tajweed_errors\]\[\d+\]/g, 'tajweed_errors][' + newIdx + ']');
        });
    }
    chip.remove();
    updateTajweedCountBadge(partIndex, posIndex);
}

function updateTajweedCountBadge(partIndex, posIndex) {
    const summary = document.getElementById('tajweed-summary-' + partIndex + '-' + posIndex);
    const badge = document.getElementById('tajweed-count-' + partIndex + '-' + posIndex);
    if (!summary || !badge) return;
    const count = summary.querySelectorAll('.tajweed-chip').length;
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}
</script>
@endpush
