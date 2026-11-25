@extends('panel')
@section('title', 'Audit Log')
@section('judul', 'Audit Log')
@section('sub', 'Catatan siapa melakukan apa, kapan, dan dari mana. Tidak dapat diubah dari aplikasi ini.')
@section('content')
<div class="card">
  <form method="GET" class="cari-bar">
    <select name="aksi" style="max-width:280px" aria-label="Filter aksi">
      <option value="">Semua aksi</option>
      @foreach(\App\Models\AuditLog::LABEL as $k => $v)
        <option value="{{ $k }}" @selected(request('aksi') === $k)>{{ $v }}</option>
      @endforeach
    </select>
    <button class="btn sec">Terapkan</button>
    @if(request('aksi'))<a class="btn sec" href="{{ route('admin.audit.index') }}">Reset</a>@endif
  </form>

  <div class="tabel-geser">
    <table class="tabel">
      <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Keterangan</th><th>IP</th></tr></thead>
      <tbody>
      @forelse($daftar as $l)
        <tr>
          <td class="nowrap">{{ $l->created_at?->format('d/m/Y H:i:s') }}</td>
          <td>{{ $l->user?->name ?? 'Tamu/Sistem' }}</td>
          <td><span class="badge" style="background:{{ $l->warna() }}">{{ $l->label() }}</span></td>
          <td>{{ $l->keterangan }}</td>
          <td class="nowrap">{{ $l->ip }}</td>
        </tr>
      @empty
        <tr><td colspan="5"><div class="kosong-besar"><span class="em">📋</span>Belum ada catatan.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $daftar->links() }}
</div>
@endsection
