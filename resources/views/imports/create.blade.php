@extends('layouts.app', ['title' => 'Import trades'])

@section('content')
    <div class="page-heading-row compact-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> DATA IMPORT</div><h1>Bring trades<br><em>into one journal.</em></h1><p>Upload a CSV or TSV, review the detected columns, then confirm the import.</p></div><a class="text-link" href="{{ route('trades.index') }}">← Back to trades</a></div>
    <div class="import-layout">
        <form class="panel import-panel" method="POST" action="{{ route('imports.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="panel-heading"><div class="eyebrow">STEP 01 · UPLOAD</div><h2>Select a trade file</h2><p>CSV, TSV, or tabular TXT files up to 10 MB.</p></div>
            <label class="field"><span>Trading account</span><select name="trading_account_id" required>@foreach ($accounts as $account)<option value="{{ $account->id }}" @selected((string) old('trading_account_id') === (string) $account->id)>{{ $account->name }} · {{ $account->platform ?: 'Manual' }}</option>@endforeach</select></label>
            <label class="field"><span>File</span><input type="file" name="file" accept=".csv,.tsv,.txt,text/csv,text/tab-separated-values,text/plain" required></label>
            <div class="import-note"><strong>Duplicate protection</strong><span>Matching position IDs or identical imported trade details will be skipped. Existing records are never overwritten.</span></div>
            <button class="button button-dark" type="submit">Upload and review <span>→</span></button>
        </form>
        <div class="panel import-guide"><div class="eyebrow">SUPPORTED NOW</div><h2>Structured files</h2><p>Column names are matched when possible. You can confirm or change every mapping before anything is added.</p><ul><li>CSV with comma or semicolon separators</li><li>TSV and tab-separated text</li><li>Elev8, Vantage, or other platforms with readable columns</li></ul><div class="guide-foot">Excel workbooks, PDFs, and screenshots are planned for later stages.</div></div>
    </div>
@endsection
