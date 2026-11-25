@extends('layouts.teknisi')
@section('title', 'Ringkasan')
@section('judul', 'Halo, ' . explode(' ', trim(auth()->user()->name))[0])
@section('sub', 'Ringkasan motor di bengkel hari ini.')
@section('aksi')
  
@endsection
@section('content')


<section class="stat-grid">
  <a class="stat" style="--a:#1f6fb2" href="{{ route('admin.servis.index') }}">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('wrench', 22) !!}</span>
    <div><b>{{ $berjalan }}</b><span class="l">Motor di bengkel</span></div>
  </a>
  <a class="stat" style="--a:#6b7a87" href="{{ route('admin.servis.index', ['status' => 'menunggu']) }}">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('clock', 22) !!}</span>
    <div><b>{{ $menunggu }}</b><span class="l">Menunggu antrian</span></div>
  </a>
  <a class="stat" style="--a:#2e9e6b" href="{{ route('admin.servis.index', ['status' => 'selesai']) }}">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('check', 22) !!}</span>
    <div><b>{{ $siapAmbil }}</b><span class="l">Siap diambil</span></div>
  </a>
  <div class="stat" style="--a:#f2c21b">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('bar', 22) !!}</span>
    <div><b>{{ $selesaiBulanIni }}</b><span class="l">Selesai bulan ini</span></div>
  </div>
  
</section>



<section class="dua">
  <div class="card">
    <div class="card-kepala">
      <div><h2>Motor terbaru di bengkel</h2><p>Motor yang belum diambil pemiliknya.</p></div>
      <a class="btn sec sm" href="{{ route('admin.servis.index') }}">Lihat semua</a>
    </div>
    @forelse($terbaru as $s)
      <a class="motor-baris" href="{{ route('admin.servis.show', $s) }}">
        <span class="thumb">@if($s->fotoUrl())<img src="{{ $s->fotoUrl() }}" alt="" loading="lazy">@else🏍️@endif</span>
        <div><b>{{ $s->merk_motor }}</b><small>{{ $s->plat_nomor }} • {{ $s->nama_pelanggan }}</small></div>
        <span class="badge s-{{ $s->status }}">{{ $s->labelStatus() }}</span>
      </a>
    @empty
      <div class="kosong-besar"><span class="em">🏍️</span>Belum ada motor di bengkel.</div>
    @endforelse
  </div>

  <div class="card">
    <div class="card-kepala"><div><h2>Komposisi status</h2><p>{{ $total }} motor tercatat.</p></div></div>
    <ul class="komposisi">
      @foreach(\App\Models\Servis::STATUS as $k => $label)
        @php $n = (int) ($status[$k] ?? 0); @endphp
        <li>
          <a href="{{ route('admin.servis.index', ['status' => $k]) }}">
            <div class="atas"><span><i class="titik" style="background:{{ $warna[$k] }}"></i>{{ $label }}</span><b>{{ $n }}</b></div>
            <div class="jalur"><i style="width: {{ $total ? round($n / $total * 100) : 0 }}%;background:{{ $warna[$k] }}"></i></div>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</section>


@endsection
