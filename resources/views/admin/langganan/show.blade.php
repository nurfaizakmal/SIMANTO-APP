@extends('panel')
@section('title', 'Riwayat Langganan')
@section('judul', $terbaru->merk_motor)
@section('sub', $terbaru->nama_pelanggan . ' • ' . $kunjungan->count() . ' kali kunjungan')
@section('aksi')
  <a class="btn sec" href="{{ route('admin.langganan.index') }}">Kembali</a>
@endsection
@section('content')
<div class="card">
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    @if($langganan)<span class="lencana-langganan">★ Langganan</span>
    @else<span class="chip-info">Belum langganan (butuh {{ $batas }} kunjungan)</span>@endif
    <span class="plat-kecil">{{ $terbaru->plat_nomor }}</span>
  </div>
  <dl class="info">
    <div><dt>Pelanggan</dt><dd>{{ $terbaru->nama_pelanggan }}</dd></div>
    <div><dt>No. HP</dt><dd>{{ $terbaru->no_hp ?: '-' }}</dd></div>
    <div><dt>Jumlah kunjungan</dt><dd>{{ $kunjungan->count() }} kali</dd></div>
    <div><dt>Pertama masuk</dt><dd>{{ $kunjungan->last()->tanggal_masuk->format('d/m/Y') }}</dd></div>
    @can('kelola-servis')
      <div><dt>Total biaya (sudah diambil)</dt><dd>{{ \App\Support\Format::rupiah($totalBiaya) }}</dd></div>
    @endcan
  </dl>
</div>

<div class="card">
  <h2>Riwayat kerusakan &amp; perbaikan</h2>
  <ul class="garis-waktu">
    @foreach($kunjungan as $i => $k)
      <li>
        <a href="{{ route('admin.servis.show', $k) }}"><b>Kunjungan ke-{{ $kunjungan->count() - $i }} • {{ $k->kode_servis }}</b></a>
        <small>Masuk {{ $k->tanggal_masuk->format('d/m/Y') }}
          @if($k->tanggal_selesai) • Selesai {{ $k->tanggal_selesai->format('d/m/Y') }}@endif
          • <span class="badge s-{{ $k->status }}">{{ $k->labelStatus() }}</span>
          @can('kelola-servis')@if(!is_null($k->biaya)) • {{ \App\Support\Format::rupiah($k->biaya) }}@endif @endcan
        </small>
        <div class="riwayat-isi">
          <div><span>Kerusakan / keluhan</span>{{ $k->keluhan }}</div>
          <div><span>Perbaikan (catatan mekanik)</span>{{ $k->catatan ?: 'Belum ada catatan perbaikan.' }}</div>
        </div>
      </li>
    @endforeach
  </ul>
</div>
@endsection
