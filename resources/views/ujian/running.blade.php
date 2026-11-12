@extends('layouts.app')

@section('examMode', '1')

@section('content')
@push('styles')
<style>
    /* Fullscreen exam mode layout */
    body { 
        overflow: hidden !important; 
        background-color: var(--exam-bg-main, #0B2545) !important;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }

    :root {
        --cat-navy: #0B2545;
        --cat-navy-mid: #133E87;
        --cat-navy-light: #1D4ED8;
        --cat-gold: #C59B27;
        --cat-gold-hover: #b0881e;
        --cat-amber: #D97706;
        --cat-emerald: #10B981;
        --cat-rose: #EF4444;
        
        --exam-bg-main: #0B2545;
        --exam-surface: #FFFFFF;
        --exam-surface-soft: #F8FAFC;
        --exam-surface-elevated: #FFFFFF;
        --exam-border: rgba(11, 37, 69, 0.12);
        --exam-border-gold: rgba(197, 155, 39, 0.35);
        --exam-text-primary: #0F172A;
        --exam-text-secondary: #475569;
        --exam-sidebar-w: 330px;
        --exam-shadow: 0 10px 30px -5px rgba(11, 37, 69, 0.08);
        --exam-shadow-lg: 0 20px 40px -10px rgba(11, 37, 69, 0.15);
    }

    [data-bs-theme="dark"] {
        --exam-bg-main: #081220;
        --exam-surface: #0F172A;
        --exam-surface-soft: #1E293B;
        --exam-surface-elevated: #1E293B;
        --exam-border: rgba(255, 255, 255, 0.12);
        --exam-border-gold: rgba(197, 155, 39, 0.45);
        --exam-text-primary: #F8FAFC;
        --exam-text-secondary: #CBD5E1;
        --exam-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
        --exam-shadow-lg: 0 20px 40px -10px rgba(0, 0, 0, 0.6);
    }

    .cat-wrapper {
        display: flex;
        flex-direction: column;
        height: 100vh;
        width: 100vw;
        position: fixed;
        top: 0;
        left: 0;
        overflow: hidden;
        background: var(--exam-bg-main);
        z-index: 9999;
    }

    /* Global Text Contrast Enforcer (Anti-Unreadable Text) */
    .cat-wrapper .text-secondary {
        color: var(--exam-text-secondary) !important;
    }
    .cat-wrapper .text-muted {
        color: var(--exam-text-secondary) !important;
    }
    .question-content-text,
    .question-content-text * {
        color: var(--exam-text-primary) !important;
    }
    .option-text-body,
    .option-text-body * {
        color: var(--exam-text-primary) !important;
    }

    /* TOP COMMAND HEADER - ROBUST ANTI-COLLISION ARCHITECTURE */
    .cat-header {
        height: 72px;
        background: #0B2545;
        border-bottom: 1.5px solid rgba(197, 155, 39, 0.35);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        z-index: 1100;
        flex-shrink: 0;
        color: #FFFFFF;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
        gap: 12px;
    }

    .cat-header-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .cat-brand-logo-shell {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(197, 155, 39, 0.40);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 4px;
        transition: transform 0.2s ease;
    }

    .cat-brand-logo-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }

    .cat-brand-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .cat-brand-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .cat-brand-title {
        font-weight: 800;
        font-size: 0.98rem;
        letter-spacing: -0.01em;
        color: #FFFFFF;
        line-height: 1.2;
    }

    .cat-brand-pkt-badge {
        padding: 2px 8px;
        border-radius: 6px;
        background: rgba(197, 155, 39, 0.22);
        border: 1px solid rgba(197, 155, 39, 0.50);
        color: #FBBF24;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .cat-brand-sub {
        font-size: 0.74rem;
        color: #CBD5E1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 320px;
        margin-top: 2px;
        font-weight: 500;
    }

    /* Center Participant HUD (Desktop Only >= 992px) */
    .cat-header-hud {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 0 16px;
        flex-shrink: 0;
    }

    .cat-hud-name {
        font-weight: 800;
        font-size: 0.95rem;
        color: #FFFFFF;
        max-width: 260px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        letter-spacing: 0.02em;
    }

    .cat-hud-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.72rem;
        color: #CBD5E1;
        margin-top: 2px;
        font-family: 'JetBrains Mono', monospace;
    }

    /* Right Action Buttons & Timer */
    .cat-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-cat-nav {
        height: 38px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.08);
        color: #F8FAFC;
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 12px;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-cat-nav:hover {
        background: rgba(255, 255, 255, 0.16);
        color: #FFFFFF;
        border-color: var(--cat-gold);
    }
    .btn-cat-nav-theme {
        width: 38px;
        padding: 0;
    }

    /* Timer Pill */
    .cat-timer-pill {
        height: 40px;
        padding: 0 14px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.08);
        border: 1.5px solid rgba(197, 155, 39, 0.45);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #FBBF24;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .cat-timer-pill i {
        font-size: 0.95rem;
    }

    .cat-timer-pill .cat-timer-number {
        font-family: 'JetBrains Mono', 'Plus Jakarta Sans', monospace;
        font-variant-numeric: tabular-nums;
        font-size: 1.25rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: 0.02em;
    }

    .cat-timer-pill.warning {
        border-color: #F59E0B;
        background: rgba(245, 158, 11, 0.18);
        color: #FBBF24;
    }

    .cat-timer-pill.critical {
        border-color: #EF4444;
        background: rgba(239, 68, 68, 0.22);
        color: #FCA5A5;
        animation: timerBreathe 2s infinite ease-in-out;
    }

    @keyframes timerBreathe {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0.0); }
    }

    /* Main Container & Sidebar */
    .cat-body {
        display: flex;
        flex: 1;
        overflow: hidden;
        position: relative;
        background: var(--exam-surface-soft);
    }

    [data-bs-theme="dark"] .cat-body {
        background: #081220;
    }

    .cat-sidebar {
        width: var(--exam-sidebar-w);
        background: var(--exam-surface);
        border-right: 1px solid var(--exam-border);
        display: flex;
        flex-direction: column;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1050;
        box-shadow: var(--exam-shadow);
    }

    .sidebar-collapsed {
        margin-left: calc(-1 * var(--exam-sidebar-w));
    }

    /* Desktop Sidebar Toggle */
    .btn-desktop-toggle {
        position: absolute;
        left: 0;
        top: 24px;
        z-index: 1020;
        background: var(--cat-navy);
        color: #FFFFFF;
        border: 1px solid var(--cat-gold);
        border-left: none;
        padding: 12px 14px;
        border-radius: 0 12px 12px 0;
        cursor: pointer;
        box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.25);
        transition: all 0.25s ease;
    }
    .btn-desktop-toggle:hover {
        background: var(--cat-navy-mid);
        padding-left: 18px;
    }

    /* Palette Filter Bar */
    .palette-filter-bar {
        padding: 14px 16px;
        border-bottom: 1px solid var(--exam-border);
        background: var(--exam-surface-soft);
    }

    .palette-tabs {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }
    .palette-tabs::-webkit-scrollbar { display: none; }

    .palette-tab-btn {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        border: 1px solid var(--exam-border);
        background: var(--exam-surface);
        color: var(--exam-text-secondary);
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .palette-tab-btn:hover {
        border-color: var(--cat-gold);
        color: var(--cat-navy-mid);
    }
    .palette-tab-btn.active {
        background: var(--cat-navy);
        border-color: var(--cat-gold);
        color: #FFFFFF;
    }
    [data-bs-theme="dark"] .palette-tab-btn.active {
        background: #1E293B;
        border-color: var(--cat-gold);
        color: #FBBF24;
    }

    /* Grid Nomor Soal */
    .grid-nomor-wrapper {
        padding: 16px;
        overflow-y: auto;
        flex: 1;
    }

    .grid-nomor {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
    }

    .no-pill {
        aspect-ratio: 1/1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 0.86rem;
        font-weight: 800;
        cursor: pointer;
        background: var(--exam-surface-soft);
        color: var(--exam-text-primary);
        border: 1px solid var(--exam-border);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .no-pill:hover {
        transform: translateY(-2px);
        border-color: var(--cat-gold);
        box-shadow: 0 4px 12px rgba(11, 37, 69, 0.08);
    }
    .no-pill.active {
        border: 2px solid var(--cat-gold) !important;
        background: var(--cat-navy) !important;
        color: #FFFFFF !important;
        box-shadow: 0 6px 16px rgba(11, 37, 69, 0.25);
        transform: scale(1.05);
        z-index: 2;
    }
    .no-pill.status-answered {
        background: rgba(16, 185, 129, 0.14);
        color: #059669;
        border-color: rgba(16, 185, 129, 0.40);
    }
    [data-bs-theme="dark"] .no-pill.status-answered {
        background: rgba(16, 185, 129, 0.22);
        color: #34D399;
    }
    .no-pill.status-doubt {
        background: rgba(217, 119, 6, 0.16);
        color: #D97706;
        border-color: rgba(217, 119, 6, 0.45);
    }
    [data-bs-theme="dark"] .no-pill.status-doubt {
        background: rgba(217, 119, 6, 0.25);
        color: #FBBF24;
    }
    .no-pill.status-skipped {
        background: rgba(239, 68, 68, 0.12);
        color: #DC2626;
        border-color: rgba(239, 68, 68, 0.35);
    }
    [data-bs-theme="dark"] .no-pill.status-skipped {
        background: rgba(239, 68, 68, 0.20);
        color: #F87171;
    }

    /* Content Area */
    .cat-main {
        flex: 1;
        overflow-y: auto;
        padding: 30px 24px 90px 24px;
        scroll-behavior: smooth;
    }

    /* Question Card */
    .exam-question-card {
        background: var(--exam-surface);
        border: 1px solid var(--exam-border);
        border-radius: 22px;
        box-shadow: var(--exam-shadow-lg);
        overflow: hidden;
        transition: transform 0.25s ease;
    }

    .exam-question-header {
        padding: 20px 28px;
        background: linear-gradient(135deg, rgba(11, 37, 69, 0.03) 0%, rgba(197, 155, 39, 0.05) 100%);
        border-bottom: 1px solid var(--exam-border-gold);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .exam-question-body {
        padding: 28px;
    }

    /* Font Size Scaler Group */
    .font-scaler-group {
        display: inline-flex;
        align-items: center;
        background: var(--exam-surface-soft);
        border: 1px solid var(--exam-border);
        border-radius: 10px;
        padding: 2px;
        gap: 2px;
    }

    .btn-font-scale {
        padding: 4px 10px;
        font-size: 0.78rem;
        font-weight: 700;
        border: none;
        background: transparent;
        color: var(--exam-text-secondary);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-font-scale:hover, .btn-font-scale.active {
        background: var(--exam-surface);
        color: var(--cat-navy);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
    }
    [data-bs-theme="dark"] .btn-font-scale.active {
        color: #FBBF24;
    }

    /* Question Typography */
    .question-content-text {
        line-height: 1.85;
        font-weight: 500;
        transition: font-size 0.2s ease;
    }
    .fs-scale-sm { font-size: 1.05rem; }
    .fs-scale-md { font-size: 1.20rem; }
    .fs-scale-lg { font-size: 1.35rem; }

    /* TKP Banner */
    .tkp-notice-banner {
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.08) 0%, rgba(197, 155, 39, 0.12) 100%);
        border: 1px solid rgba(217, 119, 6, 0.30);
        border-left: 4px solid #D97706;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--exam-text-primary);
    }

    /* Interactive Options A-E (Tactile & Fluid) */
    .option-card-row {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 24px;
    }

    .option-pill-card {
        padding: 16px 20px;
        background: var(--exam-surface-soft);
        border: 1.5px solid var(--exam-border);
        border-radius: 16px;
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                    border-color 0.2s ease,
                    background-color 0.2s ease;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        color: var(--exam-text-primary);
        user-select: none;
        position: relative;
    }
    .option-pill-card:hover {
        border-color: rgba(197, 155, 39, 0.55);
        background: var(--exam-surface);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(11, 37, 69, 0.07);
    }
    .option-pill-card:active {
        transform: translateY(0) scale(0.995);
    }
    .option-pill-card.selected {
        background: rgba(197, 155, 39, 0.08) !important;
        border: 2px solid #C59B27 !important;
        box-shadow: 0 6px 22px rgba(197, 155, 39, 0.20), inset 0 0 0 1px rgba(197, 155, 39, 0.30) !important;
        transform: translateY(-2px);
    }
    [data-bs-theme="dark"] .option-pill-card.selected {
        background: rgba(197, 155, 39, 0.16) !important;
        border-color: #FBBF24 !important;
        box-shadow: 0 6px 22px rgba(197, 155, 39, 0.30), inset 0 0 0 1px rgba(251, 191, 36, 0.40) !important;
    }

    .option-letter-badge {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: var(--exam-surface);
        border: 1.5px solid var(--exam-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.02rem;
        color: var(--cat-navy);
        flex-shrink: 0;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    [data-bs-theme="dark"] .option-letter-badge {
        color: #F8FAFC;
    }
    .option-pill-card.selected .option-letter-badge {
        background: linear-gradient(135deg, #0B2545 0%, #133E87 100%) !important;
        border-color: #C59B27 !important;
        color: #FBBF24 !important;
        box-shadow: 0 4px 12px rgba(11, 37, 69, 0.40) !important;
        transform: scale(1.05);
    }

    .option-text-body {
        flex: 1;
        font-size: calc(1.05rem * var(--exam-content-scale, 1));
        line-height: 1.65;
        padding-top: 4px;
        color: var(--exam-text-primary);
        transition: font-size 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .option-text-body p {
        margin-bottom: 0;
    }

    /* Option Radio Check Indicator */
    .option-check-wrap {
        flex-shrink: 0;
    }
    .option-radio-dot {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid var(--exam-border);
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        color: #FFFFFF;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .option-radio-dot.is-selected {
        border-color: #C59B27;
        background: linear-gradient(135deg, #C59B27 0%, #D97706 100%);
        box-shadow: 0 2px 8px rgba(197, 155, 39, 0.45);
        transform: scale(1.08);
    }

    /* Mobile Scrim & Drawer Handle */
    .cat-mobile-scrim {
        position: fixed;
        inset: 0;
        background: rgba(11, 37, 69, 0.65);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 1045;
        transition: opacity 0.25s ease;
    }
    .cat-drawer-handle {
        width: 100%;
        display: flex;
        justify-content: center;
        padding: 10px 0 6px;
        cursor: pointer;
    }
    .cat-drawer-handle-bar {
        width: 44px;
        height: 5px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.45);
        transition: background 0.2s ease;
    }
    .cat-drawer-handle:hover .cat-drawer-handle-bar {
        background: var(--cat-gold);
    }

    /* Action Dock Toolbar */
    .exam-action-dock {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid var(--exam-border);
    }

    .btn-exam-action {
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.90rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border: 1px solid var(--exam-border);
        cursor: pointer;
        min-height: 46px;
    }
    .btn-exam-prev {
        background: var(--exam-surface);
        color: var(--exam-text-primary);
    }
    .btn-exam-prev:hover:not(:disabled) {
        background: var(--exam-surface-soft);
        border-color: var(--cat-navy-mid);
        transform: translateX(-2px);
    }
    .btn-exam-doubt {
        background: rgba(217, 119, 6, 0.10);
        color: #D97706;
        border-color: rgba(217, 119, 6, 0.30);
    }
    .btn-exam-doubt:hover, .btn-exam-doubt.active {
        background: #D97706;
        color: #FFFFFF;
        border-color: #D97706;
        box-shadow: 0 6px 16px rgba(217, 119, 6, 0.35);
    }
    .btn-exam-bookmark {
        background: var(--exam-surface);
        color: var(--exam-text-secondary);
    }
    .btn-exam-bookmark.active {
        background: rgba(19, 62, 135, 0.12);
        color: var(--cat-navy-mid);
        border-color: var(--cat-navy-mid);
    }
    .btn-exam-next {
        background: linear-gradient(135deg, #0B2545 0%, #133E87 100%);
        color: #FFFFFF !important;
        border: 1px solid rgba(197, 155, 39, 0.40);
        box-shadow: 0 4px 14px rgba(11, 37, 69, 0.25);
    }
    .btn-exam-next:hover {
        background: linear-gradient(135deg, #133E87 0%, #1D4ED8 100%);
        transform: translateX(2px);
    }
    .btn-exam-finish {
        background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%);
        color: #FFFFFF !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
    }
    .btn-exam-finish:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.45);
    }

    /* MOBILE DEDICATED LAYOUT (ZERO COLLISION) */
    @media (max-width: 767px) {
        .cat-header {
            height: calc(62px + env(safe-area-inset-top));
            padding: env(safe-area-inset-top) 12px 0 12px;
            gap: 8px;
        }
        .cat-brand-logo-shell {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            padding: 3px;
        }
        .cat-brand-title {
            font-size: 0.88rem;
        }
        .cat-brand-pkt-badge {
            font-size: 0.64rem;
            padding: 1px 6px;
        }
        .cat-brand-sub {
            font-size: 0.68rem;
            max-width: 140px;
        }
        .cat-header-actions {
            gap: 6px;
        }
        .btn-cat-nav {
            height: 34px;
            padding: 0 8px;
            border-radius: 8px;
        }
        .btn-cat-nav-theme {
            width: 34px;
            padding: 0;
        }
        .cat-timer-pill {
            height: 34px;
            padding: 0 10px;
            border-radius: 8px;
            gap: 6px;
        }
        .cat-timer-pill .cat-timer-number {
            font-size: 1.05rem;
        }
        .cat-timer-pill i {
            font-size: 0.82rem;
        }

        .cat-sidebar {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 75vh !important;
            margin-left: 0 !important;
            transform: translateY(100%);
            border-radius: 24px 24px 0 0 !important;
            box-shadow: 0 -12px 36px rgba(0, 0, 0, 0.5) !important;
            z-index: 1060 !important;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .cat-sidebar.mobile-open {
            transform: translateY(0) !important;
        }
        .sidebar-collapsed {
            margin-left: 0 !important;
        }
        .btn-desktop-toggle { display: none !important; }
        
        .cat-main {
            padding: 14px 12px 100px 12px;
        }
        .exam-question-header {
            padding: 14px 18px;
        }
        .exam-question-body {
            padding: 18px 16px;
        }
        .grid-nomor {
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }

        .btn-floating-mobile-dock {
            position: fixed;
            bottom: calc(18px + env(safe-area-inset-bottom));
            right: 18px;
            z-index: 1060;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0B2545 0%, #133E87 100%);
            border: 2px solid #C59B27;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.20rem;
            box-shadow: 0 10px 25px rgba(11, 37, 69, 0.45);
            cursor: pointer;
        }
    }

    /* Fluid Question Fade Transition */
    .fade-exam-enter {
        animation: fadeExamIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        will-change: opacity, transform;
    }
    @keyframes fadeExamIn {
        0% { opacity: 0.25; transform: translateY(5px); }
        100% { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

<div x-data="catExam()" x-init="init()" class="cat-wrapper" @keydown.window="handleKeyboard($event)">
    
    <!-- TOP COMMAND HEADER -->
    <header class="cat-header">
        <!-- BRAND & LOGO (LEFT) -->
        <div class="cat-header-brand">
            <div class="cat-brand-logo-shell">
                <img src="/icons/cat-logo.png?v=20261008-4" alt="CAT ASN Logo" class="cat-brand-logo-img">
            </div>
            <div class="cat-brand-info">
                <div class="cat-brand-title-row">
                    <span class="cat-brand-title">CAT ASN</span>
                    <span class="cat-brand-pkt-badge">PKT {{ $ujian->paket }}</span>
                </div>
                <div class="cat-brand-sub">
                    {{ (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'exam_mode_id') && $ujian->examMode) ? $ujian->examMode->name : 'Simulasi Mandiri' }}
                    @if(\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'sub_category_id') && $ujian->subCategory)
                        · {{ $ujian->subCategory->name }}
                    @endif
                </div>
            </div>
        </div>

        <!-- PARTICIPANT & AUTOSAVE STATUS (CENTER, DESKTOP ONLY >= 992px) -->
        <div class="cat-header-hud d-none d-lg-flex">
            <div class="cat-hud-name">
                {{ strtoupper($ujian->nama_peserta) }}
            </div>
            <div class="cat-hud-meta">
                <span>NO: {{ str_pad($ujian->user_id, 6, '0', STR_PAD_LEFT) }}</span>
                <span class="text-white-50">·</span>
                <span class="text-success"><i class="fas fa-cloud-arrow-up me-1"></i>Autosave Aktif</span>
            </div>
            @if(isset($pesertaLomba) && count($pesertaLomba) > 0)
                <button type="button" data-bs-toggle="modal" data-bs-target="#modalPesertaLomba" class="badge rounded-pill mt-1" style="background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #34D399; cursor: pointer; text-decoration: none;">
                    <i class="fas fa-users me-1"></i> Mode Bersama ({{ count($pesertaLomba) }} Aktif)
                </button>
            @endif
        </div>

        <!-- ACTIONS & TIMER (RIGHT) -->
        <div class="cat-header-actions">
            <!-- Break/Pause Button -->
            <button @click="pause()" class="btn-cat-nav" title="Istirahat & Simpan Progres">
                <i class="fas fa-mug-hot text-warning"></i>
                <span class="d-none d-md-inline">Istirahat</span>
            </button>

            <!-- Theme Toggle Button -->
            <button class="btn-cat-nav btn-cat-nav-theme" @click="toggleTheme()" title="Ubah Tema">
                <i class="fas" :class="themeIcon()"></i>
            </button>

            <!-- Countdown Timer Pill -->
            <div class="cat-timer-pill" :class="timeLeft < 300 ? 'critical' : (timeLeft < 900 ? 'warning' : '')">
                <i class="far fa-clock" :class="timeLeft < 300 ? 'text-danger' : 'text-warning'"></i>
                <span class="cat-timer-number" id="timerDisplay">00:00</span>
            </div>
        </div>
    </header>

    <div class="cat-body">
        <!-- DESKTOP SIDEBAR OPEN BUTTON (IF COLLAPSED) -->
        <button x-show="!sidebarOpen" @click="sidebarOpen = true" class="btn-desktop-toggle d-none d-md-block" title="Buka Navigasi Soal">
            <i class="fas fa-table-cells fa-lg"></i>
        </button>

        <!-- MOBILE FLOATING ACTION BUTTON (NAV TOGGLE) -->
        <button @click="sidebarOpen = !sidebarOpen" class="btn-floating-mobile-dock d-md-none" title="Daftar Soal">
            <i class="fas" :class="sidebarOpen ? 'fa-xmark' : 'fa-list-ol'"></i>
        </button>

        <!-- MOBILE BACKDROP SCRIM (TAP OUTSIDE TO DISMISS) -->
        <div x-show="sidebarOpen && window.innerWidth <= 768"
             @click="sidebarOpen = false"
             class="cat-mobile-scrim d-md-none"
             style="display: none;"></div>

        <!-- SIDEBAR PALETTE DRAWER -->
        <aside :class="{
            'cat-sidebar': true,
            'sidebar-collapsed': !sidebarOpen,
            'mobile-open': sidebarOpen && window.innerWidth <= 768
        }">
            <!-- Mobile Drawer Grab Handle -->
            <div class="cat-drawer-handle d-md-none" @click="sidebarOpen = false">
                <span class="cat-drawer-handle-bar"></span>
            </div>

            <!-- Sidebar Header -->
            <div class="p-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--exam-border); background: var(--exam-surface-soft);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-compass text-primary"></i>
                    <strong style="color: var(--exam-text-primary); font-size: 0.90rem;">NAVIGASI LEMBAR SOAL</strong>
                </div>
                <button @click="sidebarOpen = false" class="btn btn-sm btn-outline-secondary border-0 p-1 rounded-circle" style="width: 30px; height: 30px;" title="Tutup">
                    <i class="fas fa-chevron-left d-none d-md-inline"></i>
                    <i class="fas fa-xmark d-md-none"></i>
                </button>
            </div>

            <!-- Filter Tabs (Kategori & Status) -->
            <div class="palette-filter-bar">
                <div class="palette-tabs mb-2">
                    <button class="palette-tab-btn" :class="activeCategoryFilter === 'ALL' ? 'active' : ''" @click="activeCategoryFilter = 'ALL'">
                        Semua (<span x-text="total"></span>)
                    </button>
                    <template x-for="cat in availableCategories" :key="cat">
                        <button class="palette-tab-btn" :class="activeCategoryFilter === cat ? 'active' : ''" @click="activeCategoryFilter = cat" x-text="cat">
                        </button>
                    </template>
                </div>
                <div class="d-flex align-items-center justify-content-between small text-secondary" style="font-size: 0.72rem;">
                    <span><strong class="text-success" x-text="countAnswered()"></strong> Terjawab</span>
                    <span><strong class="text-warning" x-text="raguSoals.length"></strong> Ragu</span>
                    <span><strong class="text-danger" x-text="countSkipped()"></strong> Dilewati</span>
                </div>
            </div>

            <!-- Grid Nomor Soal -->
            <div class="grid-nomor-wrapper">
                <div class="grid-nomor">
                    <template x-for="(s, idx) in filteredSoals" :key="s.id">
                        <div class="no-pill"
                             :class="{
                                 'active': index === s._originalIndex,
                                 'status-doubt': raguSoals.includes(s.id),
                                 'status-answered': jawabanUser[s.id] && !raguSoals.includes(s.id),
                                 'status-skipped': !jawabanUser[s.id] && visitedSoals.includes(s.id) && !raguSoals.includes(s.id)
                             }"
                             @click="goToSoal(s._originalIndex)">
                            <span x-text="s._originalIndex + 1"></span>
                            <template x-if="jawabanUser[s.id]">
                                <span style="font-size: 0.65rem; font-weight: 900; line-height: 1; margin-top: 2px;" x-text="jawabanUser[s.id]"></span>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Legend Status Footer -->
            <div class="p-3" style="border-top: 1px solid var(--exam-border); background: var(--exam-surface-soft);">
                <div class="row g-2 text-secondary" style="font-size: 0.72rem; font-weight: 600;">
                    <div class="col-6 d-flex align-items-center gap-2">
                        <span class="rounded-circle" style="width: 10px; height: 10px; background: #10B981;"></span>
                        Terjawab (<span x-text="countAnswered()"></span>)
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <span class="rounded-circle" style="width: 10px; height: 10px; background: #D97706;"></span>
                        Ragu-ragu (<span x-text="raguSoals.length"></span>)
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <span class="rounded-circle" style="width: 10px; height: 10px; background: #EF4444;"></span>
                        Dilewati (<span x-text="countSkipped()"></span>)
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <span class="rounded-circle" style="width: 10px; height: 10px; background: var(--exam-border);"></span>
                        Belum Dibuka
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN QUESTION WORKSPACE -->
        <main class="cat-main" id="examMainArea">
            <div class="container-fluid px-0 px-md-3">
                <div class="row justify-content-center">
                    <div class="col-xl-9 col-lg-11">
                        
                        <!-- QUESTION CARD -->
                        <div class="exam-question-card fade-exam-enter" :key="index">
                            <!-- Card Header -->
                            <div class="exam-question-header">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center text-white rounded-3 fw-bold" style="width: 44px; height: 44px; font-size: 1.15rem; background: linear-gradient(135deg, #0B2545 0%, #133E87 100%) !important; border: 1.5px solid #C59B27; box-shadow: 0 4px 10px rgba(11, 37, 69, 0.25);">
                                        <span x-text="index + 1"></span>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: var(--exam-text-primary); font-size: 0.95rem;">
                                            SOAL NOMOR <span x-text="index + 1"></span>
                                        </div>
                                        <div class="small text-secondary">
                                            Dari total <span x-text="total"></span> butir soal ujian
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <!-- Font Size Adjuster -->
                                    <div class="font-scaler-group d-none d-sm-inline-flex" title="Sesuaikan Ukuran Font">
                                        <button class="btn-font-scale" :class="fontSize === 'sm' ? 'active' : ''" @click="fontSize = 'sm'">A-</button>
                                        <button class="btn-font-scale" :class="fontSize === 'md' ? 'active' : ''" @click="fontSize = 'md'">A</button>
                                        <button class="btn-font-scale" :class="fontSize === 'lg' ? 'active' : ''" @click="fontSize = 'lg'">A+</button>
                                    </div>

                                    <!-- Kategori Badge -->
                                    <span class="badge px-3 py-2 rounded-pill font-monospace" style="background: rgba(19, 62, 135, 0.12); color: #133E87; border: 1px solid rgba(19, 62, 135, 0.25); font-size: 0.80rem;" x-text="currentSoal.kategori"></span>
                                </div>
                            </div>

                            <!-- Card Body with Fluid Typography Scale -->
                            <div class="exam-question-body" :style="{ '--exam-content-scale': fontSize === 'sm' ? '0.90' : (fontSize === 'lg' ? '1.18' : '1') }">
                                <!-- TKP Guidance Banner (Jika Kategori TKP) -->
                                <template x-if="currentSoal.kategori === 'TKP'">
                                    <div class="tkp-notice-banner">
                                        <i class="fas fa-circle-info fa-lg text-warning"></i>
                                        <div>
                                            <strong style="font-size: 0.88rem;">Karakteristik Penilaian TKP</strong>
                                            <div class="small text-secondary">Setiap opsi jawaban memiliki bobot nilai 1 sampai 5 poin. Pilihlah opsi yang paling mencerminkan integritas dan profesionalitas ASN.</div>
                                        </div>
                                    </div>
                                </template>

                                <!-- QUESTION TEXT -->
                                <div class="question-content-text mb-4" 
                                     :class="{
                                         'fs-scale-sm': fontSize === 'sm',
                                         'fs-scale-md': fontSize === 'md',
                                         'fs-scale-lg': fontSize === 'lg'
                                     }" 
                                     x-html="formatQuestion(currentSoal)">
                                </div>

                                <!-- QUESTION IMAGE (IF ATTACHED) -->
                                <template x-if="currentSoal.gambar_path">
                                    <div class="mb-4 text-center p-3 rounded-4" style="background: var(--exam-surface-soft); border: 1px solid var(--exam-border);">
                                        <img :src="'/storage/' + currentSoal.gambar_path" class="img-fluid rounded-3" style="max-height: 380px; box-shadow: var(--exam-shadow);" alt="Ilustrasi Soal">
                                    </div>
                                </template>

                                <!-- ANSWER OPTIONS A - E -->
                                <div class="option-card-row">
                                    <template x-for="pilih in ['A','B','C','D','E']">
                                        <div class="option-pill-card"
                                             :class="jawabanUser[currentSoal.id] === pilih ? 'selected' : ''"
                                             @click="saveAnswer(currentSoal.id, pilih)">
                                            
                                            <div class="option-letter-badge" x-text="pilih"></div>

                                            <div class="option-text-body">
                                                <!-- Opsi Image (Jika Opsi Berupa Gambar Figural) -->
                                                <template x-if="currentSoal['gambar_' + pilih.toLowerCase()]">
                                                    <div class="mb-2">
                                                        <img :src="'/storage/' + currentSoal['gambar_' + pilih.toLowerCase()]" class="img-fluid rounded-2" style="max-height: 160px; border: 1px solid var(--exam-border);" alt="Opsi Gambar">
                                                    </div>
                                                </template>
                                                <!-- Opsi Teks -->
                                                <div x-show="currentSoal['opsi_' + pilih.toLowerCase()] !== '-'" x-html="currentSoal['opsi_' + pilih.toLowerCase()]"></div>
                                            </div>

                                            <!-- Radio Indicator with Tactile Checkmark -->
                                            <div class="option-check-wrap ms-auto align-self-center pe-1">
                                                <div class="option-radio-dot" :class="jawabanUser[currentSoal.id] === pilih ? 'is-selected' : ''">
                                                    <i class="fas fa-check" x-show="jawabanUser[currentSoal.id] === pilih"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ACTION DOCK (TOOLBAR) -->
                                <div class="exam-action-dock">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <!-- Previous Button -->
                                        <button class="btn-exam-action btn-exam-prev"
                                                @click="prev()" :disabled="index === 0" :style="index === 0 ? 'opacity: 0.4; cursor: not-allowed;' : ''">
                                            <i class="fas fa-arrow-left"></i> Sebelumnya
                                        </button>

                                        <!-- Doubt Toggle Button -->
                                        <button class="btn-exam-action btn-exam-doubt"
                                                :class="raguSoals.includes(currentSoal.id) ? 'active' : ''"
                                                @click="toggleRagu(currentSoal.id)">
                                            <i class="fas fa-flag"></i>
                                            <span x-text="raguSoals.includes(currentSoal.id) ? 'Status: Ragu-Ragu' : 'Tandai Ragu'"></span>
                                        </button>

                                        <!-- Bookmark / Favorite Button -->
                                        <button class="btn-exam-action btn-exam-bookmark"
                                                :class="bookmarkedSoals.includes(currentSoal.id) ? 'active' : ''"
                                                @click="toggleBookmark(currentSoal.id)">
                                            <i class="fas fa-bookmark" :class="bookmarkedSoals.includes(currentSoal.id) ? 'text-primary' : ''"></i>
                                            <span x-text="bookmarkedSoals.includes(currentSoal.id) ? 'Tersimpan' : 'Simpan Soal'"></span>
                                        </button>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                        <!-- Next Button (If not on last question) -->
                                        <button class="btn-exam-action btn-exam-next"
                                                @click="next()" x-show="index < total - 1">
                                            Selanjutnya <i class="fas fa-arrow-right"></i>
                                        </button>

                                        <!-- Finish Exam Button -->
                                        <button class="btn-exam-action btn-exam-finish"
                                                @click="openFinishModal()" id="finishBtn">
                                            <i class="fas fa-check-double"></i> Akhiri Ujian
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL KONFIRMASI AKHIRI UJIAN ELEGAN -->
    <div class="modal fade" id="modalFinishConfirmation" tabindex="-1" aria-hidden="true" style="z-index: 100000;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg" style="background: var(--exam-surface); color: var(--exam-text-primary); border: 1px solid var(--exam-border-gold) !important;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-shield-halved text-warning me-2"></i> Konfirmasi Pengakhiran Ujian
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Periksa kembali status pengerjaan butir soal Anda sebelum mengunci hasil ujian:
                    </p>
                    
                    <div class="row g-2 text-center mb-4">
                        <div class="col-4">
                            <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3);">
                                <div class="fs-4 fw-bold text-success" x-text="countAnswered()"></div>
                                <div class="small text-secondary" style="font-size: 0.72rem;">Terjawab</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3" style="background: rgba(217, 119, 6, 0.12); border: 1px solid rgba(217, 119, 6, 0.3);">
                                <div class="fs-4 fw-bold text-warning" x-text="raguSoals.length"></div>
                                <div class="small text-secondary" style="font-size: 0.72rem;">Ragu-ragu</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3);">
                                <div class="fs-4 fw-bold text-danger" x-text="total - countAnswered()"></div>
                                <div class="small text-secondary" style="font-size: 0.72rem;">Kosong</div>
                            </div>
                        </div>
                    </div>

                    <template x-if="(total - countAnswered()) > 0 || raguSoals.length > 0">
                        <div class="alert alert-warning border-0 small d-flex align-items-center gap-2 rounded-3 mb-0">
                            <i class="fas fa-triangle-exclamation fa-lg"></i>
                            <div>Masih terdapat soal yang belum terjawab atau ragu-ragu. Anda tetap yakin ingin mengakhiri sesi sekarang?</div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">
                        Lanjut Kerjakan
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" @click="confirmFinishNow()" id="confirmFinishBtn">
                        <i class="fas fa-check me-1"></i> Ya, Akhiri Ujian
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL PESERTA LOMBA BARENG --}}
@if(isset($pesertaLomba) && count($pesertaLomba) > 0)
<div class="modal fade" id="modalPesertaLomba" tabindex="-1" aria-hidden="true" style="z-index: 100000;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background: var(--exam-surface); color: var(--exam-text-primary); border: 1px solid var(--exam-border) !important;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-users me-2 text-success"></i> Peserta Test Bersama</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary small">Seluruh peserta di bawah ini sedang mengerjakan paket soal yang identik secara serentak.</p>
                <div class="list-group list-group-flush rounded-3 overflow-hidden">
                    @foreach($pesertaLomba as $p)
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3" style="background: var(--exam-surface-soft); border-bottom: 1px solid var(--exam-border); color: var(--exam-text-primary);">
                        <div class="d-flex flex-column">
                            <span class="fw-bold">{{ strtoupper($p->nama_peserta) }}</span>
                            @if($p->id === $ujian->id)
                                <span class="badge bg-success mt-1" style="width: fit-content;">Sesi Anda</span>
                            @endif
                        </div>
                        <div class="text-end">
                            @if($p->is_selesai)
                                <span class="badge bg-danger bg-opacity-20 text-danger">Selesai</span>
                            @else
                                <span class="badge bg-primary bg-opacity-20 text-primary font-monospace"><i class="far fa-clock"></i> {{ floor($p->sisa_waktu / 60) }}m</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
function catExam() {
    return {
        index: 0,
        sidebarOpen: window.innerWidth > 1200,
        soals: @json($soals),
        jawabanUser: @json($ujian->jawaban_user) || {},
        visitedSoals: [],
        raguSoals: [],
        bookmarkedSoals: @json($bookmarkedSoalIds ?? []),
        total: {{ $soals->count() }},
        timeLeft: {{ $ujian->sisa_waktu }},
        fontSize: 'md',
        activeCategoryFilter: 'ALL',

        get currentSoal() { 
            return this.soals[this.index] || {}; 
        },

        formatQuestion(soal) {
            if (!soal || !soal.teks_soal) return '';
            let text = (soal.teks_soal || '').trim();
            
            // 1. Strip accidental option rows
            text = text.replace(/\n\s*[A-E]\.\s*.*(?:\n\s*[A-E]\.\s*.*)*$/s, '').trim();
            
            const kategori = (soal.kategori || '').toUpperCase().trim();
            const subTopik = (soal.sub_topik || '').toLowerCase().trim();
            const isDeretTopic = /deret|barisan|sequence|pola\s*angka/i.test(subTopik);
            
            const esc = (str) => {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };
            const nl2br = (str) => esc(str).replace(/\n/g, '<br>');
            
            // 2. Try TABEL DATA (Markdown or pipe-separated)
            const lines = text.split(/\r?\n/);
            if (lines.length >= 2) {
                let tableStart = -1, tableEnd = -1, inTable = false;
                for (let i = 0; i < lines.length; i++) {
                    const l = lines[i].trim();
                    const pipeCount = (l.match(/\|/g) || []).length;
                    const isTableRow = pipeCount >= 2 || (l.startsWith('|') && l.endsWith('|'));
                    if (isTableRow) {
                        if (!inTable) { inTable = true; tableStart = i; }
                        tableEnd = i;
                    } else if (inTable) {
                        break;
                    }
                }

                if (tableStart !== -1 && (tableEnd - tableStart + 1) >= 2) {
                    const tableLines = lines.slice(tableStart, tableEnd + 1);
                    const before = lines.slice(0, tableStart).join('\n').trim();
                    const after = lines.slice(tableEnd + 1).join('\n').trim();

                    const parsedRows = [];
                    for (const rawLine of tableLines) {
                        const l = rawLine.trim();
                        if (!l) continue;
                        if (/^\|?[\s\-:|]+\|?$/.test(l) && l.includes('-')) continue;
                        const trimmed = l.replace(/^\|+|\|+$/g, '').trim();
                        const cells = trimmed.split('|').map(c => c.trim());
                        if (cells.length > 0) parsedRows.push(cells);
                    }

                    if (parsedRows.length >= 2) {
                        const headers = parsedRows[0];
                        const dataRows = parsedRows.slice(1);

                        let html = '<div class="soal-tabel-container my-3">';
                        html += '<div class="soal-tabel-badge"><i class="fas fa-table-list me-1"></i> Data Tabel &amp; Analisis Informasi</div>';
                        if (before) html += '<div class="mb-2 text-secondary" style="font-size: 0.95rem;">' + nl2br(before) + '</div>';

                        html += '<div class="soal-tabel-wrapper">';
                        html += '<table class="soal-data-table">';
                        html += '<thead><tr>';
                        headers.forEach(th => { html += `<th>${esc(th)}</th>`; });
                        html += '</tr></thead>';
                        html += '<tbody>';
                        dataRows.forEach(row => {
                            html += '<tr>';
                            for (let c = 0; c < headers.length; c++) {
                                html += `<td>${esc(row[c] || '')}</td>`;
                            }
                            html += '</tr>';
                        });
                        html += '</tbody></table></div>';

                        if (after) {
                            html += '<div class="soal-pertanyaan-box mt-3">';
                            html += '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Inti Pertanyaan:</div>';
                            html += '<div class="soal-pertanyaan-text">' + nl2br(after) + '</div>';
                            html += '</div>';
                        }
                        html += '</div>';
                        return html;
                    }
                }
            }

            // 3. Try DERET ANGKA / HURUF Visualizer
            const tokenPat = '(?:[-+]?\\d+(?:[.,\\/]?\\d+)?|\\b[A-Z]\\b|\\?{1,3}|\\.{2,4}|_{1,4})';
            const seqPat = new RegExp('(' + tokenPat + '(?:\\s*,\\s*' + tokenPat + '){2,}(?:\\s*,\\s*' + tokenPat + ')*)', 'i');
            const deretMatch = text.match(seqPat);
            
            if (deretMatch) {
                const rawSeq = deretMatch[1];
                const matchPos = deretMatch.index;
                const tokens = rawSeq.split(',').map(t => t.trim()).filter(Boolean);
                
                if (tokens.length >= 3) {
                    let before = text.substring(0, matchPos).trim().replace(/[:\\s,]+$/, '');
                    let after = text.substring(matchPos + rawSeq.length).trim().replace(/^[.,\\s]+/, '');
                    
                    let html = '<div class="soal-deret-container my-3">';
                    html += '<div class="soal-deret-header">';
                    html += '<span class="soal-deret-badge"><i class="fas fa-arrow-trend-up me-1"></i> Pola Barisan &amp; Deret</span>';
                    html += '</div>';
                    
                    if (before) {
                        html += '<div class="soal-deret-intro mb-2">' + esc(before) + '</div>';
                    }
                    
                    html += '<div class="soal-deret-track-wrapper">';
                    html += '<div class="soal-deret-track">';
                    
                    tokens.forEach((t, idx) => {
                        const isTarget = ['?', '??', '???', '...', '....', '_', '__', '___', 'X', 'Y'].includes(t) || (idx === tokens.length - 1 && t === '?');
                        const idxLabel = isTarget ? '<span class="text-warning fw-bold">Target</span>' : `U<sub>${idx + 1}</sub>`;
                        
                        html += `<div class="soal-deret-token ${isTarget ? 'is-target' : ''}">`;
                        html += `<div class="soal-deret-val">${esc(t)}</div>`;
                        html += `<div class="soal-deret-idx">${idxLabel}</div>`;
                        html += `</div>`;
                        
                        if (idx < tokens.length - 1) {
                            html += '<div class="soal-deret-connector"><i class="fas fa-arrow-right"></i></div>';
                        }
                    });
                    
                    html += '</div>';
                    html += '</div>';
                    
                    if (after) {
                        html += '<div class="soal-pertanyaan-box mt-3">';
                        html += '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Inti Pertanyaan:</div>';
                        html += '<div class="soal-pertanyaan-text">' + esc(after) + '</div>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    return html;
                }
            }
            
            // 3. Try PERBANDINGAN KUANTITATIF (X vs Y)
            const compMatch = text.match(/\b([xXpP])\s*=\s*([^,\n;]+?)\s*(?:,|dan|serta|sedangkan)\s*([yYqQ])\s*=\s*([^.\n;]+?)(?:\.|\n|$)/i);
            if (compMatch) {
                const var1 = compMatch[1].toUpperCase();
                const val1 = compMatch[2].trim();
                const var2 = compMatch[3].toUpperCase();
                const val2 = compMatch[4].trim();
                
                const fullMatch = compMatch[0];
                const matchIdx = compMatch.index;
                const before = text.substring(0, matchIdx).trim();
                const after = text.substring(matchIdx + fullMatch.length).trim();
                
                let html = '<div class="soal-comparison-container my-3">';
                html += '<div class="soal-comparison-badge mb-2"><i class="fas fa-scale-balanced me-1"></i> Perbandingan Kuantitatif</div>';
                
                if (before) {
                    html += '<div class="mb-2 text-secondary" style="font-size: 0.95rem;">' + esc(before) + '</div>';
                }
                
                html += '<div class="soal-comparison-grid">';
                html += `<div class="soal-comp-card"><div class="soal-comp-label">Nilai ${esc(var1)}</div><div class="soal-comp-val">${esc(val1)}</div></div>`;
                html += '<div class="soal-comp-vs">VS</div>';
                html += `<div class="soal-comp-card"><div class="soal-comp-label">Nilai ${esc(var2)}</div><div class="soal-comp-val">${esc(val2)}</div></div>`;
                html += '</div>';
                
                if (after) {
                    html += '<div class="soal-pertanyaan-box mt-3">';
                    html += '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Hubungan yang Ditanyakan:</div>';
                    html += '<div class="soal-pertanyaan-text">' + esc(after) + '</div>';
                    html += '</div>';
                }
                
                html += '</div>';
                return html;
            }
            
            // 5. Try ANALOGI KATA (A : B = C : ?)
            const analogiPat = /(?:^|\n|\b)([^:\n\r=]{1,40})\s*:\s*([^:\n\r=]{1,40})\s*(?:=|==|::|\bsepadan dengan\b)\s*([^:\n\r=]{1,40})\s*:\s*([^:\n\r]{1,40})(?:\.|\n|$)/i;
            const analogiMatch = text.match(analogiPat);
            if (analogiMatch) {
                const term1 = analogiMatch[1].trim();
                const term2 = analogiMatch[2].trim();
                const term3 = analogiMatch[3].trim();
                const term4 = analogiMatch[4].trim();

                if (term1.length <= 35 && term2.length <= 35 && term3.length <= 35 && term4.length <= 35) {
                    const fullMatch = analogiMatch[0];
                    const matchIdx = analogiMatch.index;
                    const before = text.substring(0, matchIdx).trim();
                    const after = text.substring(matchIdx + fullMatch.length).trim();

                    const isUnk = (t) => ['?', '??', '???', '...', '....', '…', '_', '__', '___'].includes(t) || /^(\?+|\.{2,}|_{2,})$/.test(t);

                    let html = '<div class="soal-analogi-container my-3">';
                    html += '<div class="soal-analogi-badge"><i class="fas fa-arrows-split-up-and-left me-1"></i> Analogi Pasangan Kata</div>';
                    if (before) html += '<div class="mb-2 text-secondary" style="font-size: 0.95rem;">' + nl2br(before) + '</div>';

                    html += '<div class="soal-analogi-track">';
                    html += '<div class="soal-analogi-pair">';
                    html += `<div class="soal-analogi-term ${isUnk(term1) ? 'is-unknown' : ''}">${esc(term1)}</div>`;
                    html += '<span class="soal-analogi-sep">:</span>';
                    html += `<div class="soal-analogi-term ${isUnk(term2) ? 'is-unknown' : ''}">${esc(term2)}</div>`;
                    html += '</div>';

                    html += '<span class="soal-analogi-eq">=</span>';

                    html += '<div class="soal-analogi-pair">';
                    html += `<div class="soal-analogi-term ${isUnk(term3) ? 'is-unknown' : ''}">${esc(term3)}</div>`;
                    html += '<span class="soal-analogi-sep">:</span>';
                    html += `<div class="soal-analogi-term ${isUnk(term4) ? 'is-unknown' : ''}">${esc(term4)}</div>`;
                    html += '</div>';
                    html += '</div>';

                    if (after) {
                        html += '<div class="soal-pertanyaan-box mt-3">';
                        html += '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Hubungan Analogi:</div>';
                        html += '<div class="soal-pertanyaan-text">' + nl2br(after) + '</div>';
                        html += '</div>';
                    }
                    html += '</div>';
                    return html;
                }
            }

            // 6. Try SILOGISME (Premis 1 / Premis 2 / Kesimpulan)
            if (/\bPremis\s*(?:1|Mayor|pertama)\b/i.test(text) || /\bPremis\s*2\b/i.test(text)) {
                const sLines = text.split(/\r?\n/);
                const premises = [];
                const introLines = [];
                const conclusionLines = [];
                let collecting = false, done = false;

                for (const rawLine of sLines) {
                    const l = rawLine.trim();
                    if (!l) continue;
                    const pMatch = l.match(/^(?:[-*•]\s*)?Premis\s*([0-9]+|[A-Za-z]+)\s*[:.-]\s*(.+)$/i);
                    if (pMatch) {
                        collecting = true;
                        const label = 'P' + (!isNaN(pMatch[1]) ? pMatch[1] : pMatch[1].charAt(0).toUpperCase());
                        premises.push({ label: label, text: pMatch[2].trim() });
                    } else if (collecting) {
                        if (/^(?:Kesimpulan|Maka|Pertanyaan|Jadi)[^:]*[:.-]?\s*(.*)$/i.test(l)) {
                            done = true;
                            conclusionLines.push(l);
                        } else if (!done) {
                            premises.push({ label: 'P' + (premises.length + 1), text: l });
                        } else {
                            conclusionLines.push(l);
                        }
                    } else {
                        introLines.push(l);
                    }
                }

                if (premises.length >= 2) {
                    const intro = introLines.join('\n').trim();
                    const conclusion = conclusionLines.join('\n').trim();

                    let html = '<div class="soal-silogisme-container my-3">';
                    html += '<div class="soal-silogisme-badge"><i class="fas fa-brain me-1"></i> Silogisme &amp; Logika Formal</div>';
                    if (intro) html += '<div class="mb-2 text-secondary" style="font-size: 0.95rem;">' + nl2br(intro) + '</div>';

                    html += '<div class="soal-premis-list">';
                    premises.forEach(p => {
                        html += '<div class="soal-premis-card">';
                        html += `<div class="soal-premis-num">${esc(p.label)}</div>`;
                        html += `<div class="soal-premis-text">${esc(p.text)}</div>`;
                        html += '</div>';
                    });
                    html += '</div>';

                    if (conclusion) {
                        html += '<div class="soal-silogisme-conclusion">';
                        html += '<div class="soal-silogisme-conclusion-icon"><i class="fas fa-circle-question"></i></div>';
                        html += '<div>';
                        html += '<div class="soal-comp-label" style="margin-bottom: 2px;">Tarik Kesimpulan Logis:</div>';
                        html += `<div class="soal-premis-text fw-bold">${nl2br(conclusion)}</div>`;
                        html += '</div></div>';
                    }
                    html += '</div>';
                    return html;
                }
            }
            
            // 7. Try SOAL CERITA / STUDI KASUS (Narrative + Core Question Stem)
            if (text.length >= 80 || ['TKP', 'TWK'].includes(kategori)) {
                const stemPatterns = [
                    /\b(?:Berdasarkan|Menurut|Dari|Sesuai)\s+(?:kasus|situasi|uraian|peristiwa|kondisi|wacana|bacaan|teks|pasal|ketentuan|deskripsi|simulasi|skenario)\s+[^.?!]+[,.]\s*(?:tindakan|sikap|langkah|keputusan|peran|upaya|manakah|apa|bagaimana|hal\s+apa)[^.?!]*[?:]?/i,
                    /\b(?:Sikap|Tindakan|Langkah|Keputusan|Upaya|Respon|Perilaku)\s+(?:yang\s+)?(?:paling\s+)?(?:tepat|bijak|efektif|seharusnya|dapat|mencerminkan|harus|diambil|Anda\s+lakukan)[^.?!]*[?:]?/i,
                    /\b(?:Berikut\s+ini|Di\s+bawah\s+ini)\s+.*?[,:]\s*(?:KECUALI)?.*$/i,
                    /\b(?:Berapakah|Berapa|Manakah|Apakah|Bagaimana|Mengapa|Siapakah|Apa\s+yang)\s+[^.?!]*\?$/i,
                    /([^.?!;\n]+\?\s*$)/
                ];
                
                let splitPos = -1;
                for (const pat of stemPatterns) {
                    const matches = [...text.matchAll(new RegExp(pat.source, pat.flags + (pat.global ? '' : 'g')))];
                    if (matches.length > 0) {
                        const last = matches[matches.length - 1];
                        if (last.index >= 45) {
                            splitPos = last.index;
                            break;
                        }
                    }
                }
                
                if (splitPos !== -1 || text.length >= 130) {
                    let scenario = splitPos !== -1 ? text.substring(0, splitPos).trim() : text;
                    let stem = splitPos !== -1 ? text.substring(splitPos).trim() : '';
                    
                    let badgeLabel = 'Studi Kasus &amp; Skenario';
                    let badgeIcon = 'fa-book-open';
                    if (kategori === 'TKP') {
                        badgeLabel = 'Situasi Kerja &amp; Dilema Etika';
                        badgeIcon = 'fa-user-tie';
                    } else if (kategori === 'TWK') {
                        badgeLabel = 'Studi Kasus Kebangsaan';
                        badgeIcon = 'fa-landmark';
                    } else if (kategori === 'TIU') {
                        badgeLabel = 'Konteks Soal Cerita';
                        badgeIcon = 'fa-file-lines';
                    }
                    
                    let html = '<div class="soal-cerita-container my-2">';
                    html += '<div class="soal-cerita-card">';
                    html += '<div class="soal-cerita-header">';
                    html += `<span class="soal-cerita-badge"><i class="fas ${badgeIcon} me-1"></i> ${badgeLabel}</span>`;
                    html += '</div>';
                    html += '<div class="soal-cerita-body">' + nl2br(scenario) + '</div>';
                    html += '</div>';
                    
                    if (stem) {
                        html += '<div class="soal-pertanyaan-box mt-3">';
                        html += '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Inti Pertanyaan:</div>';
                        html += '<div class="soal-pertanyaan-text">' + nl2br(stem) + '</div>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    return html;
                }
            }
            
            // 5. Standard Plain Text
            return '<div class="soal-plain-content">' + nl2br(text) + '</div>';
        },

        get availableCategories() {
            const cats = [];
            this.soals.forEach(s => {
                if (s.kategori && !cats.includes(s.kategori)) {
                    cats.push(s.kategori);
                }
            });
            return cats;
        },

        get filteredSoals() {
            return this.soals
                .map((s, i) => ({ ...s, _originalIndex: i }))
                .filter(s => {
                    if (this.activeCategoryFilter === 'ALL') return true;
                    return s.kategori === this.activeCategoryFilter;
                });
        },

        init() {
            const lsKey = 'cat_exam_{{ $ujian->id }}';
            const savedData = localStorage.getItem(lsKey);
            if (savedData) {
                try {
                    const parsed = JSON.parse(savedData);
                    if (parsed.timeLeft && parsed.jawabanUser) {
                        if (parsed.timeLeft < this.timeLeft) {
                            this.timeLeft = parsed.timeLeft;
                        }
                        this.jawabanUser = { ...this.jawabanUser, ...parsed.jawabanUser };
                    }
                } catch(e) {}
            }

            this.startTimer();
            if (this.currentSoal && this.currentSoal.id) {
                this.markAsVisited(this.currentSoal.id);
            }
        },

        countAnswered() {
            return Object.keys(this.jawabanUser).length;
        },

        countSkipped() {
            return this.visitedSoals.filter(id => !this.jawabanUser[id] && !this.raguSoals.includes(id)).length;
        },

        showToast(msg, type='warning') {
            const t = document.createElement('div');
            t.style.position = 'fixed';
            t.style.bottom = '24px';
            t.style.left = '50%';
            t.style.transform = 'translateX(-50%)';
            t.style.background = type === 'warning' ? '#D97706' : '#059669';
            t.style.color = '#FFFFFF';
            t.style.padding = '12px 24px';
            t.style.borderRadius = '999px';
            t.style.zIndex = '999999';
            t.style.fontWeight = '700';
            t.style.fontSize = '0.88rem';
            t.style.boxShadow = '0 10px 25px rgba(0,0,0,0.35)';
            t.style.transition = 'opacity 0.3s ease';
            t.innerHTML = '<i class="fas fa-circle-check me-2"></i>' + msg;
            document.body.appendChild(t);
            setTimeout(() => { 
                t.style.opacity = '0'; 
                setTimeout(() => t.remove(), 350); 
            }, 3000);
        },

        startTimer() {
            setInterval(() => {
                if (this.timeLeft > 0) {
                    this.timeLeft--;
                    let m = Math.floor(this.timeLeft / 60);
                    let s = this.timeLeft % 60;
                    const el = document.getElementById('timerDisplay');
                    if (el) {
                        el.innerText = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                    }
                    
                    if (this.timeLeft % 3 === 0) {
                        localStorage.setItem('cat_exam_{{ $ujian->id }}', JSON.stringify({
                            timeLeft: this.timeLeft,
                            jawabanUser: this.jawabanUser
                        }));
                    }
                } else {
                    this.confirmFinishNow();
                }
            }, 1000);
        },

        markAsVisited(id) {
            if (id && !this.visitedSoals.includes(id)) {
                this.visitedSoals.push(id);
            }
        },

        triggerFadeAnimation() {
            const card = document.querySelector('.exam-question-card');
            if (card) {
                card.classList.remove('fade-exam-enter');
                void card.offsetWidth;
                card.classList.add('fade-exam-enter');
            }
        },

        goToSoal(idx) {
            if (this.index !== idx && idx >= 0 && idx < this.total) {
                this.index = idx;
                this.markAsVisited(this.currentSoal.id);
                this.triggerFadeAnimation();
                if (window.innerWidth < 768) {
                    this.sidebarOpen = false;
                }
                const mainArea = document.getElementById('examMainArea');
                if (mainArea) mainArea.scrollTop = 0;
            }
        },

        toggleRagu(id) {
            if (this.raguSoals.includes(id)) {
                this.raguSoals = this.raguSoals.filter(i => i !== id);
            } else {
                this.raguSoals.push(id);
            }
        },

        async toggleBookmark(id) {
            const isBookmarked = this.bookmarkedSoals.includes(id);
            try {
                const response = await fetch('{{ url('/bookmark-soal') }}/' + encodeURIComponent(id), {
                    method: isBookmarked ? 'DELETE' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    this.showToast('Gagal memperbarui status favorit.', 'warning');
                    return;
                }

                if (isBookmarked) {
                    this.bookmarkedSoals = this.bookmarkedSoals.filter((item) => item !== id);
                    this.showToast('Soal dihapus dari daftar favorit.', 'success');
                } else {
                    this.bookmarkedSoals.push(id);
                    this.showToast('Soal berhasil ditandai favorit.', 'success');
                }
            } catch (e) {
                this.showToast('Koneksi penyimpanan gagal.', 'warning');
            }
        },

        saveAnswer(id, p) {
            this.jawabanUser[id] = p;
            localStorage.setItem('cat_exam_{{ $ujian->id }}', JSON.stringify({
                timeLeft: this.timeLeft,
                jawabanUser: this.jawabanUser
            }));
            axios.post('{{ route("ujian.simpan", $ujian->id) }}', {
                soal_id: id,
                pilihan: p,
                sisa_waktu: this.timeLeft
            }).catch(() => {});
        },

        next() {
            if (this.index < this.total - 1) {
                this.goToSoal(this.index + 1);
            }
        },

        prev() {
            if (this.index > 0) {
                this.goToSoal(this.index - 1);
            }
        },

        handleKeyboard(e) {
            const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (tag === 'input' || tag === 'textarea') return;

            const key = e.key.toUpperCase();
            if (['A', 'B', 'C', 'D', 'E'].includes(key)) {
                if (this.currentSoal && this.currentSoal.id) {
                    this.saveAnswer(this.currentSoal.id, key);
                }
            } else if (key === 'R') {
                if (this.currentSoal && this.currentSoal.id) {
                    this.toggleRagu(this.currentSoal.id);
                }
            } else if (key === 'S') {
                if (this.currentSoal && this.currentSoal.id) {
                    this.toggleBookmark(this.currentSoal.id);
                }
            } else if (e.key === 'ArrowRight') {
                this.next();
            } else if (e.key === 'ArrowLeft') {
                this.prev();
            }
        },

        openFinishModal() {
            const el = document.getElementById('modalFinishConfirmation');
            if (el && window.bootstrap?.Modal) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            } else {
                if (confirm('Apakah Anda yakin ingin mengakhiri sesi ujian ini? Semua jawaban Anda telah tersimpan.')) {
                    this.confirmFinishNow();
                }
            }
        },

        confirmFinishNow() {
            localStorage.removeItem('cat_exam_{{ $ujian->id }}');
            const btn = document.getElementById('confirmFinishBtn') || document.getElementById('finishBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengunci Jawaban...';
            }
            setTimeout(() => {
                window.location.href = '{{ route("ujian.selesai", $ujian->id) }}';
            }, 120);
        },

        pause() {
            if (confirm('Ingin istirahat sejenak? Seluruh jawaban Anda telah tersimpan otomatis dan Anda dapat melanjutkan dari Dashboard kapan saja.')) {
                window.location.href = '{{ route("ujian.index") }}';
            }
        },

        toggleTheme() {
            const current = document.documentElement.getAttribute('data-bs-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            document.body.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('cat_theme', next); } catch(e) {}
        },

        themeIcon() {
            return (document.documentElement.getAttribute('data-bs-theme') === 'light') ? 'fa-moon' : 'fa-sun';
        }
    }
}
</script>
@endsection
