@props([
    'id'           => 'importModal',
    'title'        => 'Import Data',
    'action'       => '',
    'templateRoute'=> '',
    'templateLabel'=> 'Download Template',
    'fields'       => [],
])

<div class="modal fade" id="{{ $id }}">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data">@csrf
    @foreach($fields as $f)
      <input type="hidden" name="{{ $f['name'] }}" value="{{ $f['value'] }}">
    @endforeach

    <div class="modal-header">
      <h6 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2" style="color:var(--color-primary)"></i>{{ $title }}</h6>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
      {{-- Template download notice --}}
      <div class="d-flex align-items-start gap-2 p-3 mb-3"
        style="background:var(--color-surface-card);border-radius:var(--rounded-md);border:1px solid var(--color-hairline)">
        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1" style="color:var(--color-primary);font-size:.85rem"></i>
        <div style="font-size:.8rem;color:var(--color-body)">
          Gunakan template resmi agar format sesuai.
          <a href="{{ $templateRoute }}" style="color:var(--color-primary);font-weight:600;text-decoration:none" class="d-inline-flex align-items-center gap-1">
            <i class="bi bi-download" style="font-size:.75rem"></i>{{ $templateLabel }}
          </a>
        </div>
      </div>

      {{-- Guidelines --}}
      <div class="mb-3" style="font-size:.76rem;color:var(--color-mute)">
        <div style="font-weight:600;color:var(--color-charcoal);margin-bottom:.4rem">Petunjuk:</div>
        <ul class="mb-0 ps-3" style="line-height:1.8">
          <li>Format file: <strong>.xlsx</strong> atau <strong>.csv</strong></li>
          <li>Baris pertama harus berupa header (nama kolom)</li>
          <li>Baris yang diawali <code style="background:var(--color-surface-card);padding:1px 5px;border-radius:6px">#</code> akan diabaikan</li>
          <li>Nama toko harus sama persis dengan yang ada di sistem</li>
        </ul>
      </div>

      {{-- File picker --}}
      <div class="mb-1">
        <label class="form-label">Pilih File</label>
        <input type="file" name="file" class="form-control"
          accept=".csv,.xlsx,.xls" required
          onchange="document.getElementById('{{ $id }}-fname').textContent = this.files[0]?.name ?? ''">
        <div id="{{ $id }}-fname" style="font-size:.72rem;color:var(--color-ash);margin-top:.3rem"></div>
      </div>
    </div>

    <div class="modal-footer justify-content-between">
      <a href="{{ $templateRoute }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-download me-1"></i>Template
      </a>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-upload me-1"></i>Import
        </button>
      </div>
    </div>
    </form>
  </div></div>
</div>
