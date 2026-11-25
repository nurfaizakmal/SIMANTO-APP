@extends('layouts.admin')
@section('title', $servis->exists ? 'Ubah Servis' : 'Motor Masuk')
@section('judul', $servis->exists ? 'Ubah Servis' : 'Catat Motor Masuk')
@section('sub', $servis->exists ? 'Kode servis ' . $servis->kode_servis : 'Isi data motor yang baru masuk ke bengkel.')
@section('content')
@php
  // Admin yang mengubah data hanya boleh mengubah status, catatan, dan foto.
  $terkunci = $servis->exists;
@endphp
<form class="card" method="POST" enctype="multipart/form-data" action="{{ $servis->exists ? route('admin.servis.update', $servis) : route('admin.servis.store') }}" style="max-width:860px">
  @csrf @if($servis->exists) @method('PUT') @endif

  @if($terkunci)
    <p class="chip-info" style="margin:0 0 6px">Anda dapat memperbarui status, catatan, dan foto. Data lain hanya bisa diubah oleh owner.</p>
  @endif

  <div class="seksi"><h3>Data pelanggan</h3></div>
  <div class="row">
    <div><label for="nama_pelanggan">Nama pelanggan</label><input id="nama_pelanggan" name="nama_pelanggan" value="{{ old('nama_pelanggan', $servis->nama_pelanggan) }}" @readonly($terkunci) required></div>
    <div><label for="no_hp">No. HP</label><input id="no_hp" name="no_hp" value="{{ old('no_hp', $servis->no_hp) }}" @readonly($terkunci)></div>
  </div>

  <div class="seksi"><h3>Data motor</h3></div>
  <div id="galatPlat" class="galat-plat" role="alert" @if(!$errors->has('plat_nomor')) hidden @endif>{{ $errors->first('plat_nomor') }}</div>
  <div class="row">
    <div><label for="plat_nomor">Plat nomor</label><input id="plat_nomor" name="plat_nomor" class="{{ $errors->has('plat_nomor') ? 'tidak-valid' : '' }}" autocomplete="off" value="{{ old('plat_nomor', $servis->exists ? $servis->plat_nomor : '') }}" @readonly($terkunci) required></div>
    <div><label for="merk_motor">Merk / tipe motor</label><input id="merk_motor" name="merk_motor" value="{{ old('merk_motor', $servis->merk_motor) }}" @readonly($terkunci) required></div>
    <div><label for="tanggal_masuk">Tanggal masuk</label><input id="tanggal_masuk" type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', optional($servis->tanggal_masuk)->format('Y-m-d')) }}" @readonly($terkunci) required></div>
  </div>
  <label for="fotoBaru">Foto motor</label>
  <div class="unggah">
    @if($servis->fotoUrl())
      <div>
        <img id="fotoLama" src="{{ $servis->fotoUrl() }}" alt="Foto motor saat ini">
        <label style="font-weight:400;margin:8px 0 0"><input type="checkbox" name="hapus_foto" value="1"> Hapus foto ini</label>
      </div>
    @endif
    <div style="flex:1;min-width:220px">
      <input type="file" name="foto" id="fotoBaru" accept="image/jpeg,image/png,image/webp">
      <small class="bantu">JPG, PNG, atau WebP, maksimal 5 MB. Foto tampil di halaman depan website, jadi pastikan plat nomor di foto tidak terlalu jelas.</small>
      <img id="fotoPratinjau" alt="Pratinjau foto baru" style="display:none;margin-top:10px">
    </div>
  </div>

  <div class="seksi"><h3>Perbaikan</h3></div>
  <label for="keluhan">Keluhan</label>
  <textarea id="keluhan" name="keluhan" rows="3" @readonly($terkunci) required>{{ old('keluhan', $servis->keluhan) }}</textarea>
  <div class="row">
    <div>
      <label for="status">Status</label>
      <select id="status" name="status">
        @foreach(\App\Models\Servis::STATUS as $k => $v)
          <option value="{{ $k }}" @selected(old('status', $servis->status ?? 'menunggu') === $k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label for="biaya">Total biaya perbaikan (Rp)</label>
      <input id="biaya" type="number" name="biaya" min="0" inputmode="numeric" value="{{ old('biaya', $servis->biaya) }}" placeholder="isi saat biaya sudah final">
      <small class="bantu" id="biayaTeks"></small>
    </div>
  </div>
  <label for="catatan">Catatan untuk pelanggan</label>
  <textarea id="catatan" name="catatan" rows="2">{{ old('catatan', $servis->catatan) }}</textarea>
  <small class="bantu">Catatan ini tampil di halaman cek status pelanggan.</small>

  @php $galatLain = collect($errors->getMessages())->except('plat_nomor')->flatten(); @endphp
  @if($galatLain->isNotEmpty())<div class="err" style="margin-top:12px">@foreach($galatLain as $e)<div>{{ $e }}</div>@endforeach</div>@endif

  <div class="bar-aksi">
    <button class="btn">Simpan</button>
    <a class="btn sec" href="{{ route('admin.servis.index') }}">Batal</a>
  </div>
</form>

<script>
  document.getElementById('fotoBaru').addEventListener('change', function (e) {
    var p = document.getElementById('fotoPratinjau'), f = e.target.files[0];
    if (!f) { p.style.display = 'none'; return; }
    p.src = URL.createObjectURL(f); p.style.display = 'block';
  });
</script>
<script>
  (function () {
    var i = document.getElementById('biaya'), t = document.getElementById('biayaTeks');
    function f() {
      var v = parseInt(i.value, 10);
      t.textContent = isNaN(v) ? 'Masuk hitungan pendapatan owner saat status Sudah diambil.' : '= Rp ' + v.toLocaleString('id-ID');
    }
    i.addEventListener('input', f); f();
  })();
</script>
<script>
  // Cek plat saat diketik: pemberitahuan muncul langsung di atas kolom plat nomor.
  (function () {
    var i = document.getElementById('plat_nomor'), g = document.getElementById('galatPlat');
    if (!i || i.readOnly) return;
    var url = @json(route('admin.servis.cek-plat')), abaikan = @json($servis->exists ? $servis->id : null), t;
    function tampil(p) {
      if (p) { g.textContent = p; g.hidden = false; i.classList.add('tidak-valid'); }
      else { g.hidden = true; i.classList.remove('tidak-valid'); }
    }
    function cek() {
      var v = i.value.trim();
      if (v.length < 3) { tampil(''); return; }
      var q = new URLSearchParams({ plat: v }); if (abaikan) q.set('abaikan', abaikan);
      fetch(url + '?' + q, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) { if (d) tampil(d.pesan || ''); })
        .catch(function () {});
    }
    i.addEventListener('input', function () { clearTimeout(t); t = setTimeout(cek, 450); });
    i.addEventListener('blur', cek);
    @if($errors->has('plat_nomor'))
    i.scrollIntoView({ block: 'center' }); i.focus();
    @endif
  })();
</script>
@endsection
