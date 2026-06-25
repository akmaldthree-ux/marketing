@extends('layouts.app')
@section('title','Target GMV')
@section('page-title','Target GMV')
@section('content')
@php $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
<div class="d-flex align-items-center gap-2 mb-3">
  <form method="GET" class="d-flex gap-2 align-items-center">
    <label class="small fw-semibold mb-0">Tahun:</label>
    <select name="year" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
      @for($y=2024;$y<=2027;$y++)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor
    </select>
  </form>
  <small class="text-muted ms-2"><i class="bi bi-info-circle me-1"></i>Klik angka untuk edit</small>
  <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Set Target</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
        <thead class="table-light">
          <tr>
            <th style="min-width:160px">Toko</th>
            <th>Brand</th>
            @foreach($months as $m)<th class="text-end">{{ $m }}</th>@endforeach
          </tr>
        </thead>
        <tbody>
        @foreach($stores as $store)
        <tr>
          <td class="fw-semibold">{{ $store->name }}</td>
          <td><span class="badge badge-brand-{{ $store->brand }}">{{ $store->brand }}</span></td>
          @for($m=1;$m<=12;$m++)
          @php $t = $targets[$store->id]?->firstWhere('month',$m); @endphp
          <td class="text-end">
            @if($t)
              <span class="target-cell text-nowrap text-primary" style="cursor:pointer"
                data-id="{{ $t->id }}"
                data-gmv="{{ $t->gmv_target }}"
                data-store="{{ $store->name }}"
                data-month="{{ $months[$m-1] }}"
                title="Klik untuk edit">
                {{ number_format($t->gmv_target/1000000,1) }}jt
              </span>
            @else
              <span class="text-muted add-target-cell" style="cursor:pointer"
                data-store-id="{{ $store->id }}"
                data-month="{{ $m }}"
                data-year="{{ $year }}"
                data-store="{{ $store->name }}"
                data-month-name="{{ $months[$m-1] }}"
                title="Klik untuk set target">—</span>
            @endif
          </td>
          @endfor
        </tr>
        @endforeach
        </tbody>
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
        <div class="col"><label class="form-label small">Bulan</label>
          <select name="month" id="add-month" class="form-select form-select-sm" required>
            @foreach($months as $i=>$m)<option value="{{ $i+1 }}">{{ $m }}</option>@endforeach
          </select>
        </div>
        <div class="col"><label class="form-label small">Tahun</label>
          <select name="year" class="form-select form-select-sm" required>
            @for($y=2024;$y<=2027;$y++)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor
          </select>
        </div>
      </div>
      <div class="mb-2"><label class="form-label small">Target GMV (Rp)</label>
        <input type="number" name="gmv_target" class="form-control form-control-sm" min="0" step="1000000" required></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Simpan</button></div>
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
      <form id="deleteForm" method="POST" onsubmit="return confirm('Hapus target ini?')">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
      </form>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button form="editForm" class="btn btn-sm btn-primary">Simpan</button>
      </div>
    </div>
    </form>
  </div></div>
</div>

@push('scripts')
<script>
// Edit modal
document.querySelectorAll('.target-cell').forEach(el => {
  el.addEventListener('click', function() {
    const id  = this.dataset.id;
    const gmv = this.dataset.gmv;
    document.getElementById('editLabel').textContent = this.dataset.store + ' — ' + this.dataset.month;
    document.getElementById('editGmv').value = gmv;
    document.getElementById('editForm').action = '/targets/' + id;
    document.getElementById('deleteForm').action = '/targets/' + id;
    new bootstrap.Modal(document.getElementById('editModal')).show();
  });
});

// Add modal shortcut: click "—" cell
document.querySelectorAll('.add-target-cell').forEach(el => {
  el.addEventListener('click', function() {
    document.getElementById('add-store-id').value = this.dataset.storeId;
    document.getElementById('add-month').value = this.dataset.month;
    new bootstrap.Modal(document.getElementById('addModal')).show();
  });
});
</script>
@endpush
@endsection
