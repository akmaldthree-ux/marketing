@props([
    'id'           => 'importModal',
    'title'        => 'Import Data',
    'action'       => '',
    'templateRoute'=> '',
    'templateLabel'=> 'Download Template',
    'fields'       => [],   // array of extra hidden/select inputs: [['name'=>'year','value'=>$year]]
])

<div class="modal fade" id="{{ $id }}">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data">@csrf
    @foreach($fields as $f)
      <input type="hidden" name="{{ $f['name'] }}" value="{{ $f['value'] }}">
    @endforeach
    <div class="modal-header">
      <h6 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>{{ $title }}</h6>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      {{-- Download template --}}
      <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.82rem">
        <i class="bi bi-info-circle-fill"></i>
        <div>
          Gunakan template resmi agar format sesuai.
          <a href="{{ $templateRoute }}" class="alert-link fw-semibold text-nowrap">
            <i class="bi bi-download me-1"></i>{{ $templateLabel }}
          </a>
        </div>
      </div>

      {{-- Petunjuk --}}
      <div class="mb-3 p-2 bg-light rounded" style="font-size:.78rem;color:#6b7280">
        <div class="fw-semibold mb-1 text-dark">Petunjuk:</div>
        <ul class="mb-0 ps-3">
          <li>Format file: <strong>.csv</strong> atau <strong>.xlsx</strong></li>
          <li>Baris pertama harus berupa header (nama kolom)</li>
          <li>Baris yang diawali <code>#</code> akan diabaikan</li>
          <li>Nama toko harus sama persis dengan yang ada di sistem</li>
        </ul>
      </div>

      {{-- Upload area --}}
      <div class="mb-2">
        <label class="form-label small fw-semibold">Pilih File</label>
        <input type="file" name="file" class="form-control form-control-sm"
          accept=".csv,.xlsx,.xls" required
          onchange="document.getElementById('{{ $id }}-fname').textContent = this.files[0]?.name ?? ''">
        <div class="form-text" id="{{ $id }}-fname"></div>
      </div>
    </div>
    <div class="modal-footer justify-content-between">
      <a href="{{ $templateRoute }}" class="btn btn-sm btn-outline-success">
        <i class="bi bi-download me-1"></i>Template
      </a>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-sm btn-primary">
          <i class="bi bi-upload me-1"></i>Import
        </button>
      </div>
    </div>
    </form>
  </div></div>
</div>
