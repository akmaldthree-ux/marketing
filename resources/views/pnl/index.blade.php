@extends('layouts.app')
@section('title','P&L')
@section('page-title','Profit & Loss')
@section('content')
<form method="GET" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  <select name="year" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
    @for($y=2024;$y<=2027;$y++)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor
  </select>
  <select name="brand" class="form-select form-select-sm" style="width:140px">
    <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
    @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach
  </select>
  <button class="btn btn-primary btn-sm px-3">Filter</button>
</form>
@php $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Ags','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des']; @endphp
<div class="card">
  <div class="card-header">Laporan P&L Bulanan {{ $year }}</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
        <thead class="table-light">
          <tr>
            <th style="min-width:180px">Komponen</th>
            @foreach($months as $m=>$ml)<th class="text-end">{{ $ml }}</th>@endforeach
            <th class="text-end fw-bold">Total</th>
          </tr>
        </thead>
        <tbody>
        @php
        $keys = ['gross_gmv'=>'Gross GMV','net_gmv'=>'Net GMV','cogs'=>'HPP/COGS','ads_spend'=>'Ads Spend','operational_cost'=>'Biaya Ops','gross_profit'=>'Gross Profit','net_profit'=>'Net Profit'];
        @endphp
        @foreach($keys as $key=>$label)
        <tr {{ in_array($key,['gross_profit','net_profit'])?'class=fw-semibold':'' }}>
          <td>{{ $label }}</td>
          @foreach($months as $m=>$ml)
          @php $period = $year.'-'.$m; $v = $byMonth[$period][$key] ?? 0; @endphp
          <td class="text-end {{ in_array($key,['gross_profit','net_profit'])&&$v<0?'text-danger':'' }}">
            {{ $v!=0 ? 'Rp '.number_format(abs($v)/1000000,1).'jt' : '—' }}
          </td>
          @endforeach
          @php $total = collect($byMonth)->sum($key); @endphp
          <td class="text-end fw-bold {{ in_array($key,['gross_profit','net_profit'])&&$total<0?'text-danger':($total>0?'text-success':'') }}">
            Rp {{ number_format($total/1000000,1) }}jt
          </td>
        </tr>
        @if($key==='net_gmv')<tr><td colspan="{{ 14 }}" class="bg-light py-1"></td></tr>@endif
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
