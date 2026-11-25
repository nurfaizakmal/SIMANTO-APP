@extends('panel')
@section('title', 'Ringkasan')
@section('judul', 'Halo, ' . explode(' ', trim(auth()->user()->name))[0])
@section('sub', 'Ringkasan motor di bengkel hari ini.')
@section('aksi')
  @can('kelola-servis')
    <a class="btn" href="{{ route('admin.servis.create') }}">{!! \App\Support\Ikon::svg('plus', 17) !!}Motor Masuk</a>
  @endcan
@endsection
@section('content')
@if(($resetMenunggu ?? 0) > 0)
  <p class="chip-info" style="margin:0 0 16px">{{ $resetMenunggu }} permintaan reset password menunggu persetujuan Anda.
    <a href="{{ route('admin.reset.index') }}" style="text-decoration:underline">Tinjau</a></p>
@endif

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
  @if(isset($langgananJumlah))
  <a class="stat" style="--a:#d9480f" href="{{ route('admin.langganan.index') }}">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('star', 22) !!}</span>
    <div><b>{{ $langgananJumlah }}</b><span class="l">Motor langganan</span></div>
  </a>
  @endif
</section>

@if(isset($uang))
@php
  $F = \App\Support\Format::class;
  $punyaKeuangan = auth()->user()->role === 'owner';   // kartu bulanan/total dan grafik hanya untuk owner
@endphp
<section class="stat-grid">
  <div class="stat uang" style="--a:#2e9e6b">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('wallet', 22) !!}</span>
    <div><b>{{ $F::rupiahSingkat($uang['hari']) }}</b><span class="l">Pendapatan hari ini</span></div>
  </div>
@if($punyaKeuangan)
  <div class="stat uang" style="--a:#1f6fb2">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('wallet', 22) !!}</span>
    <div><b>{{ $F::rupiahSingkat($uang['bulan']) }}</b><span class="l">Pendapatan bulan ini</span>
      @if(!is_null($uang['pct']))<small class="d {{ $uang['pct'] >= 0 ? 'naik' : 'turun' }}">{{ $uang['pct'] >= 0 ? '▲' : '▼' }} {{ abs($uang['pct']) }}% dari bulan lalu</small>@endif</div>
  </div>
  <div class="stat uang" style="--a:#6b7a87">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('bar', 22) !!}</span>
    <div><b>{{ $F::rupiahSingkat($uang['total']) }}</b><span class="l">Total pendapatan</span></div>
  </div>
  <div class="stat uang" style="--a:#f2c21b">
    <span class="stat-ikon">{!! \App\Support\Ikon::svg('check', 22) !!}</span>
    <div><b>{{ $F::rupiahSingkat($uang['rata']) }}</b><span class="l">Rata-rata per servis</span></div>
  </div>
@endif
</section>
@if($uang['tanpaBiaya'])
  <p class="chip-info" style="margin:-4px 0 16px">{{ $uang['tanpaBiaya'] }} servis yang sudah diambil belum diisi biayanya, jadi belum masuk hitungan pendapatan.
    <a href="{{ route('admin.servis.index', ['status' => 'diambil']) }}" style="text-decoration:underline">Lihat</a></p>
@endif
@if($punyaKeuangan && ($uang['tertahanJumlah'] ?? 0) > 0)
  <p class="chip-info" style="margin:-4px 0 16px">{{ $uang['tertahanJumlah'] }} motor sudah selesai dengan total {{ $F::rupiah($uang['tertahan']) }} dan menunggu diambil.
    Nilainya masuk pendapatan saat statusnya menjadi Sudah diambil.</p>
@endif

@if($punyaKeuangan)
<div class="card" style="margin-bottom:16px">
  <div class="card-kepala">
    <div>
      <h2>Pendapatan tahun {{ now()->year }}</h2>
      <p>Total Januari sampai sekarang <b>{{ $F::rupiah($uang['tahun']) }}</b>. Dihitung saat motor berstatus Sudah diambil; arahkan kursor ke batang untuk nominalnya.</p>
    </div>
  </div>
  <div class="bars rapat" style="height:220px">
    @foreach($uangBulanan as $b)<i class="{{ $b['depan'] ? 'depan' : ($b['kini'] ? 'kini' : '') }}" title="{{ $b['tip'] }}" style="height: {{ $b['persen'] }}%"></i>@endforeach
  </div>
  <div class="labels rapat">@foreach($uangBulanan as $b)<span class="{{ $b['depan'] ? 'depan' : '' }}">{{ $b['label'] }}<b>{{ $b['depan'] ? '–' : $F::singkat($b['jumlah']) }}</b></span>@endforeach</div>
</div>
@endif
@endif

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

@if(auth()->user()->role === 'owner')
<div class="card" style="margin-top:16px">
  <div class="card-kepala">
    <div><h2>Motor langganan</h2><p>Plat sama masuk lebih dari {{ \App\Models\Servis::batasLangganan() - 1 }} kali. Terbanyak:</p></div>
    <a class="btn sec sm" href="{{ route('admin.langganan.index') }}">Lihat semua</a>
  </div>
  @forelse($langgananTeratas as $b)
    <a class="motor-baris" href="{{ route('admin.langganan.show', $b->servis) }}">
      <span class="thumb">@if($b->servis->fotoUrl())<img src="{{ $b->servis->fotoUrl() }}" alt="" loading="lazy">@else🏍️@endif</span>
      <div><b>{{ $b->servis->merk_motor }}</b><small>{{ $b->servis->plat_nomor }} • {{ $b->servis->nama_pelanggan }}</small></div>
      <span class="lencana-langganan">★ {{ $b->jumlah }}x</span>
    </a>
  @empty
    <div class="kosong-besar"><span class="em">⭐</span>Belum ada motor langganan.</div>
  @endforelse
</div>

<section class="dua" style="margin-top:16px">
  <div class="card">
    <div class="card-kepala">
      <div><h2>Motor masuk 6 bulan terakhir</h2>
        <p>Rata-rata lama pengerjaan: <b>{{ $rata !== null ? number_format($rata, 1, ',', '.') . ' hari' : '-' }}</b></p></div>
    </div>
    <div class="bars">
      @foreach($bulan as $b)
        <i title="{{ $b['jumlah'] }} motor" style="height: {{ max(2, round($b['jumlah'] / $maks * 100)) }}%"></i>
      @endforeach
    </div>
    <div class="labels">@foreach($bulan as $b)<span>{{ $b['label'] }}<br><b>{{ $b['jumlah'] }}</b></span>@endforeach</div>
  </div>

  <div class="card">
    <div class="card-kepala">
      <h2>Aktivitas terbaru</h2>
      <a class="btn sec sm" href="{{ route('admin.audit.index') }}">Audit log</a>
    </div>
    <table class="aktivitas">
      @forelse($aktivitas as $l)
        <tr>
          <td class="nowrap">{{ $l->created_at?->format('d/m H:i') }}</td>
          <td><span class="badge" style="background:{{ $l->warna() }}">{{ $l->label() }}</span></td>
          <td>{{ $l->user?->name ?? 'Tamu/Sistem' }}</td>
        </tr>
      @empty
        <tr><td>Belum ada aktivitas.</td></tr>
      @endforelse
    </table>
  </div>
</section>
@endif
@endsection
