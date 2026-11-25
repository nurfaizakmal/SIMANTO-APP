@extends('layouts.owner')
@section('title', 'Pengguna')
@section('judul', 'Kelola Pengguna')
@section('sub', 'Akun yang dapat masuk ke panel dan hak aksesnya.')
@section('aksi')
  <a class="btn" href="{{ route('admin.pengguna.create') }}">{!! \App\Support\Ikon::svg('plus', 17) !!}Pengguna</a>
@endsection
@section('content')
@php
  $warnaRole = ['owner' => '#17242f', 'admin' => '#1f6fb2', 'teknisi' => '#6b7a87'];
  $labelRole = ['owner' => 'Owner', 'admin' => 'Admin', 'teknisi' => 'Teknisi'];
@endphp
<div class="card">
  @error('hapus')<div class="err" style="margin:0 0 10px">{{ $message }}</div>@enderror
  <div class="tabel-geser">
    <table class="tabel">
      <thead><tr><th>Pengguna</th><th>Email</th><th>Role</th><th>Dibuat</th><th></th></tr></thead>
      <tbody>
      @foreach($daftar as $u)
        @php
          $kata = preg_split('/\s+/', trim($u->name)) ?: [];
          $inisial = mb_strtoupper(mb_substr($kata[0] ?? '', 0, 1) . mb_substr($kata[1] ?? '', 0, 1));
        @endphp
        <tr>
          <td>
            <div class="sel-motor" style="min-width:0">
              <span class="avatar" style="width:36px;height:36px;font-size:.8rem">{{ $inisial }}</span>
              <div><b>{{ $u->name }}</b>@if($u->is(auth()->user()))<small>Akun Anda</small>@endif</div>
            </div>
          </td>
          <td>{{ $u->email }}</td>
          <td><span class="badge" style="background:{{ $warnaRole[$u->role] ?? '#6b7a87' }}">{{ $labelRole[$u->role] ?? $u->role }}</span></td>
          <td class="nowrap">{{ $u->created_at?->format('d/m/Y') }}</td>
          <td>
            <div class="aksi">
              <a class="btn sm" href="{{ route('admin.pengguna.edit', $u) }}">Ubah</a>
              @unless($u->is(auth()->user()))
                <form method="POST" action="{{ route('admin.pengguna.destroy', $u) }}" onsubmit="return confirm('Hapus pengguna {{ $u->name }}?')">
                  @csrf @method('DELETE')<button class="btn red sm">Hapus</button>
                </form>
              @endunless
            </div>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
