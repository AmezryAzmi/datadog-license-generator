<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0d12">
    <title>{{ $title ?? config('datadog.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #080a0f;
            --surface: #10131a;
            --surface-2: #151922;
            --surface-3: #1b202b;
            --border: #282e3a;
            --text: #f4f7fb;
            --muted: #929aaa;
            --accent: #7c5cff;
            --accent-2: #9b84ff;
            --success: #43d19e;
            --danger: #ff6b7a;
            --shadow: 0 18px 50px rgba(0,0,0,.24);
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: radial-gradient(circle at 15% -10%, rgba(124,92,255,.14), transparent 32%),
                        radial-gradient(circle at 90% 0%, rgba(54,211,153,.06), transparent 25%), var(--bg);
            color: var(--text);
            font-size: .94rem;
        }
        .app-shell { max-width: 1240px; }
        .topbar {
            background: rgba(8,10,15,.82);
            border-bottom: 1px solid rgba(255,255,255,.07);
            backdrop-filter: blur(18px);
        }
        .brand { letter-spacing: -.02em; color: #fff !important; }
        .brand-mark {
            width: 34px; height: 34px; border-radius: 10px;
            display:inline-grid; place-items:center;
            background: linear-gradient(135deg, var(--accent), #4b7dff);
            box-shadow: 0 8px 24px rgba(124,92,255,.28);
        }
        .eyebrow { color: var(--accent-2); text-transform: uppercase; letter-spacing: .13em; font-size: .68rem; font-weight: 700; }
        .muted { color: var(--muted) !important; }
        .surface { background: linear-gradient(180deg, rgba(21,25,34,.96), rgba(15,18,25,.96)); border: 1px solid var(--border); border-radius: 16px; box-shadow: var(--shadow); }
        .surface-soft { background: var(--surface-2); border: 1px solid var(--border); border-radius: 13px; }
        .section-title { font-weight: 700; letter-spacing: -.02em; }
        .section-icon { width: 38px; height: 38px; display:grid; place-items:center; border-radius: 11px; background: rgba(124,92,255,.12); color: var(--accent-2); border: 1px solid rgba(124,92,255,.2); }
        .form-label { color: #c9ced8; font-size: .78rem; font-weight: 600; margin-bottom: .45rem; }
        .form-control, .form-select, .input-group-text {
            background-color: #0d1016 !important; color: #edf0f5 !important; border-color: #303744 !important;
        }
        .form-control::placeholder { color: #5e6675; }
        .form-control:focus, .form-select:focus { border-color: var(--accent) !important; box-shadow: 0 0 0 .2rem rgba(124,92,255,.14) !important; }
        .input-group-text { color: #737c8d !important; }
        .license-option { transition: .18s ease; cursor:pointer; }
        .license-option:hover { border-color: #3d4656; transform: translateY(-1px); }
        .license-option.active { border-color: rgba(124,92,255,.75); background: rgba(124,92,255,.055); box-shadow: inset 0 0 0 1px rgba(124,92,255,.14); }
        .license-check { width: 18px; height: 18px; accent-color: var(--accent); }
        .pill { display:inline-flex; align-items:center; gap:.35rem; padding:.3rem .58rem; border-radius:999px; background:#1b202b; color:#aeb6c5; font-size:.68rem; border:1px solid #2b3240; }
        .btn-primary { background: linear-gradient(135deg, #7958f5, #5d78ff); border:0; box-shadow: 0 10px 24px rgba(101,91,245,.2); }
        .btn-primary:hover { background: linear-gradient(135deg, #876af8, #6e88ff); }
        .btn-outline-light { border-color:#353c49; color:#dce1e9; }
        .btn-outline-light:hover { background:#1b202b; border-color:#4b5567; color:#fff; }
        .sticky-panel { position: sticky; top: 22px; }
        .check-list { color:#aeb6c5; }
        .check-list li { margin:.7rem 0; }
        .check { color:var(--success); margin-right:.45rem; }
        .alert-dark-ui { background:rgba(255,107,122,.07); border:1px solid rgba(255,107,122,.22); color:#ffadb6; border-radius:12px; }
        .json-box { min-height: 420px; max-height: 640px; overflow:auto; background:#090c11; color:#dce3ed; border:1px solid #252c38; border-radius:13px; padding:18px; font-size:.8rem; line-height:1.55; }
        .json-box::-webkit-scrollbar { width:9px; height:9px; }
        .json-box::-webkit-scrollbar-thumb { background:#303847; border-radius:10px; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .metric-card { background:#11151d; border:1px solid #292f3b; border-radius:12px; }
        .metric-value { font-size:1.05rem; font-weight:700; }
        .status-dot { width:8px; height:8px; border-radius:50%; background:var(--success); box-shadow:0 0 12px rgba(67,209,158,.65); display:inline-block; }
        .divider { border-color:#292f3b !important; opacity:1; }
        .footer { color:#596273; font-size:.72rem; }
        @media (max-width: 991.98px) { .sticky-panel { position: static; } }
    </style>
</head>
<body>
<nav class="navbar navbar-dark topbar py-3 mb-4">
    <div class="container app-shell">
        <a class="navbar-brand brand fw-semibold d-flex align-items-center gap-2" href="{{ route('generator.index') }}">
            <span class="brand-mark">◈</span>
            <span>{{ config('datadog.name') }}</span>
        </a>
        <div class="d-flex align-items-center gap-2 muted small"><span class="status-dot"></span> Stateless generator</div>
    </div>
</nav>
<div class="container app-shell pb-5">
    @yield('content')
    <div class="text-center footer mt-5">No customer data is stored · No Datadog API calls</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
