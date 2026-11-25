@extends('layouts.owner')
@section('title', 'Reset Password')
@section('judul', 'Permintaan Reset Password')
@section('sub', 'Setujui hanya jika Anda yakin yang meminta adalah pemilik akun. Permintaan kedaluwarsa setelah ' . config('bengkel.reset_berlaku_hari') . ' hari.')
@section('content')
@php
  $labelRole = ['owner' => 'Owner', 'admin' => 'Admin', 'teknisi' => 'Teknisi'];
@endphp

<div class="card">
  <div class="card-kepala"><h2>Menunggu persetujuan</h2></div>
  <div class="tabel-geser">
    <table class="tabel">
      <thead><tr><th>Akun</th><th>Role</th><th>Diminta</th><th>Asal permintaan</th><th></th></tr></thead>
      <tbody>
      @forelse($menunggu as $p)
        <tr>
          <td><b>{{ $p->user->name }}</b><span class="sub">{{ $p->user->email }}</span></td>
          <td>{{ $labelRole[$p->user->role] ?? $p->user->role }}</td>
          <td class="nowrap">{{ $p->created_at->format('d/m/Y H:i') }}</td>
          <td><span class="nowrap">{{ $p->ip ?: '-' }}</span><span class="sub" title="{{ $p->perangkat }}">{{ \Illuminate\Support\Str::limit($p->perangkat, 38) }}</span></td>
          <td>
            <div class="aksi">
              <form method="POST" action="{{ route('admin.reset.setujui', $p) }}" onsubmit="return confirm('Aktifkan password baru untuk {{ $p->user->name }}? Pastikan benar-benar dia yang meminta.')">
                @csrf<button class="btn sm">Setujui</button>
              </form>
              <form method="POST" action="{{ route('admin.reset.tolak', $p) }}" onsubmit="return confirm('Tolak permintaan ini?')">
                @csrf<button class="btn sec sm">Tolak</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="5"><div class="kosong-besar"><span class="em">🔑</span>Tidak ada permintaan yang menunggu.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($riwayat->isNotEmpty())
<div class="card">
  <div class="card-kepala"><h2>Riwayat terbaru</h2></div>
  <div class="tabel-geser">
    <table class="tabel">
      <thead><tr><th>Akun</th><th>Status</th><th>Diproses oleh</th><th>Waktu</th></tr></thead>
      <tbody>
      @foreach($riwayat as $p)
        <tr>
          <td>{{ $p->user?->name ?? '-' }}<span class="sub">{{ $p->user?->email }}</span></td>
          <td><span class="badge" style="background:{{ $p->status === 'disetujui' ? '#2e9e6b' : '#c8372d' }}">{{ ucfirst($p->status) }}</span></td>
          <td>{{ $p->pemroses?->name ?? '-' }}</td>
          <td class="nowrap">{{ $p->diproses_at?->format('d/m/Y H:i') }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
