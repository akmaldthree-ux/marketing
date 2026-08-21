@props([
    'dateFrom'  => '',
    'dateTo'    => '',
    'extras'    => [],   // array of extra <select> html strings passed as slot
])
{{-- Date Range Filter Component --}}
<form method="GET" id="drfForm" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  {{-- Hidden passthrough for any extra params already in GET --}}
  @foreach(request()->except(['date_from','date_to']) as $k => $v)
    @if(!in_array($k, ['brand','platform','store_id']))
      <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endif
  @endforeach

  {{-- Preset chips --}}
  <div class="d-flex gap-1 flex-wrap" id="drfPresets">
    @foreach([
      ['label'=>'Hari ini',   'key'=>'today'],
      ['label'=>'7 hari',     'key'=>'last7'],
      ['label'=>'30 hari',    'key'=>'last30'],
      ['label'=>'Bulan ini',  'key'=>'this_month'],
      ['label'=>'Bulan lalu', 'key'=>'last_month'],
    ] as $p)
    <button type="button" class="btn btn-sm drf-preset px-3"
      data-preset="{{ $p['key'] }}"
      style="border-radius:var(--rounded-full);border:1px solid var(--color-hairline);
             background:var(--color-surface-card);color:var(--color-body);font-size:.75rem;
             transition:all .15s">
      {{ $p['label'] }}
    </button>
    @endforeach
  </div>

  {{-- Date inputs --}}
  <div class="d-flex align-items-center gap-1"
       style="background:var(--color-surface-card);border:1px solid var(--color-hairline);
              border-radius:var(--rounded-md);padding:4px 10px">
    <input type="date" name="date_from" id="drfFrom" value="{{ $dateFrom }}"
      style="border:none;background:transparent;outline:none;font-size:.82rem;
             color:var(--color-body);cursor:pointer;width:130px">
    <span style="color:var(--color-ash);font-size:.8rem">—</span>
    <input type="date" name="date_to" id="drfTo" value="{{ $dateTo }}"
      style="border:none;background:transparent;outline:none;font-size:.82rem;
             color:var(--color-body);cursor:pointer;width:130px">
  </div>

  {{-- Extra filters slot (brand, platform, store, etc.) --}}
  {{ $slot }}

  <button class="btn btn-primary btn-sm px-4" style="border-radius:var(--rounded-full)">
    <i class="bi bi-funnel-fill me-1"></i>Filter
  </button>
</form>

@push('scripts')
<script>
(function(){
  function fmt(d){ return d.toISOString().slice(0,10); }
  const today = new Date();
  const presets = {
    today:      [fmt(today), fmt(today)],
    last7:      [fmt(new Date(today - 6*864e5)), fmt(today)],
    last30:     [fmt(new Date(today - 29*864e5)), fmt(today)],
    this_month: [fmt(new Date(today.getFullYear(), today.getMonth(), 1)), fmt(today)],
    last_month: (()=>{
      const f = new Date(today.getFullYear(), today.getMonth()-1, 1);
      const t = new Date(today.getFullYear(), today.getMonth(), 0);
      return [fmt(f), fmt(t)];
    })(),
  };

  const fromEl = document.getElementById('drfFrom');
  const toEl   = document.getElementById('drfTo');

  // Highlight active preset based on current values
  function highlightActive(){
    const cur = fromEl.value + '|' + toEl.value;
    document.querySelectorAll('.drf-preset').forEach(btn => {
      const p = presets[btn.dataset.preset];
      const active = p && (p[0]+'|'+p[1] === cur);
      btn.style.background    = active ? 'var(--color-primary)' : 'var(--color-surface-card)';
      btn.style.color         = active ? '#fff' : 'var(--color-body)';
      btn.style.borderColor   = active ? 'var(--color-primary)' : 'var(--color-hairline)';
    });
  }

  document.querySelectorAll('.drf-preset').forEach(btn => {
    btn.addEventListener('click', function(){
      const p = presets[this.dataset.preset];
      if (!p) return;
      fromEl.value = p[0];
      toEl.value   = p[1];
      highlightActive();
      document.getElementById('drfForm').submit();
    });
  });

  fromEl.addEventListener('change', highlightActive);
  toEl.addEventListener('change', highlightActive);
  highlightActive();
})();
</script>
@endpush
