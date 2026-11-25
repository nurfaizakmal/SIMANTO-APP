@extends('layouts.owner')
@section('title', 'Cadangan Data')
@section('judul', 'Cadangan Data')
@section('sub', 'Simpan salinan data servis dan foto, atau kembalikan dari salinan sebelumnya.')
@section('content')
@php
  $waktu = fn ($l) => $l ? $l->created_at?->format('d/m/Y H:i') . ' oleh ' . ($l->user?->name ?? 'Sistem') : 'Belum pernah';
@endphp

<div class="dua">
  <section class="card">
    <div class="card-kepala">
      <div><h2>Buat cadangan</h2><p>Unduh satu berkas .zip berisi seluruh data.</p></div>
    </div>
    <p style="margin:0 0 12px">Saat ini ada <b>{{ number_format($jumlahServis, 0, ',', '.') }}</b> data servis dan <b>{{ number_format($jumlahFoto, 0, ',', '.') }}</b> foto motor.</p>
    <ul class="daftar-info">
      <li>Berisi: data servis, riwayat status, foto motor, dan arsip audit log.</li>
      <li>Tidak berisi: akun pengguna &amp; password.</li>
      <li>Plat nomor tetap terenkripsi di dalam berkas.</li>
    </ul>
    <p class="bantu-kecil">Terakhir diunduh: {{ $waktu($terakhirBuat) }}</p>
    <form method="POST" action="{{ route('admin.cadangan.unduh') }}" style="margin-top:14px">
      @csrf
      <button class="btn">{!! \App\Support\Ikon::svg('download', 17) !!}Unduh cadangan</button>
    </form>
    
      <hr class="pemisah">
      <h3>Cadangan lengkap (owner)</h3>
      <p class="bantu-kecil" style="margin:4px 0 10px">Sama seperti di atas, ditambah <b>akun pengguna beserta hash password</b>. Hanya untuk owner: simpan di tempat aman dan jangan diberikan ke admin.</p>
      <form method="POST" action="{{ route('admin.cadangan.unduh') }}">
        @csrf <input type="hidden" name="jenis" value="lengkap">
        <button class="btn sec">{!! \App\Support\Ikon::svg('download', 17) !!}Unduh cadangan lengkap</button>
      </form>
    
    <p class="bantu-kecil" style="margin-top:12px">Simpan berkas ini di tempat aman (bukan hanya di komputer bengkel). Jangan lupa simpan juga <b>APP_KEY</b> dari file .env: tanpa kunci yang sama, plat nomor di cadangan tidak bisa dibuka.</p>
  </section>

  
  <section class="card kartu-bahaya">
    <div class="card-kepala">
      <div><h2>Pulihkan data</h2><p>Hanya owner. Mengganti seluruh data sekarang dengan isi cadangan.</p></div>
    </div>
    <p class="peringatan-merah">Semua data servis, riwayat, dan foto saat ini akan <b>diganti</b> dengan isi cadangan. Sebelum diganti, kondisi sekarang otomatis disimpan di server sebagai cadangan pengaman. Akun pengguna dan audit log tidak berubah.</p>

    <form method="POST" action="{{ route('admin.cadangan.pulihkan') }}" enctype="multipart/form-data"
          onsubmit="return confirm('Ganti SEMUA data servis sekarang dengan isi cadangan ini?')">
      @csrf
      <label for="berkas">Berkas cadangan (.zip)</label>
      <input id="berkas" type="file" name="berkas" accept=".zip,application/zip" required>
      @error('berkas')<div class="err">{{ $message }}</div>@enderror

      <label for="password">Password Anda (konfirmasi)</label>
      <input id="password" type="password" name="password" autocomplete="current-password" required>
      @error('password')<div class="err">{{ $message }}</div>@enderror

      <label style="display:flex;gap:9px;align-items:flex-start;font-weight:500;margin-top:14px">
        <input type="checkbox" name="konfirmasi" value="1" style="margin-top:4px" required>
        <span>Saya mengerti data servis saat ini akan diganti.</span>
      </label>
      @error('konfirmasi')<div class="err">{{ $message }}</div>@enderror

      <div class="kotak-akun">
        <label style="display:flex;gap:9px;align-items:flex-start;font-weight:600;margin:0">
          <input type="checkbox" name="pulihkan_pengguna" value="1" style="margin-top:4px" @checked(old('pulihkan_pengguna')) data-akun>
          <span>Ikut pulihkan akun pengguna <small class="bantu">Hanya berlaku bila berkas adalah cadangan lengkap. Akun ditambah/diperbarui berdasarkan email (password dan role ikut kembali ke kondisi cadangan); tidak ada akun yang dihapus, dan akun Anda sendiri tidak diubah.</small></span>
        </label>
        <div data-akun-ketik style="display:none">
          <label for="ketik">Ketik <b>PULIHKAN AKUN</b> untuk memastikan</label>
          <input id="ketik" name="ketik" autocomplete="off" value="{{ old('ketik') }}">
          @error('ketik')<div class="err">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="bar-aksi"><button class="btn red">Pulihkan data</button></div>
    </form>
    <p class="bantu-kecil" style="margin-top:12px">Terakhir dipulihkan: {{ $waktu($terakhirPulih) }}</p>
  </section>
  @else
  <section class="card">
    <div class="card-kepala"><div><h2>Pulihkan data</h2></div></div>
    <p class="bantu-kecil" style="font-size:.9rem">Pemulihan data hanya dapat dilakukan oleh owner. Hubungi owner bila data perlu dikembalikan.</p>
  </section>
  
</div>
<script>
  (function () {
    var c = document.querySelector('[data-akun]'), k = document.querySelector('[data-akun-ketik]');
    if (!c || !k) return;
    var u = function () { k.style.display = c.checked ? 'block' : 'none'; };
    c.addEventListener('change', u); u();
  })();
</script>
@endsection
