@extends('layouts.app')
@section('title','Upload Data — DSM Intelligence')
@section('page-title','Upload Data')
@section('breadcrumb')<li class="breadcrumb-item active">Upload Data</li>@endsection
@section('content')
<div class="row g-4">
    <div class="col-md-5"><div class="section-card"><div class="section-card-header"><h6><i class="bi bi-cloud-upload me-2"></i>Upload Laporan Marketplace</h6></div>
    <div class="p-3">
        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">@csrf
            <div class="mb-3"><label class="form-label fw-semibold">Toko <span class="text-danger">*</span></label><select name="store_id" class="form-select" required><option value="">Pilih toko...</option>@foreach($stores as $s)<option value="{{ $s->id }}">{{ $s->store_name }} ({{ $s->brand }})</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label fw-semibold">Platform <span class="text-danger">*</span></label><select name="source_platform" class="form-select" id="platformSelect" required onchange="updateReportTypes()"><option value="">Pilih platform...</option><option>Shopee</option><option>TikTok Shop</option><option>Meta Ads</option></select></div>
            <div class="mb-3"><label class="form-label fw-semibold">Jenis Laporan <span class="text-danger">*</span></label><select name="report_type" class="form-select" id="reportTypeSelect" required><option value="">Pilih jenis laporan...</option></select></div>
            <div class="mb-3">
                <label class="form-label fw-semibold">File (.xlsx / .csv) <span class="text-danger">*</span></label>
                <div class="border-2 border-dashed rounded-3 p-4 text-center" id="dropZone" style="border:2px dashed #CBD5E1;cursor:pointer;" onclick="document.getElementById('fileInput').click()">
                    <i class="bi bi-file-earmark-excel text-success" style="font-size:2.5rem;"></i>
                    <div class="mt-2 fw-semibold text-muted">Klik atau drag &amp; drop file</div>
                    <div class="small text-muted">Format: .xlsx, .csv (maks 10MB)</div>
                    <div id="fileName" class="mt-2 text-primary small fw-semibold"></div>
                </div>
                <input type="file" id="fileInput" name="file" accept=".xlsx,.csv,.xls" class="d-none" onchange="showFileName(this)" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="bi bi-cloud-upload me-2"></i>Upload &amp; Proses Data</button>
        </form>
    </div></div></div>
    <div class="col-md-7"><div class="section-card"><div class="section-card-header"><h6><i class="bi bi-clock-history me-2"></i>Riwayat Upload Terbaru</h6></div>
    <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>File</th><th>Toko</th><th>Platform</th><th>Jenis</th><th class="text-center">Status</th><th>Waktu</th></tr></thead>
        <tbody>@forelse($logs as $log)<tr>
            <td><div class="fw-semibold" style="font-size:0.82rem;">{{ Str::limit($log->filename,25) }}</div><small class="text-muted num">{{ $log->rows_parsed }} baris</small></td>
            <td style="font-size:0.82rem;">{{ $log->store?->store_name??'-' }}</td>
            <td><span class="badge bg-light text-dark border" style="font-size:0.7rem;">{{ $log->source_platform }}</span></td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:0.7rem;">{{ $log->report_type }}</span></td>
            <td class="text-center">@if($log->status==='success')<span class="badge bg-success">Sukses</span>@elseif($log->status==='processing')<span class="badge bg-warning text-dark">Proses</span>@else<span class="badge bg-danger">Gagal</span>@endif</td>
            <td style="font-size:0.78rem;" class="text-muted">{{ \Carbon\Carbon::parse($log->uploaded_at)->diffForHumans() }}</td>
        </tr>@empty<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox display-5 d-block mb-2 opacity-50"></i>Belum ada riwayat upload</td></tr>@endforelse</tbody>
    </table></div></div></div>
</div>
@endsection
@section('scripts')
<script>
const reportTypes={'Shopee':['orders','financials','ads','metrics'],'TikTok Shop':['orders','financials','ads'],'Meta Ads':['ads']};
const reportLabels={orders:'Laporan Pesanan',financials:'Laporan Keuangan',ads:'Laporan Iklan',metrics:'Performa Toko'};
function updateReportTypes(){const plat=document.getElementById('platformSelect').value;const sel=document.getElementById('reportTypeSelect');sel.innerHTML='<option value="">Pilih jenis laporan...</option>';if(plat&&reportTypes[plat]){reportTypes[plat].forEach(t=>{const o=new Option(reportLabels[t],t);sel.appendChild(o);})}}
function showFileName(input){if(input.files[0]){document.getElementById('fileName').textContent='✓ '+input.files[0].name;document.getElementById('dropZone').style.borderColor='#16A34A';}}
const dz=document.getElementById('dropZone');dz.addEventListener('dragover',e=>{e.preventDefault();dz.style.background='#EFF6FF';});dz.addEventListener('dragleave',()=>{dz.style.background='';});dz.addEventListener('drop',e=>{e.preventDefault();dz.style.background='';const files=e.dataTransfer.files;if(files[0]){document.getElementById('fileInput').files=files;showFileName(document.getElementById('fileInput'));}});
</script>
@endsection
