@extends('layouts.app', ['title' => 'Review import'])

@section('content')
    <div class="page-heading-row compact-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> DATA IMPORT · STEP 02</div><h1>Review the<br><em>column mapping.</em></h1><p>{{ $import['original_name'] }} · {{ $import['row_count'] }} rows · {{ $accounts->name }}</p></div><a class="text-link" href="{{ route('imports.create') }}">Cancel import</a></div>
    <form class="panel import-review" method="POST" action="{{ route('imports.confirm') }}">
        @csrf
        <input type="hidden" name="key" value="{{ $key }}">
        <div class="import-review-heading"><div><h2>Match file columns to trade fields</h2><p>Symbol, direction, and opened time are required. The other fields are optional.</p></div><span class="review-count">{{ count($headers) }} columns found</span></div>
        <div class="mapping-grid">
            @foreach ($fields as $field => $label)
                <label class="field"><span>{{ $label }} @if(in_array($field, ['symbol', 'side', 'opened_at'], true))<small>· required</small>@endif</span><select name="mapping[{{ $field }}]" @required(in_array($field, ['symbol', 'side', 'opened_at'], true))><option value="">Not mapped</option>@foreach ($headers as $index => $header)<option value="{{ $index }}" @selected((string) old('mapping.'.$field, $suggested[$field] ?? '') === (string) $index)>{{ $header ?: 'Column '.($index + 1) }}</option>@endforeach</select></label>
            @endforeach
        </div>
        <div class="import-preview-wrap"><div class="import-preview-title"><strong>Data preview</strong><span>First {{ count($import['preview']) }} rows</span></div><div class="table-scroll"><table><thead><tr>@foreach ($headers as $header)<th>{{ $header }}</th>@endforeach</tr></thead><tbody>@foreach ($import['preview'] as $row)<tr>@foreach ($headers as $index => $header)<td>{{ $row[$index] ?? '' }}</td>@endforeach</tr>@endforeach</tbody></table></div></div>
        <div class="form-actions"><a class="text-link" href="{{ route('imports.create') }}">Cancel</a><button class="button button-dark" type="submit">Confirm and import <span>↗</span></button></div>
    </form>
@endsection
