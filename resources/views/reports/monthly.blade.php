@extends('layouts.app')

@section('actions')
    <div class="period-controls">
        <form method="GET" action="{{ route('report.monthly') }}" style="display:flex; gap:8px; align-items:center;">
            <select name="year">
                @for($y = 2024; $y <= 2027; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select name="start_month">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $startMonth == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-outline">Load</button>
        </form>
    </div>
    <a href="{{ route('report.export', 'monthly') }}?year={{ $year }}&start_month={{ $startMonth }}" class="btn btn-green">
        ⬇ Export .xlsx
    </a>
@endsection

@section('content')
@php
    $periods = $report['periods'] ?? [];
    $ws_pc = $report['workshop_pc'] ?? [];
    $bs = $report['bodyshop'] ?? [];
    $ws_cv = $report['workshop_cv'] ?? [];
    $p_pc = $report['parts_pc'] ?? [];
    $p_cv = $report['parts_cv'] ?? [];

    function fmtNumM($v) { return $v != 0 ? number_format($v, 0, ',', '.') : '-'; }
    function fmtDecM($v) { return $v != 0 ? number_format($v, 2, ',', '.') : '-'; }
@endphp

<div class="table-wrapper">
<table class="report-table">
<thead>
    <tr>
        <th class="corner" style="min-width:30px">#</th>
        <th class="corner" style="min-width:220px">Description</th>
        <th style="min-width:80px">Unit</th>
        @foreach($periods as $period)
            <th colspan="2">{{ $period }}</th>
        @endforeach
    </tr>
    <tr>
        <th class="corner"></th>
        <th class="corner"></th>
        <th></th>
        @foreach($periods as $p)
            <th>Amount</th>
            <th>%</th>
        @endforeach
    </tr>
</thead>
<tbody>
    {{-- UNIT SALES PC (placeholder) --}}
    <tr class="row-section"><td></td><td colspan="{{ 3 + count($periods) * 2 }}">UNIT SALES - PC</td></tr>
    <tr class="row-metric"><td></td><td><em>Coming soon — separate report source</em></td><td></td>
        @foreach($periods as $p)<td></td><td></td>@endforeach
    </tr>

    {{-- UNIT SALES CV (placeholder) --}}
    <tr class="row-section"><td></td><td colspan="{{ 3 + count($periods) * 2 }}">UNIT SALES - CV</td></tr>
    <tr class="row-metric"><td></td><td><em>Coming soon — separate report source</em></td><td></td>
        @foreach($periods as $p)<td></td><td></td>@endforeach
    </tr>

    {{-- WORKSHOP PC --}}
    <tr class="row-section"><td></td><td colspan="{{ 3 + count($periods) * 2 }}">WORKSHOP - PC</td></tr>
    <tr class="row-revenue">
        <td></td><td>Pendapatan Labor</td><td class="num">Rp</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtNumM($w['pendapatan_labor']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-metric"><td></td><td>Total Unit</td><td class="num">Unit</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtNumM($w['total_unit']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-metric"><td></td><td>Avg. Throughput/Hari</td><td class="num">Unit</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtDecM($w['avg_throughput_per_day']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-metric"><td></td><td>Avg. Jam Jual/Unit</td><td class="num">Jam</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtDecM($w['avg_jam_jual_per_unit']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-metric"><td></td><td>Avg. Labor/Jam</td><td class="num">Rp</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtNumM($w['avg_labor_per_jam']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-metric"><td></td><td>Avg. Spend/Unit</td><td class="num">Rp</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtNumM($w['avg_spend_per_unit']) }}</td><td></td>@endforeach
    </tr>
    <tr><td></td><td>Pendapatan Sublet</td><td class="num">Rp</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtNumM($w['pendapatan_sublet']) }}</td><td></td>@endforeach
    </tr>
    <tr><td></td><td>HPP Sublet</td><td class="num">Rp</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtNumM($w['hpp_sublet']) }}</td><td></td>@endforeach
    </tr>
    <tr><td></td><td>Gross Margin Sublet</td><td class="num">Rp</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtNumM($w['gross_margin_sublet']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Total Gross Margin Workshop</td><td class="num">Rp</td>
        @foreach($ws_pc as $w)<td class="num">{{ fmtNumM($w['gross_margin']) }}</td><td></td>@endforeach
    </tr>

    {{-- BODY SHOP --}}
    <tr class="row-section"><td></td><td colspan="{{ 3 + count($periods) * 2 }}">WORKSHOP - BODY SHOP</td></tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Labor</td><td class="num">Rp</td>
        @foreach($bs as $w)<td class="num">{{ fmtNumM($w['pendapatan_labor']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-metric"><td></td><td>Total Unit (Panel)</td><td class="num">Unit</td>
        @foreach($bs as $w)<td class="num">{{ fmtNumM($w['total_unit']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Total Gross Margin Body Shop</td><td class="num">Rp</td>
        @foreach($bs as $w)<td class="num">{{ fmtNumM($w['gross_margin']) }}</td><td></td>@endforeach
    </tr>

    {{-- WORKSHOP CV --}}
    <tr class="row-section"><td></td><td colspan="{{ 3 + count($periods) * 2 }}">WORKSHOP - CV</td></tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Labor</td><td class="num">Rp</td>
        @foreach($ws_cv as $w)<td class="num">{{ fmtNumM($w['pendapatan_labor']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-metric"><td></td><td>Total Unit</td><td class="num">Unit</td>
        @foreach($ws_cv as $w)<td class="num">{{ fmtNumM($w['total_unit']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Total Gross Margin CV</td><td class="num">Rp</td>
        @foreach($ws_cv as $w)<td class="num">{{ fmtNumM($w['gross_margin']) }}</td><td></td>@endforeach
    </tr>

    {{-- PARTS PC --}}
    <tr class="row-section"><td></td><td colspan="{{ 3 + count($periods) * 2 }}">PARTS - PC</td></tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Parts via WS</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['parts_via_ws_revenue']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Counter</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['counter_revenue']) }}</td><td></td>@endforeach
    </tr>
    <tr><td></td><td>HPP Counter</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['counter_hpp']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Gross Margin Counter</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['counter_gross_margin']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Dead Stock</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['dead_stock_revenue']) }}</td><td></td>@endforeach
    </tr>
    <tr><td></td><td>HPP Dead Stock</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['dead_stock_hpp']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Gross Margin Dead Stock</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['dead_stock_gross_margin']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Merchandise</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['merchandise_revenue']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Gross Margin Merchandise</td><td class="num">Rp</td>
        @foreach($p_pc as $p)<td class="num">{{ fmtNumM($p['merchandise_gross_margin']) }}</td><td></td>@endforeach
    </tr>

    {{-- PARTS CV --}}
    <tr class="row-section"><td></td><td colspan="{{ 3 + count($periods) * 2 }}">PARTS - CV</td></tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Parts via WS</td><td class="num">Rp</td>
        @foreach($p_cv as $p)<td class="num">{{ fmtNumM($p['parts_via_ws_revenue']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Counter</td><td class="num">Rp</td>
        @foreach($p_cv as $p)<td class="num">{{ fmtNumM($p['counter_revenue']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Gross Margin Counter</td><td class="num">Rp</td>
        @foreach($p_cv as $p)<td class="num">{{ fmtNumM($p['counter_gross_margin']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Dead Stock</td><td class="num">Rp</td>
        @foreach($p_cv as $p)<td class="num">{{ fmtNumM($p['dead_stock_revenue']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Gross Margin Dead Stock</td><td class="num">Rp</td>
        @foreach($p_cv as $p)<td class="num">{{ fmtNumM($p['dead_stock_gross_margin']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-revenue"><td></td><td>Pendapatan Merchandise</td><td class="num">Rp</td>
        @foreach($p_cv as $p)<td class="num">{{ fmtNumM($p['merchandise_revenue']) }}</td><td></td>@endforeach
    </tr>
    <tr class="row-total"><td></td><td>Gross Margin Merchandise</td><td class="num">Rp</td>
        @foreach($p_cv as $p)<td class="num">{{ fmtNumM($p['merchandise_gross_margin']) }}</td><td></td>@endforeach
    </tr>
</tbody>
</table>
</div>
@endsection
