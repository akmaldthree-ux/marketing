@extends('layouts.app')
@section('title','Target Funnel')
@section('page-title','Target Funnel')
@section('content')
@php
$stageLabels = ['views_to_visitor'=>'Views to Visitor (VTR)','atc_rate'=>'Add-to-Cart Rate','cvr'=>'Conversion Rate (CVR)'];
@endphp
<div class="d-flex justify-content-end gap-2 mb-3">
  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importFunnelModal"><i class="bi bi-file-earmark-arrow-up me-1"></i>Import Excel</button>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Set Target Funnel</button>
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
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Toko</th><th>Brand</th><th>Stage</th><th class="text-end">Target (%)</th><th>Berlaku dari</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      @forelse($targets as $t)
      <tr>
        <td class="fw-semibold">{{ $t->store?->name ?? '-' }}</td>
        <td><span class="badge badge-brand-{{ $t->store?->brand }}">{{ $t->store?->brand }}</span></td>
        <td>{{ $stageLabels[$t->stage] ?? $t->stage }}</td>
        <td class="text-end fw-semibold">{{ $t->target_pct }}%</td>
        <td>{{ $t->effective_from?->format('d/m/Y') }}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $t->id }}"><i class="bi bi-pencil"></i></button>
          <form method="POST" action="{{ route('funnel-targets.destroy',$t) }}" class="d-inline" onsubmit="return confirm('Hapus target ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </td>
      </tr>
      <div class="modal fade" id="editModal{{ $t->id }}">
        <div class="modal-dialog"><div class="modal-content">
          <form method="POST" action="{{ route('funnel-targets.update',$t) }}">@csrf @method('PUT')
          <div class="modal-header"><h6 class="modal-title">Edit Target Funnel</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <p class="text-muted small mb-2">{{ $t->store?->name }} — {{ $stageLabels[$t->stage] ?? $t->stage }}</p>
            <div class="mb-2"><label class="form-label small">Target (%)</label><input type="number" name="target_pct" class="form-control form-control-sm" value="{{ $t->target_pct }}" min="0" max="100" step="0.01" required></div>
            <div class="mb-2"><label class="form-label small">Berlaku dari</label><input type="date" name="effective_from" class="form-control form-control-sm" value="{{ $t->effective_from?->format('Y-m-d') }}" required></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Simpan</button></div>
          </form>
        </div></div>
      </div>
      @empty
      <tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-sliders fs-3 d-block mb-2 opacity-50"></i>Belum ada target funnel</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="addModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('funnel-targets.store') }}">@csrf
    <div class="modal-header"><h6 class="modal-title">Set Target Funnel</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label class="form-label small">Toko</label>
        <select name="store_id" class="form-select form-select-sm" required>
          <option value="">Pilih Toko...</option>
          @foreach($stores as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->brand }})</option>@endforeach
        </select>
      </div>
      <div class="mb-2"><label class="form-label small">Stage</label>
        <select name="stage" class="form-select form-select-sm" required>
          @foreach($stageLabels as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
        </select>
      </div>
      <div class="mb-2"><label class="form-label small">Target (%)</label><input type="number" name="target_pct" class="form-control form-control-sm" min="0" max="100" step="0.01" required></div>
      <div class="mb-2"><label class="form-label small">Berlaku dari</label><input type="date" name="effective_from" class="form-control form-control-sm" required></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Simpan</button></div>
    </form>
  </div></div>
</div>

<x-import-modal
  id="importFunnelModal"
  title="Import Target Funnel"
  action="{{ route('import.funnel-target') }}"
  template-route="{{ route('template.funnel-target') }}"
  template-label="Download Template Funnel"
/>
@endsection
