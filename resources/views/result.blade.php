@extends('layout')

@section('content')
<style>
    .result-accordion, .result-accordion .accordion-item, .result-accordion .accordion-collapse, .result-accordion .accordion-body { min-width: 0; max-width: 100%; }
    .result-accordion .accordion-item { background: transparent; color: var(--text); border: 0; }
    .result-accordion .accordion-button { min-width: 0; background: transparent; color: var(--text); box-shadow: none; }
    .result-accordion .accordion-button:not(.collapsed) { background: rgba(124,92,255,.06); color: var(--text); }
    .result-accordion .accordion-button::after { filter: invert(1); }
    .result-accordion .accordion-body { min-width: 0; padding-top: 1rem; }
    .result-accordion .json-box { width: 100%; min-width: 0; max-width: 100%; }
    .result-actions { margin-top: .5rem; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div><div class="eyebrow mb-2">Generation complete</div><h1 class="display-6 fw-bold mb-2" style="letter-spacing:-.04em">Your configuration is ready.</h1><p class="muted mb-0">Dashboard and monitor JSON have been generated from your selected license commitments.</p></div>
    <a href="{{ route('generator.index') }}" class="btn btn-outline-light">← Generate Again</a>
</div>

<div class="accordion result-accordion d-grid gap-3" id="result-accordion">
    <div class="surface accordion-item">
        <h2 class="accordion-header" id="summary-heading">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#summary-collapse" aria-expanded="true" aria-controls="summary-collapse">
                <span><span class="eyebrow d-block mb-1">Selected commitments</span><span class="h5 section-title mb-0">License Summary</span></span>
                <span class="pill ms-auto me-3"><span class="status-dot"></span>{{ count($result['summary']) }} license{{ count($result['summary']) === 1 ? '' : 's' }}</span>
            </button>
        </h2>
        <div id="summary-collapse" class="accordion-collapse collapse show" aria-labelledby="summary-heading" data-bs-parent="#result-accordion">
            <div class="accordion-body"><div class="row g-3">
            @foreach ($result['summary'] as $item)
                <div class="col-md-6 col-xl-4"><div class="metric-card p-3 h-100"><div class="d-flex justify-content-between gap-2"><strong>{{ $item['name'] }}</strong><span class="status-dot mt-1"></span></div><div class="metric-value mt-2">{{ rtrim(rtrim(number_format($item['commitment'], 4, '.', ''), '0'), '.') }} <span class="muted fw-normal fs-6">{{ $item['unit'] }}</span></div><div class="small muted mt-1">{{ $item['source'] }}</div></div></div>
            @endforeach
            </div></div>
        </div>
    </div>

    <div class="surface accordion-item">
        <h2 class="accordion-header" id="dashboard-heading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dashboard-collapse" aria-expanded="false" aria-controls="dashboard-collapse">
                <span><span class="eyebrow d-block mb-1">Dashboard export</span><span class="h5 section-title mb-0">Dashboard JSON</span><span class="small muted d-block mt-1">Import this JSON into Datadog to create the license usage dashboard.</span></span>
            </button>
        </h2>
        <div id="dashboard-collapse" class="accordion-collapse collapse" aria-labelledby="dashboard-heading" data-bs-parent="#result-accordion">
            <div class="accordion-body">
                <div class="d-flex justify-content-end gap-2 mb-3 result-actions"><button class="btn btn-sm btn-outline-light" data-copy-target="dashboard-json">Copy JSON</button><button class="btn btn-sm btn-primary" data-download-target="dashboard-json" data-filename="datadog-license-dashboard.json">Download</button></div>
                <pre class="json-box mono mb-0" id="dashboard-json">{{ json_encode($result['dashboard'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>

    <div class="surface accordion-item">
        <h2 class="accordion-header" id="alerts-heading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#alerts-collapse" aria-expanded="false" aria-controls="alerts-collapse">
                <span><span class="eyebrow d-block mb-1">Monitor exports</span><span class="h5 section-title mb-0">License Alert JSON</span><span class="small muted d-block mt-1">One monitor file is generated for every selected license.</span></span>
            </button>
        </h2>
        <div id="alerts-collapse" class="accordion-collapse collapse" aria-labelledby="alerts-heading" data-bs-parent="#result-accordion">
            <div class="accordion-body">
                <div class="d-flex justify-content-end mb-3"><form method="POST" action="{{ route('generator.download-monitors') }}" id="monitor-zip-form"><input type="hidden" name="monitors" id="monitor-zip-data">@csrf<button class="btn btn-sm btn-primary" type="submit">↓ Download All as ZIP</button></form></div>
                <div class="accordion" id="monitor-accordion">
                @foreach ($result['monitor_files'] as $index => $monitor)
                    <div class="surface-soft accordion-item mb-2">
                        <h3 class="accordion-header" id="monitor-heading-{{ $index }}">
                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#monitor-collapse-{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="monitor-collapse-{{ $index }}">
                                <span class="d-flex align-items-center gap-2"><span class="status-dot"></span><span><strong>{{ $monitor['name'] }}</strong><span class="small muted mono d-block">{{ $monitor['filename'] }}</span></span></span>
                            </button>
                        </h3>
                        <div id="monitor-collapse-{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="monitor-heading-{{ $index }}" data-bs-parent="#monitor-accordion">
                            <div class="accordion-body">
                                <div class="d-flex justify-content-end gap-2 mb-3 result-actions"><button class="btn btn-sm btn-outline-light" data-copy-target="monitor-json-{{ $index }}">Copy</button><button class="btn btn-sm btn-outline-light" data-download-target="monitor-json-{{ $index }}" data-filename="{{ $monitor['filename'] }}">Download</button></div>
                                <pre class="json-box mono mb-0" id="monitor-json-{{ $index }}">{{ $monitor['json'] }}</pre>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
 document.querySelectorAll('[data-copy-target]').forEach(button=>button.addEventListener('click',async()=>{const target=document.getElementById(button.dataset.copyTarget);const original=button.textContent;try{await navigator.clipboard.writeText(target.textContent);button.textContent='Copied ✓';}catch(e){button.textContent='Copy failed';}setTimeout(()=>button.textContent=original,1200);}));
 document.querySelectorAll('[data-download-target]').forEach(button=>button.addEventListener('click',()=>{const target=document.getElementById(button.dataset.downloadTarget);const blob=new Blob([target.textContent],{type:'application/json;charset=utf-8'});const url=URL.createObjectURL(blob);const a=document.createElement('a');a.href=url;a.download=button.dataset.filename;document.body.appendChild(a);a.click();a.remove();URL.revokeObjectURL(url);}));
 const data=document.getElementById('monitor-zip-data'); if(data)data.value=JSON.stringify(@json($result['monitor_files']));
})();
</script>
@endsection
