@extends('layouts.app')

@section('content')
<div style="max-width:900px; margin:0 auto;">
    <h2 style="font-size:1.2rem; font-weight:600; margin-bottom:16px;">📁 Import Source Data</h2>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="import-grid">
        {{-- PC Section --}}
        <div class="import-section">
            <h3>🚗 Passenger Car (PC)</h3>
            @foreach(['labour' => 'Labour', 'parts' => 'Parts Counter', 'throughput' => 'Throughput', 'wip' => 'WIP/Unit', 'deadstock' => 'Dead Stock'] as $type => $label)
                @php $batch = $batches->where('file_type', $type)->where('franchise', 'pc')->first(); @endphp
                <div class="file-slot">
                    <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data" style="display:flex; align-items:center; gap:8px; width:100%;">
                        @csrf
                        <input type="hidden" name="file_type" value="{{ $type }}">
                        <input type="hidden" name="franchise" value="pc">
                        <label>{{ $label }}</label>
                        <input type="file" name="file" accept=".xls,.xlsx,.ods" required>
                        <button type="submit" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem;">Import</button>
                        @if($batch)
                            <span class="status status-imported">✓ {{ $batch->record_count }} rows</span>
                        @endif
                    </form>
                </div>
            @endforeach
        </div>

        {{-- CV Section --}}
        <div class="import-section">
            <h3>🚚 Commercial Vehicle (CV)</h3>
            @foreach(['labour' => 'Labour', 'parts' => 'Parts Counter', 'throughput' => 'Throughput', 'wip' => 'WIP/Unit', 'deadstock' => 'Dead Stock'] as $type => $label)
                @php $batch = $batches->where('file_type', $type)->where('franchise', 'cv')->first(); @endphp
                <div class="file-slot">
                    <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data" style="display:flex; align-items:center; gap:8px; width:100%;">
                        @csrf
                        <input type="hidden" name="file_type" value="{{ $type }}">
                        <input type="hidden" name="franchise" value="cv">
                        <label>{{ $label }}</label>
                        <input type="file" name="file" accept=".xls,.xlsx,.ods" required>
                        <button type="submit" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem;">Import</button>
                        @if($batch)
                            <span class="status status-imported">✓ {{ $batch->record_count }} rows</span>
                        @endif
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
