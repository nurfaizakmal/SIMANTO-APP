@extends('layouts.owner')
@section('title', $pengguna->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('judul', $pengguna->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('sub', 'Atur nama, email, role, dan password akun.')
@section('content')
@php $diriSendiri = $pengguna->exists && $pengguna->is(auth()->user()); @endphp
<form class="card" method="POST" style="max-width:600px"
      action="{{ $pengguna->exists ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}">
  @csrf @if($pengguna->exists) @method('PUT') @endif

  <div class="seksi"><h3>Data akun</h3></div>
  <label for="name">Nama</label><input id="name" name="name" value="{{ old('name', $pengguna->name) }}" required>
  <label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $pengguna->email) }}" required>

  <label for="role">Role</label>
  <select id="role" name="role" @disabled($diriSendiri)>
    @foreach(['teknisi' => 'Teknisi (hanya melihat)', 'admin' => 'Admin (melihat, tambah, perbarui status)', 'owner' => 'Owner (akses penuh)'] as $k => $v)
      <option value="{{ $k }}" @selected(old('role', $pengguna->role ?? 'teknisi') === $k)>{{ $v }}</option>
    @endforeach
  </select>
  @if($diriSendiri)<small class="bantu">Role akun Anda sendiri tidak bisa diubah.</small>@endif

  <div class="seksi" style="margin-top:20px"><h3>Password</h3></div>
  <label for="password">Password {{ $pengguna->exists ? '(kosongkan jika tidak diganti)' : '' }}</label>
  <input id="password" type="password" name="password" autocomplete="new-password" @required(!$pengguna->exists)>
  <label for="password_confirmation">Ulangi password</label>
  <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" @required(!$pengguna->exists)>
  @include('partials.syarat-password', ['target' => 'password'])

  @if($errors->any())<div class="err" style="margin-top:12px">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

  <div class="bar-aksi">
    <button class="btn">Simpan</button>
    <a class="btn sec" href="{{ route('admin.pengguna.index') }}">Batal</a>
  </div>
</form>
@endsection
