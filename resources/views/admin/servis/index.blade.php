@extends('layouts.admin')
@section('title', 'Servis')
@section('judul', 'Daftar Motor Masuk')
@section('sub', 'Pantau semua motor di bengkel dan status perbaikannya.')
@section('aksi')
  
    <a class="btn" href="{{ route('admin.servis.create') }}">{!! \App\Support\Ikon::svg('plus', 17) !!}Motor Masuk</a>
  
@endsection
@section('content')
@php
  $statusAktif = request('status');
  $cari = trim((string) request('cari'));
  $jumlahKunjungan = \App\Models\Servis::hitungKunjungan($daftar->pluck('plat_nomor_hash'));
  $batasLangganan = \App\Models\Servis::batasLangganan();
  $warna = ['menunggu' => '#6b7a87', 'dikerjakan' => '#1f6fb2', 'menunggu_sparepart' => '#c97a0a', 'selesai' => '#2e9e6b', 'diambil' => '#4a5a68'];
@endphp

<nav class="tab-status" aria-label="Filter status">
  <a href="{{ route('admin.servis.index', array_filter(['cari' => $cari])) }}" class="{{ $statusAktif ? '' : 'on' }}">Semua <b>{{ $hitung->sum() }}</b></a>
  @foreach(\App\Models\Servis::STATUS as $k => $label)
    <a href="{{ route('admin.servis.index', array_filter(['status' => $k, 'cari' => $cari])) }}" class="{{ $statusAktif === $k ? 'on' : '' }}">
      <i class="titik" style="background:{{ $warna[$k] }}"></i>{{ $label }} <b>{{ $hitung[$k] ?? 0 }}</b>
    </a>
  @endforeach
</nav>

<div class="card">
  <form method="GET" class="cari-bar">
    @if($statusAktif)<input type="hidden" name="status" value="{{ $statusAktif }}">@endif
    <input name="cari" placeholder="Cari kode servis, nama pelanggan, atau plat nomor lengkap" value="{{ $cari }}" aria-label="Pencarian">
    <button class="btn sec">Cari</button>
    @if($cari)<a class="btn sec" href="{{ route('admin.servis.index', array_filter(['status' => $statusAktif])) }}">Reset</a>@endif
  </form>

  <div class="tabel-geser">
    <table class="tabel">
      <thead>
        <tr><th>Motor</th><th>Pelanggan</th><th>Plat</th><th>Status</th><th>Biaya</th><th>Masuk</th><th></th></tr>
      </thead>
      <tbody>
      @forelse($daftar as $s)
        <tr>
          <td>
            <div class="sel-motor">
              <span class="thumb">@if($s->fotoUrl())<img src="{{ $s->fotoUrl() }}" alt="Foto {{ $s->merk_motor }}" loading="lazy">@else🏍️@endif</span>
              <div><b>{{ $s->merk_motor }}</b><small>{{ $s->kode_servis }}</small></div>
            </div>
          </td>
          <td>{{ $s->nama_pelanggan }}@if($s->no_hp)<span class="sub">{{ $s->no_hp }}</span>@endif</td>
          <td class="nowrap"><span class="plat-kecil">{{ $s->plat_nomor }}</span>
            @if(($jumlahKunjungan[$s->plat_nomor_hash] ?? 0) >= $batasLangganan)<a class="lencana-langganan" href="{{ route('admin.langganan.show', $s) }}" title="Motor langganan">★ Langganan</a>@endif
          </td>
          <td><span class="badge s-{{ $s->status }}">{{ $s->labelStatus() }}</span></td>
          <td class="nowrap">{{ is_null($s->biaya) ? '-' : \App\Support\Format::rupiah($s->biaya) }}</td>
          <td class="nowrap">{{ $s->tanggal_masuk->format('d/m/Y') }}</td>
          <td>
            <div class="aksi">
              <a class="btn sec sm" href="{{ route('admin.servis.show', $s) }}">Detail</a>
              
                <a class="btn sm" href="{{ route('admin.servis.edit', $s) }}">Ubah status</a>
              
              
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7">
          <div class="kosong-besar">
            <span class="em">🔎</span>
            @if($cari || $statusAktif)Tidak ada motor yang cocok dengan pencarian ini.@else Belum ada motor yang tercatat.@endif
          </div>
        </td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $daftar->links() }}
</div>
@endsection
