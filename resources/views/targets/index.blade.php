@extends('layouts.app')
@section('title','Target GMV')
@section('page-title','Target GMV')
@section('content')
@php
  // Siklus Apr–Feb: Apr s/d Des = tahun siklus, Jan–Feb = tahun+1
  $cycleMonths = App\Http\Controllers\TargetController::cycleMonths();
  $monthNames  = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
@endphp

<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
  <form method="GET" class="d-flex gap-2 align-items-center">
    <label class="small fw-semibold mb-0">Siklus:</label>
    <select name="cycle" class="form-select form-select-sm" style="width:200px" onchange="this.form.submit()">
      @for($y=2024;$y<=2027;$y++)
      <option value="{{ $y }}" {{ $cycle==$y?'selected':'' }}>
        Apr {{ $y }} – Feb {{ $y+1 }}
      </option>
      @endfor
    </select>
  </form>
  <small class="text-muted ms-2"><i class="bi bi-info-circle me-1"></i>Klik angka untuk edit</small>
  <a href="{{ route('export.targets', ['year'=>$cycle]) }}" class="btn btn-success btn-sm ms-auto">
    <i class="bi bi-download me-1"></i>Export CSV
  </a>
  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
    <i class="bi bi-file-earmark-arrow-up me-1"></i>Import
  </button>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="bi bi-plus-lg me-1"></i>Set Target
  </button>
</div>

@if(session('import_errors'))
<div class="alert alert-warning alert-dismissible fade show mb-3" style="font-size:.82rem">
  <strong>Beberapa baris dilewati:</strong>
  <ul class="mb-0 mt-1">@foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
        <thead class="table-light">
          <tr>
            <th style="min-width:160px">Toko</th>
            <th>Brand</th>
            @foreach($cycleMonths as $cm)
            @php $yr = $cycle + $cm['off']; @endphp
            <th class="text-end" style="{{ $cm['off']===1 ? 'background:rgba(0,0,0,.03)' : '' }}">
              {{ $monthNames[$cm['m']] }}
              @if($cm['off']===1)<small class="text-muted d-block" style="font-size:.65rem">{{ $yr }}</small>@endif
            </th>
            @endforeach
            <th class="text-end">Total</th>
          </tr>
        </thead>
        <tbody>
        @foreach($stores as $store)
        @php
          $storeTargets = $targets->get($store->id) ?? collect();
          $rowTotal = 0;
        @endphp
        <tr>
          <td class="fw-semibold">{{ $store->name }}</td>
          <td><span class="badge badge-brand-{{ $store->brand }}">{{ $store->brand }}</span></td>
          @foreach($cycleMonths as $cm)
          @php
            $yr  = $cycle + $cm['off'];
            $key = $yr.'-'.str_pad($cm['m'],2,'0',STR_PAD_LEFT);
            $t   = $storeTargets->get($key);
            $rowTotal += $t ? $t->gmv_target : 0;
          @endphp
          <td class="text-end" style="{{ $cm['off']===1 ? 'background:rgba(0,0,0,.03)' : '' }}">
            @if($t)
              <span class="target-cell text-nowrap text-primary" style="cursor:pointer"
                data-id="{{ $t->id }}"
                data-gmv="{{ $t->gmv_target }}"
                data-store="{{ $store->name }}"
                data-month="{{ $monthNames[$cm['m']] }} {{ $yr }}"
                title="Klik untuk edit">
                {{ number_format($t->gmv_target/1000000,1) }}jt
              </span>
            @else
              <span class="text-muted add-target-cell" style="cursor:pointer"
                data-store-id="{{ $store->id }}"
                data-month="{{ $cm['m'] }}"
                data-year="{{ $yr }}"
                data-store="{{ $store->name }}"
                data-month-name="{{ $monthNames[$cm['m']] }} {{ $yr }}"
                title="Klik untuk set target">—</span>
            @endif
          </td>
          @endforeach
          <td class="text-end fw-semibold">{{ $rowTotal > 0 ? number_format($rowTotal/1000000,1).'jt' : '—' }}</td>
        </tr>
        @endforeach
        </tbody>
        {{-- Footer total per kolom --}}
        <tfoot class="table-light fw-semibold">
          <tr>
            <td colspan="2">Total</td>
            @php $grandTotal = 0; @endphp
            @foreach($cycleMonths as $cm)
            @php
              $yr  = $cycle + $cm['off'];
              $key = $yr.'-'.str_pad($cm['m'],2,'0',STR_PAD_LEFT);
              $colTotal = $targets->sum(fn($st) => optional($st->get($key))->gmv_target ?? 0);
              $grandTotal += $colTotal;
            @endphp
            <td class="text-end" style="{{ $cm['off']===1 ? 'background:rgba(0,0,0,.03)' : '' }}">
              {{ $colTotal > 0 ? number_format($colTotal/1000000,1).'jt' : '—' }}
            </td>
            @endforeach
            <td class="text-end">{{ $grandTotal > 0 ? number_format($grandTotal/1000000,1).'jt' : '—' }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- Modal Tambah Target --}}
<div class="modal fade" id="addModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('targets.store') }}">@csrf
    <div class="modal-header"><h6 class="modal-title">Set Target GMV</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label class="form-label small">Toko</label>
        <select name="store_id" id="add-store-id" class="form-select form-select-sm" required>
          <option value="">Pilih Toko...</option>
          @foreach($stores as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->brand }})</option>@endforeach
        </select>
      </div>
      <div class="row g-2 mb-2">
        <div class="col"><label class="form-label small">Bulan &amp; Tahun</label>
          <select name="month" id="add-month" class="form-select form-select-sm" required>
            @foreach($cycleMonths as $cm)
            @php $yr = $cycle + $cm['off']; @endphp
            <option value="{{ $cm['m'] }}" data-year="{{ $yr }}">{{ $monthNames[$cm['m']] }} {{ $yr }}</option>
            @endforeach
          </select>
        </div>
        <div class="col d-none"><label class="form-label small">Tahun</label>
          <input type="number" name="year" id="add-year" class="form-control form-control-sm" value="{{ $cycle }}">
        </div>
      </div>
      <div class="mb-2"><label class="form-label small">Target GMV (Rp)</label>
        <input type="number" name="gmv_target" class="form-control form-control-sm" min="0" step="1000000" required>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button class="btn btn-sm btn-primary">Simpan</button>
    </div>
    </form>
  </div></div>
</div>

{{-- Modal Edit Target --}}
<div class="modal fade" id="editModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" id="editForm">@csrf @method('PUT')
    <div class="modal-header"><h6 class="modal-title">Edit Target — <span id="editLabel"></span></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label small">Target GMV (Rp)</label>
        <input type="number" name="gmv_target" id="editGmv" class="form-control" min="0" step="1000000" required>
      </div>
    </div>
    <div class="modal-footer justify-content-between">
      <button type="button" class="btn btn-sm btn-outline-danger" id="btnDelete"><i class="bi bi-trash me-1"></i>Hapus</button>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
      </div>
    </div>
    </form>
  </div></div>
</div>

<form id="deleteForm" method="POST" style="display:none">@csrf @method('DELETE')</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Sync year when month changes in add modal
  const addMonth = document.getElementById('add-month');
  const addYear  = document.getElementById('add-year');
  if (addMonth && addYear) {
    addMonth.addEventListener('change', function() {
      addYear.value = this.selectedOptions[0].dataset.year;
    });
    // Set initial
    addYear.value = addMonth.selectedOptions[0]?.dataset.year ?? '{{ $cycle }}';
  }

  // Edit existing target
  document.querySelectorAll('.target-cell').forEach(function(el) {
    el.addEventListener('click', function() {
      document.getElementById('editLabel').textContent = this.dataset.store + ' — ' + this.dataset.month;
      document.getElementById('editGmv').value = this.dataset.gmv;
      document.getElementById('editForm').action = '/targets/' + this.dataset.id;
      document.getElementById('deleteForm').action = '/targets/' + this.dataset.id;
      document.getElementById('btnDelete').onclick = function() {
        if (confirm('Hapus target ini?')) document.getElementById('deleteForm').submit();
      };
      bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
    });
  });

  // Add target shortcut from empty cell
  document.querySelectorAll('.add-target-cell').forEach(function(el) {
    el.addEventListener('click', function() {
      document.getElementById('add-store-id').value = this.dataset.storeId;
      // Find option matching month+year
      const opts = addMonth?.options;
      if (opts) {
        for (let o of opts) {
          if (o.value == this.dataset.month && o.dataset.year == this.dataset.year) {
            addMonth.value = o.value;
            addYear.value  = o.dataset.year;
            break;
          }
        }
      }
      bootstrap.Modal.getOrCreateInstance(document.getElementById('addModal')).show();
    });
  });
});
</script>
@endpush

<x-import-modal
  id="importModal"
  title="Import Target GMV"
  action="{{ route('import.target-gmv') }}"
  template-route="{{ route('template.target-gmv') }}"
  template-label="Download Template Target GMV"
  :fields="[['name'=>'year','value'=>$cycle]]"
/>
@endsection
