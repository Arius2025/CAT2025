    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #0f1115;
            --bg-secondary: #161a22;
            --surface: #1c212b;
            --surface-elevated: #242b36;
            --surface-2: var(--surface-elevated);
            --card-bg: var(--surface);
            --card-bg-hover: var(--surface-elevated);
            --card-border: var(--border);
            --card-shadow: 0 2px 6px rgba(0,0,0,0.35);
            --card-text: var(--text-primary);
            --card-muted: var(--text-muted);
            --surface-glass: rgba(28, 33, 43, 0.82);
            --bg-gradient: radial-gradient(circle at top right, rgba(56, 189, 248, 0.10), transparent 28%), linear-gradient(180deg, #0f1115 0%, #161a22 100%);

            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-inverse: #0f1115;

            --border: #334155;
            --divider: #475569;
            --border-strong: var(--divider);

            --accent: #60a5fa;
            --accent-hover: #3b82f6;
            --accent-soft: #38bdf8;
            --accent-text: #0f1115;

            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #38bdf8;

            --input-bg: #161c25;
            --input-border: #334155;
            --input-text: #f8fafc;
            --placeholder: #94a3b8;

            --table-header: #202733;
            --table-row-hover: rgba(96, 165, 250, 0.08);

            --shadow: 0 16px 34px rgba(2, 6, 23, 0.32);
            --shadow-soft: 0 18px 36px rgba(2, 6, 23, 0.22);
            --radius-card: 20px;
            --radius-panel: 24px;
            --blur-strength: 16px;
            --nav-surface: rgba(15, 17, 21, 0.88);
            --nav-border: rgba(71, 85, 105, 0.62);
            --sidebar-bg: rgba(22, 26, 34, 0.96);
            --navbar-bg: rgba(15, 17, 21, 0.88);
            --footer-bg: rgba(15, 17, 21, 0.92);

            --bg-midnight: var(--bg-primary);
            --bg-glass: var(--surface-glass);
            --border-glass: var(--border);
            --accent-cyan: var(--accent-soft);
            --accent-blue: var(--accent);
            --shadow-premium: var(--shadow);
            --card-surface: var(--surface);
        }

        body[data-theme="light"] {
            /* Warm Prestigious Backgrounds (Logo-inspired Alabaster & Ivory) */
            --bg-primary: #f8f9fc;
            --bg-secondary: #f1f4f9;
            --surface: #ffffff;
            --surface-elevated: #ffffff;
            --surface-2: #f8fafc;
            --card-bg: #ffffff;
            --card-bg-hover: #fbfcfe;
            --card-border: rgba(11, 37, 69, 0.08);
            --card-shadow: 0 4px 20px rgba(11, 37, 69, 0.05), 0 1px 3px rgba(11, 37, 69, 0.03);
            --card-text: #0b2545;
            --card-muted: #52637a;
            --surface-glass: rgba(255, 255, 255, 0.90);
            --bg-gradient: radial-gradient(circle at 85% 5%, rgba(197, 155, 39, 0.09), transparent 32%),
                               radial-gradient(circle at 10% 20%, rgba(11, 37, 69, 0.05), transparent 30%),
                               linear-gradient(180deg, #fbfcfe 0%, #f1f4f9 100%);

            /* Prestigious Navy & Rich Neutral Typography */
            --text-primary: #0b2545;      /* Royal Deep Navy from CAT ASN logo */
            --text-secondary: #1e3a5f;    /* Elegant Slate Navy */
            --text-muted: #64748b;        /* Crisp Balanced Muted */
            --text-inverse: #ffffff;

            /* Luxury Gold & Slate Borders */
            --border: rgba(11, 37, 69, 0.09);
            --divider: rgba(11, 37, 69, 0.08);
            --border-strong: rgba(197, 155, 39, 0.28); /* Noble Gold Accent Border */

            /* Brand Accent: Royal Navy Blue with Gold Flourish */
            --accent: #133e87;            /* Prestige Deep Navy Blue */
            --accent-hover: #0b2545;      /* Darker Navy on hover */
            --accent-soft: #c59b27;       /* Noble Gold from KORPRI / ASN ribbon */
            --accent-text: #ffffff;

            /* Status & Highlights */
            --success: #0d9488;           /* Deep Emerald */
            --warning: #c59b27;           /* KORPRI Warm Gold */
            --danger: #e11d48;            /* Crimson */
            --info: #0284c7;              /* Clear Azure */

            /* Controls */
            --input-bg: #ffffff;
            --input-border: rgba(11, 37, 69, 0.14);
            --input-text: #0b2545;
            --placeholder: #94a3b8;

            /* Tables & Surfaces */
            --table-header: #f1f5fa;
            --table-row-hover: rgba(19, 62, 135, 0.035);

            /* Shadows & Floating Navs */
            --shadow: 0 10px 30px rgba(11, 37, 69, 0.06);
            --shadow-soft: 0 16px 40px rgba(11, 37, 69, 0.08);
            --nav-surface: rgba(255, 255, 255, 0.92);
            --nav-border: rgba(197, 155, 39, 0.20); /* Luxurious gold sheen on nav */
            --sidebar-bg: rgba(255, 255, 255, 0.98);
            --navbar-bg: rgba(255, 255, 255, 0.92);
            --footer-bg: rgba(255, 255, 255, 0.95);
        }

        body[data-theme="dark"] {
            --bg-primary: #0f1115;
            --bg-secondary: #161a22;
            --surface: #1c212b;
            --surface-elevated: #242b36;
            --surface-2: var(--surface-elevated);
            --card-bg: var(--surface);
            --card-bg-hover: #242b36;
            --card-border: #334155;
            --card-shadow: 0 2px 6px rgba(0,0,0,0.35);
            --card-text: #f8fafc;
            --card-muted: #94a3b8;
            --surface-glass: rgba(28, 33, 43, 0.82);
            --bg-gradient: radial-gradient(circle at top right, rgba(56, 189, 248, 0.10), transparent 28%), linear-gradient(180deg, #0f1115 0%, #161a22 100%);

            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-inverse: #0f1115;

            --border: #334155;
            --divider: #475569;
            --border-strong: var(--divider);

            --accent: #60a5fa;
            --accent-hover: #3b82f6;
            --accent-soft: #38bdf8;
            --accent-text: #0f1115;

            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #38bdf8;

            --input-bg: #161c25;
            --input-border: #334155;
            --input-text: #f8fafc;
            --placeholder: #94a3b8;

            --table-header: #202733;
            --table-row-hover: rgba(96, 165, 250, 0.08);

            --shadow: 0 16px 34px rgba(2, 6, 23, 0.32);
            --shadow-soft: 0 18px 36px rgba(2, 6, 23, 0.22);
            --nav-surface: rgba(15, 17, 21, 0.88);
            --nav-border: rgba(71, 85, 105, 0.62);
            --sidebar-bg: rgba(22, 26, 34, 0.96);
            --navbar-bg: rgba(15, 17, 21, 0.88);
            --footer-bg: rgba(15, 17, 21, 0.92);
        }

        body[data-theme="soft-blue"] {
            --bg-primary: #ecf4ff;
            --bg-gradient: radial-gradient(circle at top right, rgba(37,99,235,0.10), transparent 30%), linear-gradient(180deg, #f7fbff 0%, #dbeafe 100%);
            --surface: #ffffff;
            --surface-2: #f4f9ff;
            --card-bg: #ffffff;
            --surface-glass: rgba(255, 255, 255, 0.78);
            --text-primary: #10223f;
            --text-secondary: #516b8d;
            --text-muted: #7b8fab;
            --border: rgba(37, 99, 235, 0.10);
            --border-strong: rgba(37, 99, 235, 0.18);
            --accent: #2563eb;
            --accent-soft: #38bdf8;
            --accent-hover: #1d4ed8;
            --shadow: 0 12px 28px rgba(37, 99, 235, 0.10);
            --shadow-soft: 0 20px 42px rgba(37, 99, 235, 0.12);
            --nav-surface: rgba(255,255,255,0.84);
            --nav-border: rgba(37, 99, 235, 0.10);
            --input-bg: rgba(37, 99, 235, 0.03);
            --table-header: rgba(37, 99, 235, 0.05);
            --table-row-hover: rgba(37, 99, 235, 0.03);
            --sidebar-bg: rgba(255,255,255,0.95);
            --navbar-bg: rgba(255,255,255,0.84);
            --footer-bg: rgba(255,255,255,0.88);
        }

        body[data-theme="soft-green"] {
            --bg-primary: #effaf4;
            --bg-gradient: radial-gradient(circle at top right, rgba(22,163,74,0.08), transparent 30%), linear-gradient(180deg, #f7fef9 0%, #dcfce7 100%);
            --surface: #ffffff;
            --surface-2: #f6fff8;
            --card-bg: #ffffff;
            --surface-glass: rgba(255, 255, 255, 0.78);
            --text-primary: #153127;
            --text-secondary: #557469;
            --text-muted: #86a094;
            --border: rgba(22, 163, 74, 0.10);
            --border-strong: rgba(22, 163, 74, 0.18);
            --accent: #16a34a;
            --accent-soft: #34d399;
            --accent-hover: #15803d;
            --shadow: 0 12px 28px rgba(22, 163, 74, 0.08);
            --shadow-soft: 0 20px 42px rgba(22, 163, 74, 0.10);
            --nav-surface: rgba(255,255,255,0.84);
            --nav-border: rgba(22, 163, 74, 0.10);
            --input-bg: rgba(22, 163, 74, 0.03);
            --table-header: rgba(22, 163, 74, 0.05);
            --table-row-hover: rgba(22, 163, 74, 0.03);
            --sidebar-bg: rgba(255,255,255,0.95);
            --navbar-bg: rgba(255,255,255,0.84);
            --footer-bg: rgba(255,255,255,0.88);
        }

        body[data-theme="soft-purple"] {
            --bg-primary: #f7f3ff;
            --bg-gradient: radial-gradient(circle at top right, rgba(139,92,246,0.10), transparent 30%), linear-gradient(180deg, #fbf8ff 0%, #ede9fe 100%);
            --surface: #ffffff;
            --surface-2: #faf5ff;
            --card-bg: #ffffff;
            --surface-glass: rgba(255, 255, 255, 0.80);
            --text-primary: #2e1065;
            --text-secondary: #6d5d91;
            --text-muted: #9a8abf;
            --border: rgba(139, 92, 246, 0.12);
            --border-strong: rgba(139, 92, 246, 0.20);
            --accent: #8b5cf6;
            --accent-soft: #c084fc;
            --accent-hover: #7c3aed;
            --shadow: 0 14px 30px rgba(139, 92, 246, 0.10);
            --shadow-soft: 0 20px 42px rgba(139, 92, 246, 0.10);
            --nav-surface: rgba(255,255,255,0.86);
            --nav-border: rgba(139, 92, 246, 0.12);
            --input-bg: rgba(139, 92, 246, 0.03);
            --table-header: rgba(139, 92, 246, 0.05);
            --table-row-hover: rgba(139, 92, 246, 0.03);
            --sidebar-bg: rgba(255,255,255,0.95);
            --navbar-bg: rgba(255,255,255,0.86);
            --footer-bg: rgba(255,255,255,0.88);
        }

        body[data-theme="apple-glass-light"] {
            --bg-primary: #eef2f7;
            --bg-gradient: radial-gradient(circle at top right, rgba(96,165,250,0.16), transparent 26%), linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            --surface: rgba(255,255,255,0.74);
            --surface-2: rgba(255,255,255,0.82);
            --card-bg: rgba(255,255,255,0.74);
            --surface-glass: rgba(255,255,255,0.55);
            --text-primary: #111827;
            --text-secondary: #5b6473;
            --text-muted: #8b95a5;
            --border: rgba(255,255,255,0.35);
            --border-strong: rgba(148, 163, 184, 0.26);
            --accent: #2563eb;
            --accent-soft: #60a5fa;
            --accent-hover: #1d4ed8;
            --shadow: 0 20px 48px rgba(148, 163, 184, 0.18);
            --shadow-soft: 0 18px 44px rgba(148, 163, 184, 0.14);
            --radius-card: 22px;
            --radius-panel: 26px;
            --blur-strength: 18px;
            --nav-surface: rgba(255,255,255,0.64);
            --nav-border: rgba(255,255,255,0.42);
            --input-bg: rgba(255,255,255,0.42);
            --table-header: rgba(255,255,255,0.40);
            --table-row-hover: rgba(37, 99, 235, 0.03);
            --sidebar-bg: rgba(255,255,255,0.78);
            --navbar-bg: rgba(255,255,255,0.64);
            --footer-bg: rgba(255,255,255,0.68);
        }

        body[data-theme="apple-glass-dark"] {
            --bg-primary: #0a0d14;
            --bg-gradient: radial-gradient(circle at top right, rgba(103,232,249,0.10), transparent 26%), linear-gradient(180deg, #0a0d14 0%, #10141d 100%);
            --surface: rgba(22,24,32,0.80);
            --surface-2: rgba(28,31,41,0.86);
            --card-bg: rgba(22,24,32,0.80);
            --surface-glass: rgba(20,20,24,0.55);
            --text-primary: #f8fafc;
            --text-secondary: rgba(241,245,249,0.72);
            --text-muted: rgba(241,245,249,0.50);
            --border: rgba(255,255,255,0.12);
            --border-strong: rgba(255,255,255,0.18);
            --accent: #60a5fa;
            --accent-soft: #67e8f9;
            --accent-hover: #93c5fd;
            --shadow: 0 20px 48px rgba(0, 0, 0, 0.34);
            --shadow-soft: 0 20px 42px rgba(2, 6, 23, 0.30);
            --radius-card: 22px;
            --radius-panel: 26px;
            --blur-strength: 18px;
            --nav-surface: rgba(17,19,26,0.70);
            --nav-border: rgba(255,255,255,0.12);
            --input-bg: rgba(255,255,255,0.05);
            --table-header: rgba(255,255,255,0.06);
            --table-row-hover: rgba(255,255,255,0.03);
            --sidebar-bg: rgba(17,19,26,0.84);
            --navbar-bg: rgba(17,19,26,0.70);
            --footer-bg: rgba(17,19,26,0.72);
        }

        body[data-theme="one-ui-light"] {
            --bg-primary: #f6f7fb;
            --bg-gradient: linear-gradient(180deg, #f6f7fb 0%, #eef1f6 100%);
            --surface: #ffffff;
            --surface-2: #fbfcff;
            --card-bg: #ffffff;
            --surface-glass: rgba(255,255,255,0.92);
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border: rgba(17,24,39,0.08);
            --border-strong: rgba(17,24,39,0.12);
            --accent: #2563eb;
            --accent-soft: #93c5fd;
            --accent-hover: #1d4ed8;
            --shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            --shadow-soft: 0 16px 32px rgba(15, 23, 42, 0.08);
            --radius-card: 18px;
            --radius-panel: 22px;
            --blur-strength: 8px;
            --nav-surface: rgba(255,255,255,0.94);
            --nav-border: rgba(17,24,39,0.08);
            --input-bg: #ffffff;
            --table-header: #f8fafc;
            --table-row-hover: rgba(37, 99, 235, 0.03);
            --sidebar-bg: #ffffff;
            --navbar-bg: rgba(255,255,255,0.94);
            --footer-bg: rgba(255,255,255,0.96);
        }

        body[data-theme="one-ui-dark"] {
            --bg-primary: #121316;
            --bg-gradient: linear-gradient(180deg, #121316 0%, #181b20 100%);
            --surface: #1b1d22;
            --surface-2: #20242a;
            --card-bg: #1b1d22;
            --surface-glass: rgba(27,29,34,0.92);
            --text-primary: #f3f4f6;
            --text-secondary: #b6bcc8;
            --text-muted: #8f96a3;
            --border: rgba(255,255,255,0.08);
            --border-strong: rgba(255,255,255,0.12);
            --accent: #60a5fa;
            --accent-soft: #93c5fd;
            --accent-hover: #bfdbfe;
            --shadow: 0 16px 34px rgba(0, 0, 0, 0.30);
            --shadow-soft: 0 18px 32px rgba(0, 0, 0, 0.24);
            --radius-card: 18px;
            --radius-panel: 22px;
            --blur-strength: 8px;
            --nav-surface: rgba(27,29,34,0.94);
            --nav-border: rgba(255,255,255,0.08);
            --input-bg: #20242a;
            --table-header: #20242a;
            --table-row-hover: rgba(255,255,255,0.03);
            --sidebar-bg: #1b1d22;
            --navbar-bg: rgba(27,29,34,0.94);
            --footer-bg: rgba(27,29,34,0.96);
        }

        body[data-theme="nothing-os"] {
            --bg-primary: #111111;
            --bg-gradient: radial-gradient(circle at top right, rgba(255,255,255,0.04), transparent 26%), linear-gradient(180deg, #111111 0%, #171717 100%);
            --surface: #181818;
            --surface-2: #202020;
            --card-bg: #181818;
            --surface-glass: rgba(24,24,24,0.94);
            --text-primary: #fafafa;
            --text-secondary: #cfcfcf;
            --text-muted: #9a9a9a;
            --border: rgba(255,255,255,0.08);
            --border-strong: rgba(255,255,255,0.14);
            --accent: #ffffff;
            --accent-soft: #d4d4d4;
            --accent-hover: #f5f5f5;
            --success: #d9f99d;
            --warning: #facc15;
            --danger: #f87171;
            --shadow: 0 14px 28px rgba(0,0,0,0.32);
            --shadow-soft: 0 18px 36px rgba(0,0,0,0.24);
            --radius-card: 18px;
            --radius-panel: 20px;
            --blur-strength: 6px;
            --nav-surface: rgba(17,17,17,0.96);
            --nav-border: rgba(255,255,255,0.08);
            --input-bg: #1f1f1f;
            --table-header: #1f1f1f;
            --table-row-hover: rgba(255,255,255,0.03);
            --sidebar-bg: #181818;
            --navbar-bg: rgba(17,17,17,0.96);
            --footer-bg: rgba(17,17,17,0.98);
        }

        body[data-theme="pixel-ui"] {
            --bg-primary: #f4f0ff;
            --bg-gradient: radial-gradient(circle at top right, rgba(99,102,241,0.10), transparent 26%), linear-gradient(180deg, #faf7ff 0%, #ede9fe 100%);
            --surface: #ffffff;
            --surface-2: #f6f3ff;
            --card-bg: #ffffff;
            --surface-glass: rgba(255,255,255,0.90);
            --text-primary: #312e81;
            --text-secondary: #6d68a8;
            --text-muted: #9f9acc;
            --border: rgba(99,102,241,0.12);
            --border-strong: rgba(99,102,241,0.20);
            --accent: #6366f1;
            --accent-soft: #a78bfa;
            --accent-hover: #4f46e5;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 14px 30px rgba(99,102,241,0.10);
            --shadow-soft: 0 20px 38px rgba(99,102,241,0.10);
            --radius-card: 22px;
            --radius-panel: 24px;
            --blur-strength: 8px;
            --nav-surface: rgba(255,255,255,0.92);
            --nav-border: rgba(99,102,241,0.12);
            --input-bg: rgba(99,102,241,0.03);
            --table-header: rgba(99,102,241,0.05);
            --table-row-hover: rgba(99,102,241,0.03);
            --sidebar-bg: rgba(255,255,255,0.94);
            --navbar-bg: rgba(255,255,255,0.92);
            --footer-bg: rgba(255,255,255,0.94);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-gradient);
            background-color: var(--bg-midnight);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* App-like behavior: reduce unintended zoom gestures (pinch zoom is also limited by viewport meta). */
        body { touch-action: pan-x pan-y; }

        body[data-theme] {
            --surface-elevated: var(--surface-2);
            --divider: var(--border-strong);
            --accent-text: var(--accent-text, #ffffff);
            --info: var(--info, var(--accent-soft));
            --input-border: var(--input-border, var(--border-glass));
            --input-text: var(--input-text, var(--text-primary));
            --placeholder: var(--placeholder, var(--text-muted));
            --card-bg: var(--card-bg, var(--surface));
            --card-bg-hover: var(--card-bg-hover, var(--surface-elevated));
            --card-border: var(--card-border, var(--border-glass));
            --card-shadow: var(--card-shadow, var(--shadow-soft));
            --card-text: var(--card-text, var(--text-primary));
            --card-muted: var(--card-muted, var(--text-muted));
        }

        /* Typography helpers used across pages */
        .fw-800 { font-weight: 800 !important; }
        .fw-700 { font-weight: 700 !important; }

        /* Neutral text helpers aligned with theme variables */
        .apple-muted { color: var(--text-secondary) !important; }
        .apple-kicker {
            color: var(--text-secondary) !important;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .apple-title { font-weight: 800 !important; line-height: 1.05; }
        .apple-subtitle { color: var(--text-secondary) !important; opacity: 0.85; }
        .apple-label { color: var(--text-secondary) !important; font-size: 0.78rem; font-weight: 700; }
        .apple-hint { color: var(--text-secondary) !important; opacity: 0.75; font-size: 0.85rem; }
        .apple-alert { color: var(--text-primary) !important; border-radius: 16px; }
        .apple-summary { cursor: pointer; }
        .page-shell { padding-top: 1.5rem; padding-bottom: 2rem; }
        .page-hero {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            padding: 28px;
            border: 1px solid var(--border-glass);
            background:
                radial-gradient(circle at top left, rgba(34,211,238,0.14), transparent 34%),
                radial-gradient(circle at top right, rgba(59,130,246,0.14), transparent 34%),
                linear-gradient(135deg, rgba(30,41,59,0.50), rgba(15,23,42,0.50));
            box-shadow: var(--shadow-premium);
        }
        [data-bs-theme="light"] .page-hero {
            background:
                radial-gradient(circle at 90% 10%, rgba(197, 155, 39, 0.12), transparent 38%),
                radial-gradient(circle at 10% 80%, rgba(11, 37, 69, 0.05), transparent 35%),
                linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.90));
            border-color: rgba(197, 155, 39, 0.22);
            box-shadow: 0 12px 36px rgba(11, 37, 69, 0.06);
        }
        .page-hero__row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
        }
        .page-hero__copy { max-width: 760px; }
        .page-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .page-section {
            margin-bottom: 1.5rem;
        }
        .page-section__head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 1rem;
        }
        .page-stats-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 14px;
        }
        .page-stat-card {
            grid-column: span 3;
            min-height: 100%;
        }
        .page-stat-card--wide { grid-column: span 6; }
        .page-empty {
            padding: 20px;
            border-radius: 18px;
            border: 1px dashed var(--card-border);
            color: var(--card-muted);
            background: var(--card-bg);
            box-shadow: var(--card-shadow);
        }
        .apple-alert-inline {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
        }
        .card-theme,
        .card-theme-hover,
        .card,
        .modal-content,
        .dropdown-menu,
        .offcanvas:not(.mobile-sheet),
        .list-group-item,
        .accordion-item {
            background: var(--card-bg);
            color: var(--card-text);
            border-color: var(--card-border);
            box-shadow: var(--card-shadow);
        }
        .card-theme-hover:hover {
            background: var(--card-bg-hover);
        }
        .card-header-theme,
        .card-body-theme,
        .card-footer-theme {
            background: transparent;
            color: var(--card-text);
        }
        .card,
        .modal-content,
        .dropdown-menu,
        .offcanvas:not(.mobile-sheet),
        .list-group-item,
        .accordion-item {
            color: var(--card-text);
        }
        .card-title,
        .modal-title,
        .dropdown-header,
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
        }
        p,
        li,
        dt,
        dd,
        span,
        div {
            border-color: inherit;
        }
        a {
            color: var(--accent);
        }
        a:hover {
            color: var(--accent-hover);
        }
        .modal-header,
        .modal-footer,
        .card-header,
        .card-footer,
        .accordion-button,
        .list-group-item {
            border-color: var(--divider);
        }
        .card-header,
        .card-footer,
        .modal-header,
        .modal-footer {
            background: transparent;
            color: var(--card-text);
        }
        .accordion-button,
        .accordion-button:not(.collapsed) {
            background: var(--surface-2);
            color: var(--text-primary);
        }
        .accordion-button::after {
            filter: var(--accordion-icon-filter, none);
        }
        body[data-bs-theme="dark"] {
            --accordion-icon-filter: invert(1) brightness(1.8);
        }
        .form-control,
        .form-select,
        textarea,
        input:not([type="checkbox"]):not([type="radio"]) {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--input-border);
        }
        .form-control:focus,
        .form-select:focus,
        textarea:focus,
        input:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: color-mix(in srgb, var(--accent) 72%, transparent);
            box-shadow: 0 0 0 .18rem color-mix(in srgb, var(--accent) 20%, transparent);
        }
        .form-control::placeholder,
        textarea::placeholder {
            color: var(--placeholder);
            opacity: 1;
        }
        .form-select option,
        .form-select optgroup,
        select option,
        select optgroup {
            color: var(--input-text) !important;
            background: var(--surface-elevated) !important;
        }
        .form-label,
        label,
        .col-form-label {
            color: var(--text-secondary);
        }
        .form-check-input {
            background-color: var(--input-bg);
            border-color: var(--input-border);
        }
        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-primary);
            --bs-table-border-color: var(--border-glass);
            --bs-table-striped-bg: transparent;
            --bs-table-hover-bg: var(--table-row-hover);
            color: var(--text-primary);
        }
        .table thead th {
            background: var(--table-header);
            color: var(--text-secondary);
            border-color: var(--divider);
        }
        .table tbody td,
        .table tbody th {
            color: var(--text-primary);
            border-color: var(--divider);
        }
        .table tbody tr:hover > * {
            color: var(--text-primary);
        }
        .pagination .page-link {
            background: var(--surface-elevated);
            color: var(--text-primary);
            border-color: var(--divider);
            box-shadow: none;
        }
        .pagination .page-item.active .page-link {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--accent-text);
        }
        .pagination .page-item.disabled .page-link {
            background: var(--surface-elevated);
            color: var(--text-muted);
            border-color: var(--divider);
        }
        .alert {
            border-radius: 18px;
            border-color: var(--divider);
            color: var(--text-primary);
        }
        .alert-success {
            background: color-mix(in srgb, var(--success) 14%, var(--surface));
            color: var(--text-primary);
        }
        .alert-warning {
            background: color-mix(in srgb, var(--warning) 14%, var(--surface));
            color: var(--text-primary);
        }
        .alert-danger {
            background: color-mix(in srgb, var(--danger) 14%, var(--surface));
            color: var(--text-primary);
        }
        .alert-info,
        .bg-info-subtle {
            background: color-mix(in srgb, var(--info) 14%, var(--surface));
            color: var(--text-primary);
        }
        .text-muted,
        small,
        .small {
            color: var(--text-muted) !important;
        }
        .text-secondary {
            color: var(--text-secondary) !important;
        }
        .text-primary {
            color: var(--text-primary) !important;
        }
        .text-success {
            color: var(--success) !important;
        }
        .text-warning {
            color: var(--warning) !important;
        }
        .text-danger {
            color: var(--danger) !important;
        }
        .text-info {
            color: var(--info) !important;
        }
        .bg-white,
        .bg-light,
        .bg-body {
            background-color: var(--surface) !important;
            color: var(--text-primary) !important;
        }
        .bg-dark,
        .bg-black {
            background-color: var(--bg-secondary) !important;
            color: var(--text-primary) !important;
        }
        .btn-outline-secondary,
        .btn-outline-light,
        .btn-outline-dark {
            color: var(--text-primary);
            border-color: var(--input-border);
        }
        .btn-primary,
        .btn-info {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--accent-text);
        }
        .btn-primary:hover,
        .btn-info:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
            color: var(--accent-text);
        }
        .btn-secondary {
            background: var(--surface-elevated);
            border-color: var(--input-border);
            color: var(--text-primary);
        }
        .btn-secondary:hover {
            background: color-mix(in srgb, var(--surface-elevated) 82%, var(--accent) 18%);
            border-color: var(--divider);
            color: var(--text-primary);
        }
        .btn-success {
            background: var(--success);
            border-color: var(--success);
            color: #ffffff;
        }
        .btn-warning {
            background: var(--warning);
            border-color: var(--warning);
            color: #ffffff;
        }
        .btn-danger {
            background: var(--danger);
            border-color: var(--danger);
            color: #ffffff;
        }
        .badge {
            color: inherit;
        }
        .bg-primary {
            background-color: var(--accent) !important;
            color: var(--accent-text) !important;
        }
        .bg-success {
            background-color: var(--success) !important;
            color: #ffffff !important;
        }
        .bg-warning {
            background-color: var(--warning) !important;
            color: #ffffff !important;
        }
        .bg-danger {
            background-color: var(--danger) !important;
            color: #ffffff !important;
        }
        .bg-secondary {
            background-color: var(--surface-elevated) !important;
            color: var(--text-primary) !important;
        }
        .btn-outline-secondary:hover,
        .btn-outline-light:hover,
        .btn-outline-dark:hover {
            background: var(--surface-2);
            color: var(--text-primary);
            border-color: var(--border-strong);
        }
        .dropdown-item {
            color: var(--text-primary);
        }
        .dropdown-item:hover,
        .dropdown-item:focus {
            background: var(--surface-2);
            color: var(--text-primary);
        }
        .dropdown-divider {
            border-color: var(--divider);
        }
        .border,
        .border-top,
        .border-bottom,
        .border-start,
        .border-end {
            border-color: var(--divider) !important;
        }
        .apple-alert-inline i { margin-top: 2px; }
        .apple-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .apple-pagination__pages {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
        }
        .apple-pagination__page {
            min-width: 44px;
            padding-inline: 14px;
        }
        .apple-pagination__ellipsis {
            color: var(--text-secondary);
            padding: 0 4px;
            align-self: center;
        }
        .apple-glass-btn.is-disabled {
            opacity: 0.45;
            pointer-events: none;
        }
        .apple-action-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .apple-skeleton {
            position: relative;
            overflow: hidden;
        }
        .apple-skeleton::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.10), transparent);
            transform: translateX(-100%);
            animation: skeletonSweep 1.4s ease-in-out infinite;
            pointer-events: none;
        }
        .apple-skeleton-line {
            display: block;
            height: 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
        }
        .apple-skeleton-line + .apple-skeleton-line {
            margin-top: 8px;
        }
        .apple-skeleton-line.lg { height: 16px; }
        .apple-skeleton-line.w-80 { width: 80%; }
        .apple-skeleton-line.w-60 { width: 60%; }
        .apple-skeleton-line.w-40 { width: 40%; }
        .apple-skeleton-card {
            border-radius: 18px;
            border: 1px solid var(--card-border);
            padding: 16px;
            background: var(--card-bg);
            box-shadow: var(--card-shadow);
        }
        .apple-skeleton-table {
            border-radius: 18px;
            border: 1px solid var(--card-border);
            padding: 14px;
            background: var(--card-bg);
            box-shadow: var(--card-shadow);
        }
        .apple-skeleton-table__row {
            display: grid;
            grid-template-columns: 0.7fr 2fr 1fr 1fr;
            gap: 12px;
            padding: 10px 0;
        }
        .apple-skeleton-table__row + .apple-skeleton-table__row {
            border-top: 1px solid var(--border-glass);
        }
        .is-loading-panel {
            position: relative;
            overflow: hidden;
        }
        .is-loading-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            transform: translateX(-100%);
            animation: skeletonSweep 1.2s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes skeletonSweep {
            100% { transform: translateX(100%); }
        }
        /* Fluid Top Progress Bar (Replaces blocking modal loader) */
        .cat-progress-track {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3.5px;
            z-index: 999999;
            pointer-events: none;
            opacity: 0;
            background: transparent;
            transition: opacity 0.22s ease;
        }
        .cat-progress-track.is-visible {
            opacity: 1;
        }
        .cat-progress-indicator {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #C59B27 0%, #D97706 40%, #2563EB 75%, #10B981 100%);
            box-shadow: 0 0 12px rgba(197, 155, 39, 0.75), 0 0 4px rgba(37, 99, 235, 0.5);
            transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 0 999px 999px 0;
        }
        .cat-progress-track.is-visible .cat-progress-indicator {
            width: 78%;
            animation: catProgressShimmer 1.6s ease infinite;
        }
        @keyframes catProgressShimmer {
            0% { filter: brightness(1); }
            50% { filter: brightness(1.3); }
            100% { filter: brightness(1); }
        }

        /* Fallback legacy modal loader (hidden / non-blocking) */
        .page-loader {
            display: none !important;
            pointer-events: none !important;
        }

        /* Fluid Page Entrance */
        .page-enter {
            animation: pageFadeIn 0.24s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 991px) {
            .page-stat-card,
            .page-stat-card--wide {
                grid-column: span 6;
            }
        }
        @media (max-width: 767px) {
            .page-hero { padding: 22px 18px; border-radius: 22px; }
            .page-stat-card,
            .page-stat-card--wide {
                grid-column: span 12;
            }
            .page-shell {
                padding-top: 1rem;
                padding-bottom: 1.5rem;
            }
            .page-toolbar,
            .apple-action-stack {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .page-toolbar > *,
            .apple-action-stack > * {
                width: 100%;
                justify-content: center;
            }
            .page-section {
                margin-bottom: 1.1rem;
            }
            .page-empty {
                padding: 18px 16px;
                border-radius: 16px;
            }
            .apple-glass-card {
                border-radius: 18px;
            }
        }

        /* =========================
           Apple Glass Utilities
           ========================= */
        .apple-glass {
            border: 1px solid var(--border-glass);
            background: var(--bg-glass);
            backdrop-filter: blur(var(--blur-strength)) saturate(175%);
            -webkit-backdrop-filter: blur(var(--blur-strength)) saturate(175%);
            box-shadow: var(--shadow-premium);
        }

        .apple-glass-card {
            border-radius: var(--radius-card);
            background: var(--card-bg) !important;
            color: var(--card-text) !important;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
        }

        .apple-glass-card.apple-glass {
            overflow: hidden;
            background: var(--card-bg) !important;
            color: var(--card-text) !important;
            border-color: var(--card-border) !important;
            box-shadow: var(--card-shadow) !important;
        }

        .page-stat-card > .apple-glass-card,
        .analysis-mode-card,
        .coach-card,
        .coach-panel,
        .result-card,
        .pembahasan-card,
        .breakdown-item,
        .result-answer-panel,
        .result-image-panel,
        .result-explain-panel,
        .result-key-panel,
        .table-card,
        .card-soal {
            background: var(--card-bg) !important;
            color: var(--card-text) !important;
            border-color: var(--card-border) !important;
            box-shadow: var(--card-shadow) !important;
        }
        .page-stat-card > .apple-glass-card:hover,
        .analysis-mode-card:hover,
        .coach-card:hover,
        .coach-panel:hover,
        .table-card:hover {
            background: var(--card-bg-hover) !important;
        }

        .apple-glass-soft {
            border: 1px solid var(--border-glass);
            background: var(--surface-2);
        }
        [data-bs-theme="light"] .apple-glass-soft {
            background: var(--surface-2);
        }

        .apple-glass-input {
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            color: var(--input-text) !important;
            border-radius: 14px !important;
            padding: 12px 14px !important;
        }
        [data-bs-theme="light"] .apple-glass-input {
            background: var(--input-bg) !important;
        }
        .apple-glass-input:focus {
            box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--accent) 20%, transparent) !important;
            border-color: color-mix(in srgb, var(--accent) 70%, transparent) !important;
        }

        /* Help native select dropdowns be readable across themes */
        select.apple-glass-input option,
        select.apple-glass-input optgroup {
            color: var(--input-text) !important;
            background: var(--surface-elevated) !important;
        }

        .apple-glass-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            max-width: 100%;
            border-radius: 16px;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: 0.005em;
            line-height: 1.2;
            text-align: center;
            white-space: nowrap;
            border: 1px solid var(--border-glass);
            background: rgba(255,255,255,0.04);
            color: var(--text-primary);
            padding: 11px 16px;
            text-decoration: none;
            transition: transform 0.18s ease, filter 0.18s ease, background 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }
        /* In light mode, keep glass buttons readable without overriding primary/danger gradients. */
        [data-bs-theme="light"] .apple-glass-btn:not(.apple-btn-primary):not(.apple-btn-danger) {
            background: rgba(2,6,23,0.02);
        }
        .apple-glass-btn:hover { transform: translateY(-1px); filter: brightness(1.03); text-decoration: none; }
        .apple-glass-btn:active { transform: translateY(0); filter: brightness(0.98); }

        .apple-btn-primary,
        .apple-glass-btn.apple-btn-primary,
        [data-bs-theme="light"] .apple-glass-btn.apple-btn-primary {
            border: 0;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.18);
            color: var(--accent-text);
        }

        .apple-btn-danger,
        .apple-glass-btn.apple-btn-danger,
        [data-bs-theme="light"] .apple-glass-btn.apple-btn-danger {
            border: 0;
            background: linear-gradient(135deg, var(--danger), color-mix(in srgb, var(--danger) 70%, #7f1d1d));
            box-shadow: 0 10px 26px rgba(239, 68, 68, 0.18);
            color: #ffffff;
        }

        .apple-btn-outline,
        .apple-glass-btn.apple-btn-outline {
            background: transparent;
            border: 1px solid var(--border-glass);
            color: var(--text-primary);
        }
        .apple-btn-ghost,
        .apple-glass-btn.apple-btn-ghost {
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--text-primary);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.03);
        }
        [data-bs-theme="light"] .apple-btn-ghost,
        [data-bs-theme="light"] .apple-glass-btn.apple-btn-ghost {
            background: rgba(2,6,23,0.02);
            border-color: rgba(15,23,42,0.08);
        }

        .apple-action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
        }
        .apple-nav-tile {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-height: 92px;
            padding: 16px 18px;
            border-radius: 22px;
            border: 1px solid var(--border-glass);
            background: linear-gradient(180deg, rgba(255,255,255,0.045), rgba(255,255,255,0.02));
            color: var(--text-primary);
            text-decoration: none;
            transition: transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
        }
        .apple-nav-tile:hover {
            transform: translateY(-2px);
            color: var(--text-primary);
            text-decoration: none;
            border-color: rgba(59,130,246,0.24);
            background: linear-gradient(180deg, rgba(34,211,238,0.10), rgba(59,130,246,0.08));
            box-shadow: 0 18px 36px rgba(15,23,42,0.14);
        }
        .apple-nav-tile__icon {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: inline-grid;
            place-items: center;
            border-radius: 14px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.08);
            font-size: 1rem;
        }
        .apple-nav-tile__body {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .apple-nav-tile__title {
            font-weight: 800;
            line-height: 1.15;
        }
        .apple-nav-tile__hint {
            color: var(--text-secondary);
            font-size: 0.83rem;
            line-height: 1.45;
        }
        [data-bs-theme="light"] .apple-nav-tile {
            background: linear-gradient(180deg, rgba(255,255,255,0.82), rgba(248,250,252,0.74));
            border-color: rgba(15,23,42,0.08);
            box-shadow: 0 14px 28px rgba(15,23,42,0.05);
        }
        [data-bs-theme="light"] .apple-nav-tile__icon {
            background: rgba(2,6,23,0.03);
            border-color: rgba(15,23,42,0.08);
        }

        .apple-glass-table-wrap {
            border-radius: 16px;
            overflow: auto;
            border: 1px solid var(--border-glass);
            background: rgba(255,255,255,0.02);
        }
        [data-bs-theme="light"] .apple-glass-table-wrap { background: rgba(2,6,23,0.015); }

        table.apple-glass-table {
            color: var(--text-primary);
        }
        table.apple-glass-table td,
        table.apple-glass-table th {
            border-color: var(--border-glass) !important;
            vertical-align: middle;
        }
        table.apple-glass-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-glass) !important;
        }
        [data-bs-theme="light"] table.apple-glass-table thead th {
            background: rgba(255,255,255,0.85);
        }

        /* Final card surface enforcement so theme switching reaches all card-like containers,
           including components that also use apple-glass or legacy panel classes. */
        .card-theme,
        .card-theme-hover,
        .card-theme-hover:hover,
        .page-stat-card > .apple-glass-card,
        .analysis-mode-card,
        .coach-card,
        .coach-panel,
        .result-card,
        .pembahasan-card,
        .breakdown-item,
        .result-answer-panel,
        .result-image-panel,
        .result-explain-panel,
        .result-key-panel,
        .table-card,
        .card-soal,
        .apple-skeleton-card,
        .apple-skeleton-table,
        .page-empty {
            background: var(--card-bg) !important;
            color: var(--card-text) !important;
            border-color: var(--card-border) !important;
            box-shadow: var(--card-shadow) !important;
        }
        .card-theme-hover:hover,
        .page-stat-card > .apple-glass-card:hover,
        .analysis-mode-card:hover,
        .coach-card:hover,
        .coach-panel:hover,
        .table-card:hover {
            background: var(--card-bg-hover) !important;
        }

        /* =========================
           Table -> Cards (Mobile)
           ========================= */
        .table-cards-wrap { display: none; }
        .table-card {
            border-radius: 18px;
            padding: 14px 14px;
        }
        .table-card .title {
            font-weight: 900;
            color: var(--text-primary);
            line-height: 1.2;
        }
        .table-card .meta {
            color: var(--text-secondary);
            font-size: 0.86rem;
        }
        .table-card .rowline {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--border-glass);
            margin-top: 10px;
        }
        .table-card .label {
            color: var(--text-secondary);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .table-card .value { color: var(--text-primary); text-align: right; }
        .table-card .value .apple-glass-btn { padding: 10px 14px; }

        /* Chips & pills (mobile cards / ranking) */
        .apple-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid var(--border-glass);
            background: rgba(255,255,255,0.05);
            color: var(--text-primary);
            font-weight: 800;
            line-height: 1;
        }
        [data-bs-theme="light"] .apple-pill { background: rgba(2,6,23,0.03); }
        .apple-pill.muted { color: var(--text-secondary); font-weight: 700; }
        [data-bs-theme="light"] .apple-pill.muted {
            background: rgba(2,6,23,0.05);
            border-color: rgba(15,23,42,0.08);
            color: #475569;
        }
        .apple-pill.success { background: rgba(16,185,129,0.12); border-color: rgba(16,185,129,0.25); color: rgba(236,253,245,0.98); }
        [data-bs-theme="light"] .apple-pill.success { color: rgba(15,23,42,0.92); }
        .apple-pill.warning { background: color-mix(in srgb, var(--warning) 16%, transparent); border-color: color-mix(in srgb, var(--warning) 30%, var(--border)); color: color-mix(in srgb, var(--warning) 72%, white); }
        .apple-pill.info { background: color-mix(in srgb, var(--info) 16%, transparent); border-color: color-mix(in srgb, var(--info) 30%, var(--border)); color: color-mix(in srgb, var(--info) 78%, white); }
        .apple-pill.danger { background: rgba(239,68,68,0.10); border-color: rgba(239,68,68,0.25); color: rgba(254,242,242,0.98); }
        [data-bs-theme="light"] .apple-pill.danger { color: rgba(15,23,42,0.92); }
        [data-bs-theme="light"] .apple-pill.warning,
        [data-bs-theme="light"] .apple-pill.info { color: rgba(15,23,42,0.92); }
        .apple-pill.total {
            border: 0;
            background: linear-gradient(135deg, rgba(34,211,238,0.92), rgba(59,130,246,0.92));
            box-shadow: 0 14px 36px rgba(59,130,246,0.18);
            color: #fff;
        }
        .apple-pill.mode-skd {
            background: rgba(59,130,246,0.12);
            border-color: rgba(59,130,246,0.24);
            color: rgba(239,246,255,0.98);
        }
        .apple-pill.mode-skb {
            background: rgba(34,197,94,0.12);
            border-color: rgba(34,197,94,0.24);
            color: rgba(240,253,244,0.98);
        }
        .apple-pill.mode-pppk {
            background: rgba(245,158,11,0.14);
            border-color: rgba(245,158,11,0.26);
            color: rgba(255,251,235,0.98);
        }
        .apple-pill.mode-farmasi {
            background: linear-gradient(135deg, rgba(16,185,129,0.16), rgba(14,165,233,0.12));
            border-color: rgba(16,185,129,0.24);
            color: rgba(236,253,245,0.98);
        }
        [data-bs-theme="light"] .apple-pill.mode-skd,
        [data-bs-theme="light"] .apple-pill.mode-skb,
        [data-bs-theme="light"] .apple-pill.mode-pppk,
        [data-bs-theme="light"] .apple-pill.mode-farmasi {
            color: rgba(15,23,42,0.92);
        }

        .apple-chip-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .apple-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 999px;
            border: 1px solid var(--border-glass);
            background: rgba(255,255,255,0.04);
            color: var(--text-primary);
            font-weight: 800;
            font-size: 0.86rem;
            line-height: 1;
        }
        .apple-chip .k { letter-spacing: 0.08em; font-weight: 900; opacity: 0.85; }
        .apple-chip.pass { border-color: rgba(16,185,129,0.28); background: rgba(16,185,129,0.10); }
        .apple-chip.fail { border-color: rgba(239,68,68,0.28); background: rgba(239,68,68,0.08); }

        @media (max-width: 768px) {
            .table-cards-ready table.apple-glass-table { display: none !important; }
            .table-cards-ready .table-cards-wrap { display: block; }
        }

        /* Floating Navbar */
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar-floating {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
            width: min(96%, 1380px);
            margin: 14px auto 0;
            padding: 12px 16px;
            margin-top: 12px;
            border-radius: 24px;
            border: 1px solid var(--nav-border);
            background: var(--nav-surface);
            backdrop-filter: blur(calc(var(--blur-strength) + 4px)) saturate(180%);
            -webkit-backdrop-filter: blur(calc(var(--blur-strength) + 4px)) saturate(180%);
            box-shadow:
                0 18px 44px rgba(2, 6, 23, 0.18),
                inset 0 1px 0 rgba(255,255,255,0.06);
            transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease;
        }
        .navbar-floating:hover {
            transform: translateY(-1px);
            box-shadow:
                0 24px 54px rgba(2, 6, 23, 0.22),
                inset 0 1px 0 rgba(255,255,255,0.08);
        }

        .desktop-brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 52px;
            padding: 0 18px;
            border-radius: 18px;
            border: 1px solid color-mix(in srgb, var(--accent) 10%, var(--border));
            background: color-mix(in srgb, var(--surface) 88%, transparent);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
        }

        .desktop-nav-shell {
            min-width: 0;
            overflow: visible;
        }

        .desktop-nav-frame {
            display: flex;
            align-items: center;
            min-height: 56px;
            padding: 6px;
            border-radius: 18px;
            border: 1px solid color-mix(in srgb, var(--accent) 9%, var(--border));
            background: color-mix(in srgb, var(--surface) 84%, transparent);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
        }

        .desktop-dropdown-menu {
            background: var(--card-bg) !important;
            border-radius: 16px !important;
            border: 1px solid var(--card-border) !important;
            min-width: 18rem;
            max-height: min(72vh, 34rem);
            overflow-y: auto;
            box-shadow: var(--card-shadow);
        }

        .desktop-nav-list {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 6px;
            overflow: visible;
            white-space: nowrap;
            padding: 0;
        }

        .nav-link {
            color: var(--text-secondary);
            font-weight: 700;
            font-size: 0.95rem;
            padding: 11px 14px !important;
            border-radius: 14px;
            transition: 0.25s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--accent-blue);
            background: color-mix(in srgb, var(--accent) 11%, transparent);
        }

        /* Override Bootstrap navbar-dark link colors (needed for light theme). */
        .navbar-floating .navbar-nav .nav-link {
            color: var(--text-secondary) !important;
        }
        .navbar-floating .navbar-nav .nav-link:hover,
        .navbar-floating .navbar-nav .nav-link.active {
            color: var(--accent-blue) !important;
        }

        .user-dropdown-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 5px 15px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            min-height: 46px;
        }

        [data-bs-theme="light"] .user-dropdown-btn {
            background: rgba(0,0,0,0.02);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            color: white;
            font-weight: bold;
        }

        .theme-toggle {
            width: 46px; height: 46px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            background: color-mix(in srgb, var(--surface) 88%, transparent);
            border: 1px solid color-mix(in srgb, var(--accent) 9%, var(--border));
            color: var(--text-primary);
            transition: 0.3s;
        }
        .theme-toggle:hover { transform: translateY(-1px) rotate(10deg); }

        .desktop-nav-actions {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .desktop-nav-actions-group {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .desktop-utility-btn,
        .desktop-admin-dropdown .nav-link,
        .desktop-user-dropdown .user-dropdown-btn {
            min-height: 46px;
        }

        .desktop-utility-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 46px;
            padding: 0 14px;
            border-radius: 14px;
            border: 1px solid color-mix(in srgb, var(--accent) 9%, var(--border));
            background: color-mix(in srgb, var(--surface) 88%, transparent);
            color: var(--text-primary);
            transition: transform .22s ease, background .22s ease, border-color .22s ease;
        }

        .desktop-utility-btn:hover {
            transform: translateY(-1px);
            background: color-mix(in srgb, var(--accent) 8%, var(--surface));
            color: var(--text-primary);
        }

        .desktop-utility-btn__label {
            font-size: .9rem;
            font-weight: 700;
            color: var(--text-secondary);
        }

        .desktop-admin-dropdown .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border-glass);
            border-radius: 14px;
            padding: 10px 14px !important;
        }

        .desktop-admin-dropdown .nav-link:hover,
        .desktop-admin-dropdown .nav-link.active {
            background: color-mix(in srgb, var(--surface) 88%, transparent);
        }

        .desktop-user-dropdown .user-dropdown-btn {
            gap: 10px;
            padding-right: 12px;
            background: color-mix(in srgb, var(--surface) 88%, transparent);
            border: 1px solid color-mix(in srgb, var(--accent) 9%, var(--border));
        }

        .desktop-user-name {
            max-width: 144px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 700;
        }

        .desktop-nav-actions .dropdown-menu {
            min-width: 16rem;
        }

        .desktop-search-modal .modal-content {
            border-radius: 24px;
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            color: var(--card-text);
            box-shadow: var(--card-shadow);
        }

        .desktop-search-modal .modal-header,
        .desktop-search-modal .modal-footer {
            border-color: var(--divider);
        }

        .desktop-search-modal .quick-link-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .desktop-search-modal .quick-link-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid var(--card-border);
            background: color-mix(in srgb, var(--surface) 86%, transparent);
            color: var(--text-primary);
            text-decoration: none;
            transition: transform .2s ease, border-color .2s ease, background .2s ease;
        }

        .desktop-search-modal .quick-link-card:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--accent) 26%, var(--card-border));
            background: color-mix(in srgb, var(--accent) 8%, var(--surface));
            color: var(--text-primary);
        }

        .desktop-search-modal .quick-link-card i {
            width: 18px;
            text-align: center;
            color: var(--accent);
        }

        .desktop-topbar {
            position: sticky;
            top: 0;
            z-index: 1035;
            padding: 14px 20px 0;
        }

        .desktop-topbar__inner {
            max-width: 1440px;
            margin: 0 auto;
            min-height: 64px;
            padding: 8px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border: 1px solid var(--nav-border);
            border-radius: 20px;
            background: var(--nav-surface);
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .desktop-topbar__brand {
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }

        .desktop-topbar__brand-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-primary);
        }

        .desktop-topbar__brand-mark {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            padding: 0;
        }

        .desktop-brand-img {
            width: 46px;
            height: 46px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.25));
            transition: transform .18s ease;
        }

        .desktop-brand-img:hover {
            transform: scale(1.06);
        }

        .desktop-topbar__brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .desktop-topbar__brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-primary);
        }

        .desktop-topbar__brand-subtitle {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .desktop-topbar__center {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            justify-content: center;
        }

        .desktop-topbar__menu-shell {
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--border) 92%, transparent);
            background: color-mix(in srgb, var(--surface) 86%, transparent);
            padding: 4px;
        }

        .desktop-topbar__menu {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex-wrap: nowrap;
        }

        .desktop-topbar__menu-link,
        .desktop-topbar__pill-btn,
        .desktop-topbar__icon-btn,
        .desktop-topbar__theme-btn,
        .desktop-topbar__user-btn,
        .desktop-topbar__text-link {
            transition: all .18s ease;
        }

        .desktop-topbar__menu-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            padding: 0 16px;
            border-radius: 16px;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 700;
            white-space: nowrap;
        }

        .desktop-topbar__menu-link i {
            font-size: .92rem;
            color: var(--text-muted);
        }

        .desktop-topbar__menu-link:hover,
        .desktop-topbar__menu-link.is-active {
            background: color-mix(in srgb, var(--accent) 14%, transparent);
            color: var(--text-primary);
        }

        .desktop-topbar__menu-link:hover i,
        .desktop-topbar__menu-link.is-active i {
            color: var(--accent);
        }

        .desktop-topbar__menu-link--button,
        .desktop-topbar__pill-btn,
        .desktop-topbar__icon-btn,
        .desktop-topbar__theme-btn,
        .desktop-topbar__user-btn {
            background: transparent;
        }

        .desktop-topbar__actions {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .desktop-topbar__icon-btn,
        .desktop-topbar__pill-btn,
        .desktop-topbar__user-btn {
            min-height: 48px;
            border-radius: 16px;
            padding: 0 16px;
            color: var(--text-primary);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid color-mix(in srgb, var(--border) 94%, transparent);
            background: color-mix(in srgb, var(--surface-elevated) 88%, transparent);
        }

        .desktop-topbar__theme-btn {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--border) 94%, transparent);
            color: var(--text-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: color-mix(in srgb, var(--surface-elevated) 88%, transparent);
        }

        .desktop-topbar__icon-btn:hover,
        .desktop-topbar__pill-btn:hover,
        .desktop-topbar__user-btn:hover,
        .desktop-topbar__theme-btn:hover,
        .desktop-topbar__pill-btn.is-active {
            background: color-mix(in srgb, var(--accent) 14%, transparent);
            border-color: color-mix(in srgb, var(--accent) 28%, var(--border));
            color: var(--text-primary);
        }

        .desktop-topbar__user-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            min-width: 0;
            line-height: 1.05;
        }

        .desktop-topbar__user-label {
            font-size: .66rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-muted);
        }

        .desktop-topbar__user-name {
            max-width: 148px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 700;
            color: var(--text-primary);
        }

        .desktop-topbar__avatar {
            width: 36px;
            height: 36px;
            border-radius: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent), color-mix(in srgb, var(--accent-soft) 62%, var(--accent)));
            color: var(--accent-text);
            font-weight: 800;
            box-shadow: 0 8px 18px color-mix(in srgb, var(--accent) 24%, transparent);
        }

        .desktop-topbar__caret {
            font-size: .72rem;
            color: var(--text-muted);
        }

        .desktop-topbar__guest {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .desktop-topbar__text-link {
            color: var(--text-secondary);
            font-weight: 700;
            text-decoration: none;
            padding: 0 4px;
        }

        .desktop-topbar__text-link:hover {
            color: var(--text-primary);
        }

        .desktop-topbar__dropdown {
            min-width: 240px;
            margin-top: 12px;
            border-radius: 18px;
            border: 1px solid var(--card-border) !important;
            background: var(--card-bg);
            color: var(--text-primary);
            box-shadow: var(--shadow-soft);
            --bs-dropdown-link-color: var(--text-primary);
            --bs-dropdown-link-hover-color: var(--text-primary);
            --bs-dropdown-link-active-color: var(--accent-text);
            --bs-dropdown-link-active-bg: var(--accent);
            --bs-dropdown-link-hover-bg: color-mix(in srgb, var(--accent) 12%, transparent);
        }

        .desktop-topbar__dropdown .dropdown-item {
            color: var(--text-primary) !important;
            font-weight: 700;
        }

        .desktop-topbar__dropdown .dropdown-item i {
            color: var(--text-secondary) !important;
        }

        .desktop-topbar__dropdown .dropdown-item:hover,
        .desktop-topbar__dropdown .dropdown-item:focus {
            background: color-mix(in srgb, var(--accent) 12%, transparent) !important;
            color: var(--text-primary) !important;
        }

        .desktop-topbar__dropdown .dropdown-item:hover i,
        .desktop-topbar__dropdown .dropdown-item:focus i {
            color: var(--accent) !important;
        }

        .desktop-topbar__dropdown .dropdown-item.active,
        .desktop-topbar__dropdown .dropdown-item:active {
            background: var(--accent) !important;
            color: var(--accent-text) !important;
        }

        .desktop-topbar__dropdown .dropdown-item.active i,
        .desktop-topbar__dropdown .dropdown-item:active i {
            color: var(--accent-text) !important;
        }

        .desktop-topbar__dropdown .dropdown-item.is-active {
            background: color-mix(in srgb, var(--accent) 16%, transparent) !important;
            color: var(--text-primary) !important;
        }

        .desktop-topbar__dropdown .dropdown-item.is-active i {
            color: var(--accent) !important;
        }

        .desktop-topbar__dropdown .dropdown-divider {
            border-color: var(--divider);
        }

        body[data-theme^="one-ui"] .apple-glass-card,
        body[data-theme^="one-ui"] .page-hero,
        body[data-theme^="one-ui"] .mobile-dock,
        body[data-theme^="one-ui"] .offcanvas.mobile-sheet,
        body[data-theme^="one-ui"] .navbar-floating {
            border-radius: var(--radius-panel);
            backdrop-filter: blur(var(--blur-strength)) saturate(140%);
            -webkit-backdrop-filter: blur(var(--blur-strength)) saturate(140%);
        }

        body[data-theme^="one-ui"] .apple-glass-btn,
        body[data-theme^="one-ui"] .user-dropdown-btn {
            border-radius: 16px;
        }

        @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
            .apple-glass,
            .navbar-floating,
            .mobile-dock,
            .offcanvas.mobile-sheet {
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
            body[data-theme="apple-glass-light"] { --surface-glass: rgba(255,255,255,0.92); }
            body[data-theme="apple-glass-dark"] { --surface-glass: rgba(17,19,26,0.94); }
        }

        /* Base Content Fade In */
        .page-fade {
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .navbar-floating { width: 92%; margin: 15px auto; padding: 10px 15px; }
        }

        @media (max-width: 1199px) {
            .desktop-topbar {
                padding-left: 14px;
                padding-right: 14px;
            }

            .desktop-topbar__inner {
                gap: 12px;
                padding: 10px 14px;
            }

            .desktop-topbar__icon-btn span,
            .desktop-topbar__brand-subtitle {
                display: none;
            }
        }

        @media (max-width: 1399px) {
            .desktop-topbar__menu-shell {
                width: 100%;
            }

            .desktop-topbar__user-name {
                max-width: 112px;
            }
        }

        /* Mobile dock nav (app-like) */
        .mobile-dock-wrap {
            position: fixed;
            left: 0;
            right: 0;
            bottom: var(--vv-bottom, 0px);
            z-index: 1030;
            padding: 10px 12px calc(10px + env(safe-area-inset-bottom));
            pointer-events: none; /* allow inner dock to handle clicks only */
            transform: translate3d(0,0,0); /* reduce jitter on mobile scroll */
            will-change: transform;
            backface-visibility: hidden;
        }

        /* Safety: force-hide mobile dock on desktop even if Bootstrap utilities fail to load. */
        @media (min-width: 992px) {
            .mobile-dock-wrap { display: none !important; }
        }
        .mobile-dock {
            pointer-events: auto;
            max-width: 600px;
            margin: 0 auto;
            border-radius: 30px;
            border: 1px solid color-mix(in srgb, var(--accent) 12%, var(--border));
            background:
                radial-gradient(700px 110px at 10% 0%, color-mix(in srgb, var(--accent-soft) 20%, transparent), transparent 55%),
                radial-gradient(700px 140px at 90% 100%, color-mix(in srgb, var(--accent) 20%, transparent), transparent 60%),
                color-mix(in srgb, var(--navbar-bg) 88%, transparent);
            backdrop-filter: blur(24px) saturate(175%);
            -webkit-backdrop-filter: blur(24px) saturate(175%);
            box-shadow:
                0 22px 55px rgba(2, 6, 23, 0.34),
                0 0 0 1px color-mix(in srgb, var(--accent-soft) 8%, transparent),
                inset 0 1px 0 color-mix(in srgb, white 10%, transparent);
            overflow: visible;
            position: relative;
            padding: 6px 6px 4px;
            transform: translate3d(0,0,0);
            backface-visibility: hidden;
        }
        [data-bs-theme="light"] .mobile-dock {
            border: 1px solid color-mix(in srgb, var(--accent) 10%, var(--border));
            background:
                radial-gradient(800px 140px at 12% 0%, color-mix(in srgb, var(--accent-soft) 18%, transparent), transparent 60%),
                radial-gradient(800px 180px at 88% 100%, color-mix(in srgb, var(--accent) 16%, transparent), transparent 62%),
                color-mix(in srgb, var(--navbar-bg) 92%, white);
            backdrop-filter: blur(24px) saturate(175%);
            -webkit-backdrop-filter: blur(24px) saturate(175%);
            box-shadow:
                0 18px 42px rgba(15, 23, 42, 0.18),
                0 0 0 1px rgba(255,255,255,0.58),
                inset 0 1px 0 rgba(255,255,255,0.72);
        }

        /* Subtle glass sheen */
        .mobile-dock::before {
            display: none;
        }
        .dock-item {
            flex: 1 1 0;
            display: grid;
            place-items: center;
            min-height: 72px;
            padding: 10px 4px 8px;
            text-decoration: none;
            color: color-mix(in srgb, var(--text-primary) 62%, transparent);
            font-weight: 700;
            position: relative;
            text-shadow: 0 1px 10px rgba(0,0,0,0.14);
            -webkit-font-smoothing: antialiased;
            letter-spacing: 0;
            touch-action: manipulation;
        }
        [data-bs-theme="light"] .dock-item {
            color: color-mix(in srgb, var(--text-primary) 66%, transparent);
            text-shadow: 0 1px 16px rgba(15, 23, 42, 0.08);
        }
        .dock-stack {
            display: grid;
            justify-items: center;
            gap: 4px;
        }
        .dock-icon-wrap {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: color-mix(in srgb, var(--surface) 84%, transparent);
            border: 1px solid transparent;
            transition: transform .22s ease, background .22s ease, border-color .22s ease, box-shadow .22s ease, color .22s ease;
        }
        [data-bs-theme="light"] .dock-icon-wrap {
            background: color-mix(in srgb, var(--surface) 92%, transparent);
        }
        .dock-item i { font-size: 1.18rem; }
        .dock-label {
            font-size: 0.68rem;
            margin-top: 0;
            letter-spacing: 0.015em;
            text-transform: none;
            font-weight: 800;
            opacity: .96;
            transform: translateY(0);
            transition: opacity .18s ease, transform .18s ease;
            min-height: 13px;
            overflow: hidden;
            color: var(--text-secondary);
            line-height: 1.1;
            white-space: nowrap;
        }
        .dock-item.active {
            color: var(--text-primary);
        }
        .dock-item.active .dock-icon-wrap {
            background: color-mix(in srgb, var(--accent) 16%, var(--surface));
            border-color: color-mix(in srgb, var(--accent) 22%, transparent);
            color: var(--accent-blue);
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.16);
            transform: translateY(-1px);
        }
        [data-bs-theme="light"] .dock-item.active .dock-icon-wrap {
            background: color-mix(in srgb, var(--accent) 10%, white);
            border-color: color-mix(in srgb, var(--accent) 18%, transparent);
        }
        .dock-item.active .dock-label {
            opacity: 1;
            transform: translateY(0);
            color: var(--text-primary);
        }
        .dock-item:active { transform: translateY(1px); }

        /* Tap/hover feedback (vibrancy) */
        .dock-item::before {
            content: '';
            position: absolute;
            inset: 6px 7px;
            border-radius: 18px;
            background: color-mix(in srgb, var(--surface) 74%, transparent);
            border: 1px solid color-mix(in srgb, white 10%, transparent);
            opacity: 0;
            transform: scale(0.98);
            transition: opacity 180ms ease, transform 220ms cubic-bezier(0.2, 0.9, 0.2, 1);
            pointer-events: none;
        }
        [data-bs-theme="light"] .dock-item::before {
            background: color-mix(in srgb, var(--surface) 82%, transparent);
            border-color: color-mix(in srgb, var(--accent) 8%, transparent);
        }
        .dock-item:hover::before { opacity: 0.45; transform: scale(1); }
        .dock-item:active::before { opacity: 0.70; transform: scale(0.99); }

        .dock-item > * { position: relative; z-index: 1; } /* keep icon/text above feedback */

        /* Small status dot for dock actions */
        .dock-dot {
            position: absolute;
            top: -2px;
            right: -6px;
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #ef4444;
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.55), 0 10px 18px rgba(239, 68, 68, 0.20);
        }
        [data-bs-theme="light"] .dock-dot {
            box-shadow: 0 0 0 2px rgba(255,255,255,0.85), 0 10px 18px rgba(239, 68, 68, 0.18);
        }

        /* Sliding indicator capsule (Apple-like) */
        .dock-indicator {
            position: absolute;
            top: 7px;
            height: calc(100% - 14px);
            left: 0;
            width: 0;
            border-radius: 22px;
            background: color-mix(in srgb, var(--surface) 70%, transparent);
            border: 1px solid color-mix(in srgb, var(--accent) 10%, var(--border));
            box-shadow: inset 0 1px 0 color-mix(in srgb, white 10%, transparent);
            transform: translate3d(0,0,0);
            transition: transform 320ms cubic-bezier(0.2, 0.9, 0.2, 1), width 320ms cubic-bezier(0.2, 0.9, 0.2, 1);
            pointer-events: none;
            will-change: transform, width;
        }
        [data-bs-theme="light"] .dock-indicator {
            background: rgba(2, 6, 23, 0.06);
            border-color: rgba(2, 6, 23, 0.10);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.60);
        }

        /* 5-slot dock layout (odd) so CTA sits perfectly center */
        .dock-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1.25fr 1fr 1fr;
            align-items: end;
            gap: 4px;
            padding-bottom: 0;
        }
        .dock-slot { display: grid; place-items: center; }

        /* Quick action (center) */
        .dock-cta {
            width: 70px;
            height: 70px;
            border-radius: 24px;
            display: grid;
            place-items: center;
            transform: translateY(-12px);
            margin: 0 6px;
            border: 2px solid rgba(56, 189, 248, 0.35);
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            color: #ffffff !important;
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.3);
            text-decoration: none;
            position: relative;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .dock-cta:hover {
            transform: translateY(-14px) scale(1.02);
            box-shadow: 0 16px 34px rgba(37, 99, 235, 0.6), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }
        [data-bs-theme="light"] .dock-cta {
            border: 2px solid rgba(255, 255, 255, 0.7);
            background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%);
            box-shadow:
                0 16px 36px rgba(37, 99, 235, 0.35),
                0 0 0 1px rgba(255,255,255,0.75),
                inset 0 1px 0 rgba(255,255,255,0.55);
            color: #ffffff !important;
        }
        .dock-cta::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.20) 0%, transparent 60%);
            pointer-events: none;
        }
        .dock-cta .dock-icon-wrap {
            width: auto;
            height: auto;
            background: transparent;
            border: 0;
            box-shadow: none;
        }
        .dock-cta i { 
            font-size: 1.34rem; 
            position: relative; 
            z-index: 1; 
            color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .dock-cta i.fa-play { transform: translateX(1px); }
        .dock-cta:active { transform: translateY(-8px) scale(0.96); }
        .dock-label-cta {
            color: #ffffff !important;
            font-weight: 800;
            font-size: 0.66rem;
            letter-spacing: 0.02em;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
            margin-top: 2px;
        }
        [data-bs-theme="light"] .dock-label-cta { color: #ffffff !important; }

        @media (max-width: 420px) {
            .mobile-dock-wrap {
                padding-left: 10px;
                padding-right: 10px;
            }
            .mobile-dock {
                border-radius: 24px;
            }
            .dock-icon-wrap {
                width: 40px;
                height: 40px;
            }
            .dock-item i {
                font-size: 1.08rem;
            }
            .dock-label {
                font-size: 0.62rem;
            }
            .dock-cta {
                width: 64px;
                height: 64px;
                border-radius: 22px;
            }
        }

        /* Start sheet (package picker) */
        .paket-card {
            text-align: left;
            border-radius: 18px;
            border: 1px solid var(--border-glass);
            background: rgba(255,255,255,0.04);
            color: var(--text-primary);
            padding: 14px 14px;
            transition: transform 180ms ease, filter 180ms ease, background 180ms ease;
        }
        [data-bs-theme="light"] .paket-card { background: rgba(2,6,23,0.03); }
        .paket-card:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .paket-card:active { transform: translateY(-1px) scale(0.99); }
        .paket-card.paket-card--ready {
            border-color: rgba(16,185,129,0.22);
            box-shadow: inset 0 0 0 1px rgba(16,185,129,0.06);
        }
        .paket-card.paket-card--short {
            border-color: rgba(245,158,11,0.28);
            background: linear-gradient(135deg, rgba(245,158,11,0.10), rgba(239,68,68,0.05));
        }
        .paket-card[disabled] {
            opacity: 0.58;
            cursor: not-allowed;
            filter: grayscale(0.1);
        }
        .paket-card[disabled]:hover,
        .paket-card[disabled]:active {
            transform: none;
            filter: grayscale(0.1);
        }
        .stock-state {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.8rem;
            border: 1px solid var(--border-glass);
        }
        .stock-state--ready {
            background: rgba(16,185,129,0.12);
            border-color: rgba(16,185,129,0.24);
            color: #d1fae5;
        }
        .stock-state--short {
            background: rgba(245,158,11,0.12);
            border-color: rgba(245,158,11,0.24);
            color: #fde68a;
        }
        [data-bs-theme="light"] .stock-state--ready,
        [data-bs-theme="light"] .stock-state--short {
            color: var(--text-primary);
        }
        .paket-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            letter-spacing: 0.02em;
        }
        .paket-badge .dot {
            width: 12px; height: 12px; border-radius: 4px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
            box-shadow: 0 10px 22px rgba(59,130,246,0.18);
        }

        /* Bottom-sheet offcanvas (mobile menu) */
        .offcanvas.mobile-sheet {
            border-top-left-radius: 22px;
            border-top-right-radius: 22px;
            border: 1px solid var(--border-glass);
            background:
                radial-gradient(900px 280px at 15% 0%, rgba(34, 211, 238, 0.16), transparent 60%),
                radial-gradient(900px 280px at 85% 10%, rgba(59, 130, 246, 0.16), transparent 60%),
                rgba(15, 23, 42, 0.68) !important;
            backdrop-filter: blur(22px) saturate(180%);
            -webkit-backdrop-filter: blur(22px) saturate(180%);
        }
        [data-bs-theme="light"] .offcanvas.mobile-sheet {
            background:
                radial-gradient(900px 280px at 15% 0%, rgba(8, 145, 178, 0.12), transparent 60%),
                radial-gradient(900px 280px at 85% 10%, rgba(37, 99, 235, 0.12), transparent 60%),
                rgba(255,255,255,0.86) !important;
        }
        .sheet-grabber {
            width: 46px;
            height: 5px;
            border-radius: 999px;
            background: rgba(255,255,255,0.22);
            border: 1px solid rgba(255,255,255,0.20);
        }
        [data-bs-theme="light"] .sheet-grabber {
            background: rgba(2,6,23,0.10);
            border-color: rgba(2,6,23,0.10);
        }

        /* PWA update toast (small, premium glass) */
        .pwa-toast-wrap {
            position: fixed;
            left: 0;
            right: 0;
            bottom: calc(92px + env(safe-area-inset-bottom) + var(--vv-bottom, 0px)); /* above dock */
            z-index: 1060;
            pointer-events: none;
            padding: 0 12px;
        }
        .pwa-toast {
            /* IMPORTANT: when hidden (opacity 0), don't block clicks on underlying UI. */
            pointer-events: none;
            max-width: 520px;
            margin: 0 auto;
            border-radius: 18px;
            padding: 12px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transform: translateY(16px);
            opacity: 0;
            transition: transform 260ms cubic-bezier(0.2, 0.9, 0.2, 1), opacity 220ms ease;
        }
        .pwa-toast.show {
            pointer-events: auto;
            transform: translateY(0);
            opacity: 1;
        }
        .pwa-toast .title {
            font-weight: 900;
            letter-spacing: 0.01em;
            color: var(--text-primary);
            line-height: 1.15;
        }
        .pwa-toast .sub {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-top: 2px;
        }
        .pwa-toast .btns {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
        }
        .pwa-toast .mini-btn {
            border-radius: 999px;
            border: 1px solid var(--border-glass);
            background: rgba(255,255,255,0.04);
            color: var(--text-primary);
            font-weight: 900;
            letter-spacing: 0.01em;
            padding: 10px 12px;
            transition: transform 0.18s ease, filter 0.18s ease;
            text-decoration: none;
        }
        [data-bs-theme="light"] .pwa-toast .mini-btn { background: rgba(2,6,23,0.02); }
        .pwa-toast .mini-btn:hover { transform: translateY(-1px); filter: brightness(1.05); }
        .pwa-toast .mini-btn:active { transform: translateY(0); filter: brightness(0.98); }
        .pwa-toast .mini-btn.primary {
            border: 0;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
            color: white;
        }
        .pwa-toast .mini-btn:disabled {
            opacity: 0.75;
            filter: grayscale(0.2);
            transform: none;
        }

        @media (min-width: 992px) {
            .pwa-toast-wrap { bottom: 22px; } /* no dock on desktop */
        }

        /* ==========================================================================
           ENHANCED QUESTION TYPOGRAPHY & VISUALIZERS (Deret, Cerita, Perbandingan)
           Antislop & Generative UI compliant (Institutional KORPRI / BKN Palette)
           ========================================================================== */

        /* 1. DERET ANGKA / HURUF VISUALIZER */
        .soal-deret-container {
            background: rgba(11, 37, 69, 0.03);
            border: 1px solid rgba(11, 37, 69, 0.10);
            border-radius: 18px;
            padding: 18px 20px;
        }
        [data-bs-theme="dark"] .soal-deret-container {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }
        .soal-deret-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .soal-deret-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(19, 62, 135, 0.10);
            border: 1px solid rgba(19, 62, 135, 0.22);
            color: #133E87;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        [data-bs-theme="dark"] .soal-deret-badge {
            background: rgba(96, 165, 250, 0.12);
            border-color: rgba(96, 165, 250, 0.28);
            color: #93C5FD;
        }
        .soal-deret-intro {
            font-size: 1.05rem;
            font-weight: 500;
            line-height: 1.6;
            color: var(--text-primary);
        }
        .soal-deret-track-wrapper {
            overflow-x: auto;
            padding: 8px 4px 12px 4px;
            margin: 6px 0;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }
        .soal-deret-track {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: min-content;
        }
        .soal-deret-token {
            min-width: 56px;
            padding: 10px 14px;
            border-radius: 14px;
            background: var(--surface, #ffffff);
            border: 1.5px solid rgba(11, 37, 69, 0.14);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        [data-bs-theme="dark"] .soal-deret-token {
            background: #132238;
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }
        .soal-deret-val {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
            font-size: 1.30rem;
            font-weight: 900;
            color: var(--text-primary);
            line-height: 1.2;
        }
        .soal-deret-idx {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin-top: 4px;
            text-transform: uppercase;
        }
        .soal-deret-token.is-target {
            border-color: #C59B27;
            background: rgba(197, 155, 39, 0.12);
            box-shadow: 0 0 0 2px rgba(197, 155, 39, 0.25);
        }
        [data-bs-theme="dark"] .soal-deret-token.is-target {
            background: rgba(197, 155, 39, 0.20);
            border-color: #FBBF24;
        }
        .soal-deret-token.is-target .soal-deret-val {
            color: #D97706;
        }
        [data-bs-theme="dark"] .soal-deret-token.is-target .soal-deret-val {
            color: #FBBF24;
        }
        .soal-deret-connector {
            color: rgba(11, 37, 69, 0.30);
            font-size: 0.82rem;
            padding: 0 2px;
        }
        [data-bs-theme="dark"] .soal-deret-connector {
            color: rgba(255, 255, 255, 0.30);
        }

        /* 2. SOAL CERITA & STUDI KASUS */
        .soal-cerita-card {
            background: rgba(11, 37, 69, 0.025);
            border: 1px solid rgba(11, 37, 69, 0.09);
            border-left: 4px solid #133E87;
            border-radius: 16px;
            padding: 18px 22px;
            position: relative;
        }
        [data-bs-theme="dark"] .soal-cerita-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
            border-left-color: #C59B27;
        }
        .soal-cerita-header {
            margin-bottom: 10px;
        }
        .soal-cerita-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(11, 37, 69, 0.06);
            border: 1px solid rgba(11, 37, 69, 0.14);
            color: var(--text-secondary);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        [data-bs-theme="dark"] .soal-cerita-badge {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
            color: #CBD5E1;
        }
        .soal-cerita-body {
            font-size: 1.10rem;
            line-height: 1.85;
            letter-spacing: 0.01em;
            color: var(--text-primary);
            font-weight: 450;
        }

        /* 3. CORE QUESTION CALLOUT (Pertanyaan Inti) */
        .soal-pertanyaan-box {
            background: rgba(19, 62, 135, 0.05);
            border: 1.5px solid rgba(19, 62, 135, 0.18);
            border-radius: 14px;
            padding: 16px 20px;
        }
        [data-bs-theme="dark"] .soal-pertanyaan-box {
            background: rgba(59, 130, 246, 0.08);
            border-color: rgba(59, 130, 246, 0.25);
        }
        .soal-pertanyaan-kicker {
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #133E87;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
        }
        [data-bs-theme="dark"] .soal-pertanyaan-kicker {
            color: #93C5FD;
        }
        .soal-pertanyaan-text {
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.65;
            color: var(--text-primary);
        }

        /* 4. PERBANDINGAN KUANTITATIF (X vs Y) */
        .soal-comparison-container {
            background: rgba(11, 37, 69, 0.03);
            border: 1px solid rgba(11, 37, 69, 0.10);
            border-radius: 18px;
            padding: 18px 20px;
        }
        [data-bs-theme="dark"] .soal-comparison-container {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }
        .soal-comparison-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(19, 62, 135, 0.10);
            border: 1px solid rgba(19, 62, 135, 0.22);
            color: #133E87;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        [data-bs-theme="dark"] .soal-comparison-badge {
            background: rgba(96, 165, 250, 0.12);
            border-color: rgba(96, 165, 250, 0.28);
            color: #93C5FD;
        }
        .soal-comparison-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 14px;
            align-items: center;
            margin: 12px 0;
        }
        @media (max-width: 576px) {
            .soal-comparison-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .soal-comp-vs {
                margin: 4px auto;
            }
        }
        .soal-comp-card {
            background: var(--surface, #ffffff);
            border: 1.5px solid rgba(11, 37, 69, 0.12);
            border-radius: 14px;
            padding: 14px 18px;
            text-align: center;
        }
        [data-bs-theme="dark"] .soal-comp-card {
            background: #132238;
            border-color: rgba(255, 255, 255, 0.10);
        }
        .soal-comp-label {
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }
        .soal-comp-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
        }
        .soal-comp-vs {
            font-weight: 900;
            font-size: 0.85rem;
            letter-spacing: 0.1em;
            color: #C59B27;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(197, 155, 39, 0.12);
            border: 1px solid rgba(197, 155, 39, 0.30);
            text-align: center;
        }

        /* 5. STANDARD PLAIN */
        .soal-plain-content {
            line-height: 1.85;
            font-weight: 500;
            color: var(--text-primary);
        }

        /* =========================================================
           6. TABEL DATA (pipe-encoded data table visualizer)
           ========================================================= */
        .soal-tabel-container {
            background: rgba(11, 37, 69, 0.03);
            border: 1px solid rgba(11, 37, 69, 0.10);
            border-radius: 18px;
            padding: 16px 18px;
            overflow: hidden;
        }
        [data-bs-theme="dark"] .soal-tabel-container {
            background: rgba(255, 255, 255, 0.025);
            border-color: rgba(255, 255, 255, 0.08);
        }
        .soal-tabel-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(13, 148, 136, 0.10);
            border: 1px solid rgba(13, 148, 136, 0.28);
            color: #0d9488;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        [data-bs-theme="dark"] .soal-tabel-badge {
            background: rgba(20, 184, 166, 0.12);
            border-color: rgba(20, 184, 166, 0.30);
            color: #2dd4bf;
        }
        .soal-data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.90rem;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(11, 37, 69, 0.10);
        }
        [data-bs-theme="dark"] .soal-data-table {
            border-color: rgba(255, 255, 255, 0.08);
        }
        .soal-data-table thead tr {
            background: rgba(19, 62, 135, 0.08);
        }
        [data-bs-theme="dark"] .soal-data-table thead tr {
            background: rgba(96, 165, 250, 0.10);
        }
        .soal-data-table th {
            padding: 10px 14px;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-secondary);
            border-bottom: 1.5px solid rgba(11, 37, 69, 0.12);
            white-space: nowrap;
        }
        [data-bs-theme="dark"] .soal-data-table th {
            border-bottom-color: rgba(255, 255, 255, 0.10);
        }
        .soal-data-table td {
            padding: 9px 14px;
            color: var(--text-primary);
            border-bottom: 1px solid rgba(11, 37, 69, 0.06);
            font-variant-numeric: tabular-nums;
        }
        [data-bs-theme="dark"] .soal-data-table td {
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }
        .soal-data-table tbody tr:last-child td {
            border-bottom: none;
        }
        .soal-data-table tbody tr:nth-child(odd) {
            background: rgba(11, 37, 69, 0.02);
        }
        [data-bs-theme="dark"] .soal-data-table tbody tr:nth-child(odd) {
            background: rgba(255, 255, 255, 0.025);
        }
        .soal-data-table tbody tr:hover {
            background: rgba(197, 155, 39, 0.06);
        }
        .soal-tabel-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 4px;
        }

        /* =========================================================
           7. ANALOGI KATA (A : B = C : ? format)
           ========================================================= */
        .soal-analogi-container {
            background: rgba(11, 37, 69, 0.02);
            border: 1px solid rgba(11, 37, 69, 0.08);
            border-radius: 18px;
            padding: 16px 18px;
        }
        [data-bs-theme="dark"] .soal-analogi-container {
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.07);
        }
        .soal-analogi-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(217, 119, 6, 0.10);
            border: 1px solid rgba(217, 119, 6, 0.28);
            color: #d97706;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        [data-bs-theme="dark"] .soal-analogi-badge {
            background: rgba(251, 191, 36, 0.10);
            border-color: rgba(251, 191, 36, 0.28);
            color: #fbbf24;
        }
        .soal-analogi-track {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 14px 0 10px;
        }
        .soal-analogi-pair {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .soal-analogi-term {
            background: var(--surface, #fff);
            border: 1.5px solid rgba(11, 37, 69, 0.12);
            border-radius: 12px;
            padding: 10px 18px;
            min-width: 70px;
            text-align: center;
            font-weight: 700;
            font-size: 1.0rem;
            color: var(--text-primary);
        }
        [data-bs-theme="dark"] .soal-analogi-term {
            background: #132238;
            border-color: rgba(255, 255, 255, 0.10);
        }
        .soal-analogi-term.is-unknown {
            background: rgba(197, 155, 39, 0.12);
            border-color: #C59B27;
            color: #C59B27;
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: 0.05em;
        }
        [data-bs-theme="dark"] .soal-analogi-term.is-unknown {
            background: rgba(197, 155, 39, 0.16);
            color: #fbbf24;
            border-color: #d97706;
        }
        .soal-analogi-sep {
            font-weight: 900;
            font-size: 1.3rem;
            color: #C59B27;
            line-height: 1;
        }
        .soal-analogi-eq {
            font-weight: 900;
            font-size: 1.5rem;
            color: rgba(11, 37, 69, 0.3);
            padding: 0 4px;
        }
        [data-bs-theme="dark"] .soal-analogi-eq {
            color: rgba(255, 255, 255, 0.25);
        }
        @media (max-width: 480px) {
            .soal-analogi-track {
                gap: 8px;
            }
            .soal-analogi-term {
                padding: 8px 12px;
                min-width: 56px;
                font-size: 0.9rem;
            }
        }

        /* =========================================================
           8. SILOGISME (Premis 1 / Premis 2 / Kesimpulan)
           ========================================================= */
        .soal-silogisme-container {
            background: rgba(11, 37, 69, 0.02);
            border: 1px solid rgba(11, 37, 69, 0.08);
            border-radius: 18px;
            padding: 16px 18px;
        }
        [data-bs-theme="dark"] .soal-silogisme-container {
            background: rgba(255, 255, 255, 0.025);
            border-color: rgba(255, 255, 255, 0.07);
        }
        .soal-silogisme-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.10);
            border: 1px solid rgba(99, 102, 241, 0.28);
            color: #6366f1;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        [data-bs-theme="dark"] .soal-silogisme-badge {
            background: rgba(129, 140, 248, 0.10);
            border-color: rgba(129, 140, 248, 0.28);
            color: #818cf8;
        }
        .soal-premis-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .soal-premis-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: var(--surface, #fff);
            border: 1px solid rgba(11, 37, 69, 0.09);
            border-radius: 12px;
            padding: 12px 16px;
        }
        [data-bs-theme="dark"] .soal-premis-card {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.07);
        }
        .soal-premis-num {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            color: #6366f1;
            font-size: 0.75rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        [data-bs-theme="dark"] .soal-premis-num {
            background: rgba(129, 140, 248, 0.14);
            border-color: rgba(129, 140, 248, 0.28);
            color: #818cf8;
        }
        .soal-premis-text {
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.65;
            color: var(--text-primary);
        }
        .soal-silogisme-conclusion {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: rgba(99, 102, 241, 0.06);
            border: 1.5px solid rgba(99, 102, 241, 0.20);
            border-radius: 12px;
            padding: 12px 16px;
            margin-top: 10px;
        }
        [data-bs-theme="dark"] .soal-silogisme-conclusion {
            background: rgba(129, 140, 248, 0.07);
            border-color: rgba(129, 140, 248, 0.22);
        }
        .soal-silogisme-conclusion-icon {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.14);
            border: 1px solid rgba(99, 102, 241, 0.28);
            color: #6366f1;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        [data-bs-theme="dark"] .soal-silogisme-conclusion-icon {
            background: rgba(129, 140, 248, 0.14);
            border-color: rgba(129, 140, 248, 0.28);
            color: #818cf8;
        }


/* ==========================================================================
   GLOBAL DESIGN SYSTEM UPGRADE (CAT ASN & KORPRI LUXURY DESIGN STANDARD)
   Anti-Slop Certified: Quiet Chrome, High Contrast, Tactile Depth
   ========================================================================== */

/* 1. Global Typography & Smoothing */
*, *::before, *::after {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

body {
    letter-spacing: -0.011em;
}

h1, h2, h3, h4, .apple-title, .exec-hero-title {
    letter-spacing: -0.025em;
    font-feature-settings: "cv02", "cv03", "cv04", "cv11";
}

/* 2. Premium Elevate Cards */
.apple-glass-card, .human-card, .admin-card, .bento-card {
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
    border: 1px solid var(--border-glass, rgba(255, 255, 255, 0.12));
}

[data-bs-theme="light"] .apple-glass-card,
[data-bs-theme="light"] .human-card,
[data-bs-theme="light"] .admin-card {
    background: #ffffff !important;
    border-color: rgba(11, 37, 69, 0.10) !important;
    box-shadow: 0 4px 20px -2px rgba(11, 37, 69, 0.05), 0 2px 6px -1px rgba(11, 37, 69, 0.02) !important;
}

[data-bs-theme="light"] .apple-glass-card:hover,
[data-bs-theme="light"] .human-card:hover {
    border-color: rgba(197, 155, 39, 0.32) !important;
    box-shadow: 0 12px 30px -4px rgba(11, 37, 69, 0.09), 0 4px 10px -2px rgba(11, 37, 69, 0.03) !important;
}

[data-bs-theme="dark"] .apple-glass-card,
[data-bs-theme="dark"] .human-card,
[data-bs-theme="dark"] .admin-card {
    background: #111827 !important;
    border-color: rgba(255, 255, 255, 0.10) !important;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.45) !important;
}

/* 3. Primary & Action Buttons with Tactile Depth */
.apple-btn-primary, .btn-exec-primary {
    background: linear-gradient(135deg, #C59B27 0%, #D97706 100%) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.35) !important;
    box-shadow: 0 4px 14px rgba(197, 155, 39, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.25) !important;
    font-weight: 800 !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.apple-btn-primary:hover, .btn-exec-primary:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(197, 155, 39, 0.48), inset 0 1px 0 rgba(255, 255, 255, 0.35) !important;
    filter: brightness(1.05);
}

.apple-btn-primary:active, .btn-exec-primary:active {
    transform: translateY(0) !important;
    box-shadow: 0 2px 8px rgba(197, 155, 39, 0.30) !important;
}

.apple-btn-outline {
    border: 1.5px solid rgba(197, 155, 39, 0.35) !important;
    background: rgba(197, 155, 39, 0.05) !important;
    color: var(--text-primary) !important;
    transition: all 0.2s ease !important;
}

.apple-btn-outline:hover {
    background: rgba(197, 155, 39, 0.15) !important;
    border-color: #C59B27 !important;
    color: #d97706 !important;
    transform: translateY(-1.5px);
}

[data-bs-theme="dark"] .apple-btn-outline:hover {
    color: #FBBF24 !important;
}

/* 4. Luxury Inputs & Selects */
.apple-glass-input, .admin-form-control, .form-select.apple-glass-input {
    border: 1.5px solid var(--border-glass, rgba(11, 37, 69, 0.15)) !important;
    border-radius: 14px !important;
    padding: 10px 16px !important;
    transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    background: var(--input-bg, rgba(255, 255, 255, 0.05)) !important;
    color: var(--text-primary) !important;
}

.apple-glass-input:focus, .admin-form-control:focus {
    border-color: #C59B27 !important;
    box-shadow: 0 0 0 4px rgba(197, 155, 39, 0.18) !important;
    outline: none !important;
}

/* 5. Refined Custom Scrollbars */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: rgba(197, 155, 39, 0.28);
    border-radius: 999px;
}
::-webkit-scrollbar-thumb:hover {
    background: rgba(197, 155, 39, 0.50);
}

/* 6. High-Contrast Accessible Badges */
.apple-pill, .badge-mode {
    letter-spacing: 0.02em;
    font-weight: 700;
}

    </style>
    @livewireStyles
    @stack('styles')
    <link rel="stylesheet" href="{{ ($themeEngineCss ?? asset('themes/engine.css')) }}?v=1.0.0">
    <link rel="stylesheet" id="activeThemeCss" href="{{ (($themeRegistry[$activeThemeSlug]['assets']['css'] ?? asset('themes/dark-pro/theme.css'))) . '?v=' . urlencode($themeRegistry[$activeThemeSlug]['version'] ?? '1.0') }}">
