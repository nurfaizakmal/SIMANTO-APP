@extends('panel')
@section('title', 'Detail Servis')
@section('judul', $servis->merk_motor)
@section('sub', 'Kode servis ' . $servis->kode_servis)
@section('aksi')
  <a class="btn sec" href="{{ route('admin.servis.index') }}">Kembali</a>
  @can('kelola-servis')
    <a class="btn" href="{{ route('admin.servis.edit', $servis) }}">{{ auth()->user()->role === 'owner' ? 'Ubah' : 'Ubah status' }}</a>
  @endcan
@endsection
@section('content')
<div class="card">
  <div class="detail">
    <div>
      @if($servis->fotoUrl())
        <img class="detail-foto" src="{{ $servis->fotoUrl() }}" alt="Foto {{ $servis->merk_motor }}">
      @else
        <div class="detail-kosong" aria-label="Belum ada foto">🏍️</div>
      @endif
    </div>
    <div>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <span class="badge s-{{ $servis->status }}" style="font-size:.9rem;padding:5px 14px">{{ $servis->labelStatus() }}</span>
        <span class="plat-kecil">{{ $servis->plat_nomor }}</span>
        @if($servis->langganan())<a class="lencana-langganan" href="{{ route('admin.langganan.show', $servis) }}">★ Langganan ({{ $kunjungan->count() + 1 }}x) • lihat riwayat</a>@endif
      </div>
      <dl class="info">
        <div><dt>Pelanggan</dt><dd>{{ $servis->nama_pelanggan }}</dd></div>
        <div><dt>No. HP</dt><dd>{{ $servis->no_hp ?: '-' }}</dd></div>
        <div><dt>Tanggal masuk</dt><dd>{{ $servis->tanggal_masuk->format('d/m/Y') }}</dd></div>
        <div><dt>Tanggal selesai</dt><dd>{{ $servis->tanggal_selesai?->format('d/m/Y') ?? '-' }}</dd></div>
        @can('kelola-servis')
          <div><dt>Total biaya</dt><dd>{{ is_null($servis->biaya) ? '-' : \App\Support\Format::rupiah($servis->biaya) }}</dd></div>
        @endcan
        <div class="lebar"><dt>Keluhan</dt><dd>{{ $servis->keluhan }}</dd></div>
        <div class="lebar"><dt>Catatan mekanik</dt><dd>{{ $servis->catatan ?: '-' }}</dd></div>
      </dl>
    </div>
  </div>
</div>

<div class="card">
  <h2>Riwayat status</h2>
  <ul class="garis-waktu">
    @forelse($servis->riwayat->reverse() as $r)
      <li>
        <b>{{ $r->deskripsi() }}</b>
        <small>{{ $r->created_at->format('d/m/Y H:i') }} • oleh {{ $r->user?->name ?? 'Sistem' }}</small>
      </li>
    @empty
      <li><small>Belum ada riwayat.</small></li>
    @endforelse
  </ul>
</div>

@if($kunjungan->isNotEmpty())
<div class="card">
  <h2>Kunjungan lain untuk plat ini</h2>
  <ul class="garis-waktu">
    @foreach($kunjungan as $k)
      <li>
        <a href="{{ route('admin.servis.show', $k) }}"><b>{{ $k->kode_servis }}</b> • {{ $k->merk_motor }}</a>
        <small>Masuk {{ $k->tanggal_masuk->format('d/m/Y') }} • {{ $k->labelStatus() }} • {{ \Illuminate\Support\Str::limit($k->keluhan, 70) }}</small>
      </li>
    @endforeach
  </ul>
</div>
@endif
@endsection
