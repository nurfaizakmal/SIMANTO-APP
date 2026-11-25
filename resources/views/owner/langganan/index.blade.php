@extends('layouts.owner')
@section('title', 'Langganan')
@section('judul', 'Motor Langganan')
@section('sub', 'Plat nomor yang sudah masuk lebih dari ' . ($batas - 1) . ' kali (mulai kunjungan ke-' . $batas . ').')
@section('content')
@php $cari = trim((string) request('cari')); @endphp

<div class="card">
  <form method="GET" class="cari-bar">
    <input name="cari" placeholder="Cari nama pelanggan atau plat nomor lengkap" value="{{ $cari }}" aria-label="Pencarian">
    <button class="btn sec">Cari</button>
    @if($cari)<a class="btn sec" href="{{ route('admin.langganan.index') }}">Reset</a>@endif
  </form>

  <div class="tabel-geser">
    <table class="tabel">
      <thead>
        <tr><th>Pelanggan</th><th>Plat</th><th>Motor</th><th>Kunjungan</th><th>Terakhir masuk</th><th></th></tr>
      </thead>
      <tbody>
      @forelse($daftar as $b)
        @php $s = $b->servis; @endphp
        <tr>
          <td>{{ $s->nama_pelanggan }}@if($s->no_hp)<span class="sub">{{ $s->no_hp }}</span>@endif</td>
          <td class="nowrap"><span class="plat-kecil">{{ $s->plat_nomor }}</span></td>
          <td>{{ $s->merk_motor }}</td>
          <td class="nowrap"><span class="lencana-langganan">★ {{ $b->jumlah }}x</span></td>
          <td class="nowrap">{{ $b->terakhir?->format('d/m/Y') }}</td>
          <td><div class="aksi"><a class="btn sm" href="{{ route('admin.langganan.show', $s) }}">Riwayat</a></div></td>
        </tr>
      @empty
        <tr><td colspan="6">
          <div class="kosong-besar">
            <span class="em">⭐</span>
            @if($cari)Tidak ada langganan yang cocok dengan pencarian ini.
            @else Belum ada motor langganan. Motor otomatis masuk daftar ini setelah datang {{ $batas }} kali dengan plat yang sama.@endif
          </div>
        </td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $daftar->links() }}
</div>
@endsection
