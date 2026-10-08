@extends('layout')

@section('content')
<div class="row align-items-end g-3 mb-4">
    <div class="col-lg-8">
        <div class="eyebrow mb-2">Datadog · License Toolkit</div>
        <h1 class="display-6 fw-bold mb-2" style="letter-spacing:-.04em">License Monitoring Generator</h1>
        <p class="muted mb-0" style="max-width:720px">Configure your Datadog license commitments and generate an import-ready dashboard plus individual license monitors. Derived entitlements are calculated automatically.</p>
    </div>
    <div class="col-lg-4 text-lg-end"><span class="pill"><span class="status-dot"></span> Ready to generate</span></div>
</div>

@if (!empty($generationErrors))
<div class="alert-dark-ui p-3 mb-4">
    <div class="fw-semibold mb-1">Some values need your attention</div>
    <ul class="mb-0 small ps-3">@foreach ($generationErrors as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('generator.generate') }}" id="generator-form">
@csrf
<div class="row g-4">
<div class="col-lg-8">

@php
$sectionData = [
 ['number'=>'01','title'=>'Infrastructure','subtitle'=>'Host-based infrastructure commitments and derived allowances.','icon'=>'▦'],
 ['number'=>'02','title'=>'Application Performance Monitoring','subtitle'=>'APM hosts, span commitments and profiler entitlements.','icon'=>'⌁'],
 ['number'=>'03','title'=>'Database Monitoring','subtitle'=>'Database Monitoring host commitments.','icon'=>'◉'],
 ['number'=>'04','title'=>'Digital Experience','subtitle'=>'RUM Session or RUM Without Limit commitments.','icon'=>'⌁'],
];
@endphp

<div class="surface p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="section-icon">▦</div><div><div class="eyebrow">{{ $sectionData[0]['number'] }}</div><h2 class="h5 section-title mb-1">{{ $sectionData[0]['title'] }}</h2><div class="small muted">{{ $sectionData[0]['subtitle'] }}</div></div>
    </div>
    <div class="surface-soft p-3 mb-3 license-option" data-card="infra_enabled">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <label class="d-flex align-items-center gap-3 mb-0 flex-grow-1" for="infra_enabled"><input class="license-check license-trigger" type="checkbox" id="infra_enabled" data-target="infra-fields" @checked(!empty($old['infra_host']))><span><strong>Infra Host</strong><small class="d-block muted">Drives Container, Custom Metrics and Custom Events allowances.</small></span></label>
            <span class="pill">Plan based</span>
        </div>
        <div id="infra-fields" class="row g-3 mt-1" style="display:none">
            <div class="col-md-4"><label class="form-label">Infra Host</label><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="infra_host" value="{{ $old['infra_host'] ?? '' }}" placeholder="e.g. 30"><span class="input-group-text">Host</span></div></div>
            <div class="col-md-4"><label class="form-label">Plan</label><select class="form-select" name="infra_plan"><option value="">Choose plan</option><option value="pro" @selected(($old['infra_plan'] ?? '') === 'pro')>PRO</option><option value="enterprise" @selected(($old['infra_plan'] ?? '') === 'enterprise')>Enterprise</option></select></div>
            <div class="col-md-4"><label class="form-label">Container Add-on</label><div class="input-group"><input class="form-control" type="number" min="0" step="any" name="container_addon" value="{{ $old['container_addon'] ?? 0 }}"><span class="input-group-text">Container</span></div></div>
        </div>
        <div class="small muted mt-3">PRO: Container ×5 · Custom Metrics ×100 · Custom Events ×500 &nbsp;|&nbsp; Enterprise: ×10 · ×200 · ×1000</div>
    </div>
    <div class="row g-3">
    @foreach (['ndm'=>'NDM','cnm'=>'CNM'] as $code=>$label)
    <div class="col-md-6"><div class="surface-soft p-3 license-option" data-card="{{ $code }}_enabled"><label class="d-flex gap-3 align-items-center mb-0" for="{{ $code }}_enabled"><input class="license-check license-trigger" type="checkbox" id="{{ $code }}_enabled" data-target="{{ $code }}-fields" @checked(!empty($old[$code]))><span><strong>{{ $label }}</strong><small class="d-block muted">{{ $code === 'ndm' ? 'Network Device Monitoring' : 'Cloud Network Monitoring' }}</small></span></label><div id="{{ $code }}-fields" class="mt-3" style="display:none"><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="{{ $code }}" value="{{ $old[$code] ?? '' }}"><span class="input-group-text">{{ $code === 'ndm' ? 'Device' : 'Host' }}</span></div></div></div></div>
    @endforeach
    </div>
</div>

<div class="surface p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4"><div class="section-icon">⌁</div><div><div class="eyebrow">02</div><h2 class="h5 section-title mb-1">Application Performance Monitoring</h2><div class="small muted">APM Host drives span allowances. Enterprise includes profiler entitlements.</div></div></div>
    <div class="surface-soft p-3 license-option" data-card="apm_enabled">
        <label class="d-flex align-items-center gap-3 mb-0" for="apm_enabled"><input class="license-check license-trigger" type="checkbox" id="apm_enabled" data-target="apm-fields" @checked(!empty($old['apm_host']))><span><strong>APM Host</strong><small class="d-block muted">Configure host commitment and optional span add-ons.</small></span></label>
        <div id="apm-fields" class="row g-3 mt-1" style="display:none">
            <div class="col-md-4"><label class="form-label">APM Host</label><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="apm_host" value="{{ $old['apm_host'] ?? '' }}" placeholder="e.g. 30"><span class="input-group-text">Host</span></div></div>
            <div class="col-md-4"><label class="form-label">Plan</label><select class="form-select" name="apm_plan"><option value="">Choose plan</option><option value="pro" @selected(($old['apm_plan'] ?? '') === 'pro')>PRO</option><option value="enterprise" @selected(($old['apm_plan'] ?? '') === 'enterprise')>Enterprise</option></select></div>
            <div class="col-md-4"><label class="form-label">Ingested Spans Add-on</label><div class="input-group"><input class="form-control" type="number" min="0" step="any" name="apm_ingested_addon_gb" value="{{ $old['apm_ingested_addon_gb'] ?? 0 }}"><span class="input-group-text">GB</span></div></div>
            <div class="col-md-4"><label class="form-label">Indexed Spans Add-on</label><div class="input-group"><input class="form-control" type="number" min="0" step="any" name="apm_indexed_addon_million" value="{{ $old['apm_indexed_addon_million'] ?? 0 }}"><span class="input-group-text">Million</span></div></div>
        </div>
        <div class="small muted mt-3">PRO: 150 GB ingested + 1M indexed per host. Enterprise: same span allowance + 1 Profiler Host + 4 Profiler Containers per host.</div>
    </div>
</div>

<div class="surface p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4"><div class="section-icon">◉</div><div><div class="eyebrow">03</div><h2 class="h5 section-title mb-1">Database Monitoring</h2><div class="small muted">Database Monitoring host commitment.</div></div></div>
    <div class="surface-soft p-3 license-option" data-card="dbm_enabled"><label class="d-flex gap-3 align-items-center mb-0" for="dbm_enabled"><input class="license-check license-trigger" type="checkbox" id="dbm_enabled" data-target="dbm-fields" @checked(!empty($old['dbm_host']))><span><strong>DBM Host</strong><small class="d-block muted">Set the number of monitored database hosts.</small></span></label><div id="dbm-fields" class="mt-3" style="display:none"><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="dbm_host" value="{{ $old['dbm_host'] ?? '' }}"><span class="input-group-text">Host</span></div></div></div>
</div>

<div class="surface p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4"><div class="section-icon">✦</div><div><div class="eyebrow">04</div><h2 class="h5 section-title mb-1">Digital Experience</h2><div class="small muted">Choose one RUM commercial model.</div></div></div>
    <label class="form-label">RUM Plan</label><select class="form-select mb-3" id="rum_mode" name="rum_mode"><option value="">No RUM</option><option value="session" @selected(($old['rum_mode'] ?? '') === 'session')>RUM Session</option><option value="without_limit" @selected(($old['rum_mode'] ?? '') === 'without_limit')>RUM Without Limit</option></select>
    <div id="rum-session-fields" class="row g-3 rum-fields" style="display:none"><div class="col-md-6"><label class="form-label">RUM Session</label><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="rum_sessions" value="{{ $old['rum_sessions'] ?? '' }}"><span class="input-group-text">Session</span></div></div><div class="col-md-6"><label class="form-label">RUM Session Replay</label><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="rum_session_replay" value="{{ $old['rum_session_replay'] ?? '' }}"><span class="input-group-text">Session</span></div></div></div>
    <div id="rum-without-fields" class="row g-3 rum-fields" style="display:none"><div class="col-md-4"><label class="form-label">RUM Investigate</label><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="rum_investigate" value="{{ $old['rum_investigate'] ?? '' }}"><span class="input-group-text">Session</span></div></div><div class="col-md-4"><label class="form-label">RUM Measure</label><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="rum_measure" value="{{ $old['rum_measure'] ?? '' }}"><span class="input-group-text">Session</span></div></div><div class="col-md-4"><label class="form-label">RUM Session Replay</label><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="rum_session_replay" value="{{ $old['rum_session_replay'] ?? '' }}"><span class="input-group-text">Session</span></div></div></div>
    <div class="small muted mt-3">RUM Session allows Session + Session Replay. RUM Without Limit allows Investigate + Measure + Session Replay.</div>
</div>

<div class="surface p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4"><div class="section-icon">↗</div><div><div class="eyebrow">05–06</div><h2 class="h5 section-title mb-1">Synthetic & Logs</h2><div class="small muted">Configure monthly test runs and log commitments.</div></div></div>
    <div class="row g-3">
    @foreach ([['synthetic_api','API Test','Runs'],['synthetic_browser','Browser Test','Runs'],['logs_ingested','Logs Ingested','GB'],['logs_indexed','Logs Indexed','Events'],['cloud_siem','Cloud SIEM','Events']] as $item)
    <div class="col-md-6"><div class="surface-soft p-3"><label class="form-label">{{ $item[1] }}</label><div class="input-group"><input class="form-control" type="number" min="1" step="any" name="{{ $item[0] }}" value="{{ $old[$item[0]] ?? '' }}" placeholder="0"><span class="input-group-text">{{ $item[2] }}</span></div></div></div>
    @endforeach
    </div>
</div>

</div>
<div class="col-lg-4">
    <div class="surface sticky-panel p-4">
        <div class="eyebrow mb-2">Generate</div><h2 class="h4 fw-bold mb-2">Ready when you are.</h2><p class="muted small mb-4">Your configuration is processed in memory and converted into Datadog JSON. Nothing is persisted.</p>
        <ul class="list-unstyled check-list small mb-4"><li><span class="check">✓</span>License-specific dashboard widgets</li><li><span class="check">✓</span>License-specific breakdowns</li><li><span class="check">✓</span>Plan-derived entitlements</li><li><span class="check">✓</span>Entitlement-based thresholds</li><li><span class="check">✓</span>Individual monitor JSON</li></ul>
        <button class="btn btn-primary btn-lg w-100" id="generate-btn" type="submit"><span id="generate-label">Generate Configuration</span><span id="generate-spinner" class="spinner-border spinner-border-sm ms-2 d-none" aria-hidden="true"></span></button>
        <div class="text-center muted small mt-3">No Datadog API · No database required</div>
    </div>
</div>
</div>
</form>
@endsection

@section('scripts')
<script>
(function(){
 const triggers=[...document.querySelectorAll('.license-trigger')];
 function syncTriggers(){triggers.forEach(x=>{const t=document.getElementById(x.dataset.target);if(t)t.style.display=x.checked?'flex':'none';const card=x.closest('.license-option');if(card)card.classList.toggle('active',x.checked);});}
 triggers.forEach(x=>x.addEventListener('change',syncTriggers));
 const rum=document.getElementById('rum_mode');
 function syncRum(){
  const v=rum.value;
  const sessionFields=document.getElementById('rum-session-fields');
  const withoutFields=document.getElementById('rum-without-fields');
  sessionFields.style.display=v==='session'?'flex':'none';
  withoutFields.style.display=v==='without_limit'?'flex':'none';
  sessionFields.querySelectorAll('input').forEach(input=>input.disabled=v!=='session');
  withoutFields.querySelectorAll('input').forEach(input=>input.disabled=v!=='without_limit');
 }
 rum.addEventListener('change',syncRum); syncTriggers(); syncRum();
 document.getElementById('generator-form').addEventListener('submit',function(){document.getElementById('generate-btn').disabled=true;document.getElementById('generate-label').textContent='Generating...';document.getElementById('generate-spinner').classList.remove('d-none');});
})();
</script>
@endsection
